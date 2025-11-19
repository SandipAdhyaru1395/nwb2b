/**
 * Order Add Script
 * Used for order/add.blade.php (add form)
 */

'use strict';

// Order Add Form Functionality (for order/add.blade.php)
document.addEventListener('DOMContentLoaded', function() {
  if (typeof window.$ !== 'undefined' && $('#addOrderForm').length) {
    const currencySymbol = window.currencySymbol || '';
    
    // ---------- Local Storage Persistence (define early) ----------
    const STORAGE_KEY = 'order_add_form_v1';
    let saveTimeoutId = null;
    let pendingAddressId = null; // Store address_id to restore after branches load

    function collectFormState() {
      const products = [];
      $('#products-table tbody tr:not(.total-row)').each(function() {
        const $row = $(this);
        const productId = $row.data('product-id');
        const productText = ($row.find('td').eq(0).text() || '').trim();
        const unit_cost = $row.find('.cost-input').val();
        const quantity = $row.find('.quantity-input').val();
        products.push({ productId, productText, unit_cost, quantity });
      });
      return {
        date: ($('#date').val() || '').trim(),
        customer_id: ($('#customer_id').val() || '').trim(),
        address_id: ($('#address_id').val() || '').trim(),
        shipping_charge: ($('#shipping_charge').val() || '').trim(),
        delivery_note: ($('#note').val() || '').trim(),
        products
      };
    }

    function saveFormState() {
      try {
        const state = collectFormState();
        localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
      } catch (e) {
        // ignore quota or JSON errors silently
      }
    }

    function saveFormStateDebounced() {
      clearPendingSave();
      saveTimeoutId = setTimeout(saveFormState, 200);
    }

    function clearPendingSave() {
      if (saveTimeoutId) {
        clearTimeout(saveTimeoutId);
        saveTimeoutId = null;
      }
    }
    
    // Initialize flatpickr for date & time
    $('.flatpickr').flatpickr({
      enableTime: true,
      dateFormat: 'd/m/Y H:i',
      time_24hr: true,
      allowInput: true,
      defaultDate: new Date(),
      onChange: function(selectedDates, dateStr, instance) {
        saveFormStateDebounced();
      }
    });

    // Initialize select2 for customer dropdown
    const $customerSelect = $('#customer_id');
    if ($customerSelect.length) {
      $customerSelect.select2({
        placeholder: 'Select Customer',
        allowClear: false,
        width: '100%',
        dropdownParent: $('#addOrderForm')
      });
    }

    // Initialize select2 for address dropdown
    const $addressSelect = $('#address_id');
    if ($addressSelect.length) {
      $addressSelect.select2({
        placeholder: 'Select Customer First',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#addOrderForm')
      });
    }

    // Load branches when customer changes
    $customerSelect.on('change', function() {
      const customerId = $(this).val();
      $addressSelect.empty().append('<option value="">Loading...</option>').prop('disabled', true);
      saveFormStateDebounced();
      
      if (customerId) {
        $.ajax({
          url: baseUrl + 'order/customer/' + customerId + '/branches',
          dataType: 'json',
          success: function(branches) {
            $addressSelect.empty();
            if (branches && branches.length > 0) {
              $addressSelect.append('<option value="">Select Address</option>');
              branches.forEach(function(branch) {
                $addressSelect.append('<option value="' + branch.id + '">' + branch.text + '</option>');
              });
            } else {
              $addressSelect.append('<option value="">No addresses available</option>');
            }
            $addressSelect.prop('disabled', false);
            
            // Restore address_id if it was pending
            if (pendingAddressId && $addressSelect.find('option[value="' + pendingAddressId + '"]').length > 0) {
              $addressSelect.val(pendingAddressId).trigger('change.select2');
              pendingAddressId = null;
            }
            saveFormStateDebounced();
          },
          error: function() {
            $addressSelect.empty().append('<option value="">Error loading addresses</option>').prop('disabled', false);
            pendingAddressId = null;
          }
        });
      } else {
        $addressSelect.empty().append('<option value="">Select Customer First</option>').prop('disabled', true);
        pendingAddressId = null;
      }
    });

    // Initialize Quill editor for delivery_note
    const noteEditor = document.querySelector('#note-editor');
    var quill;
    if (noteEditor) {
      quill = new Quill(noteEditor, {
        modules: {
          toolbar: '.comment-toolbar'
        },
        placeholder: 'Add delivery note...',
        theme: 'snow'
      });

      // Update hidden input on editor change
      quill.on('text-change', function() {
        let content = quill.root.innerHTML;
        if (content === '<p><br></p>') {
          content = ''; // Treat as empty
        }
        $('#note').val(content);
        saveFormStateDebounced();
      });
    }

    // Product search functionality for orders
    const MAX_RESULTS = 20;
    const $productSearch = $('#product-search');
    const $productResults = $('#product-search-results');
    const $productSpinner = $('#product-search-spinner');
    let activeIndex = -1;
    let searchTimeout = null;
    let currentQuery = '';
    let currentRequest = null;
    let productDataMap = {};

    function resetResults() {
      if (currentRequest && currentRequest.readyState !== 4) {
        currentRequest.abort();
      }
      currentRequest = null;
      activeIndex = -1;
      $productResults.empty().hide();
    }

    function setActiveItem(index) {
      const $items = $productResults.find('.list-group-item');
      $items.removeClass('active');
      if (index >= 0 && index < $items.length) {
        const $item = $items.eq(index);
        // Skip disabled items when navigating
        if (!$item.prop('disabled') && !$item.data('isDisabled')) {
          $item.addClass('active');
          activeIndex = index;
        }
      }
    }

    function renderResults(items) {
      $productResults.empty();
      if (!items.length) {
        activeIndex = -1;
        $productResults
          .append('<div class="list-group-item bg-white text-muted">No results found</div>')
          .show();
        return;
      }

      items.forEach(function(item) {
        const stockQuantity = parseFloat(item.stock_quantity || 0);
        const isDisabled = stockQuantity <= 0;
        
        const $button = $('<button type="button" class="list-group-item list-group-item-action bg-white text-dark"></button>')
          .text(item.text)
          .data('productId', item.id)
          .data('productText', item.text)
          .data('isDisabled', isDisabled);
        
        // Store price (sale price for orders) in data attribute
        // Use price field if available, otherwise fall back to unit_cost
        const salePrice = item.price !== undefined ? parseFloat(item.price) : (item.unit_cost !== undefined ? parseFloat(item.unit_cost) : 0);
        $button.data('productPrice', salePrice);
        
        // Disable button if quantity <= 0
        if (isDisabled) {
          $button.prop('disabled', true)
            .addClass('disabled')
            .css({
              'opacity': '0.6',
              'cursor': 'not-allowed'
            });
        }
        
        $productResults.append($button);
      });

      $productResults.show();
      // Set active item to first enabled item, or -1 if all disabled
      const $enabledItems = $productResults.find('.list-group-item:not(.disabled)');
      if ($enabledItems.length > 0) {
        setActiveItem($enabledItems.first().index());
      } else {
        activeIndex = -1;
      }
    }

    function fetchProducts(query) {
      if (currentRequest) {
        currentRequest.abort();
      }

      currentRequest = $.ajax({
        url: baseUrl + 'purchase/search/ajax',
        dataType: 'json',
        data: {
          q: query,
          limit: MAX_RESULTS
        }
      }).done(function(data) {
        if (currentQuery !== query) {
          return;
        }
        const results = data && data.results ? data.results.slice(0, MAX_RESULTS) : [];
        
        // Store product data by ID for quick lookup
        productDataMap = {};
        results.forEach(function(item) {
          productDataMap[item.id] = item;
        });
        
        renderResults(results);
      }).fail(function(xhr, status) {
        if (status !== 'abort') {
          resetResults();
        }
      }).always(function() {
        currentRequest = null;
        $productSpinner.hide();
      });
    }

    if ($productSearch.length) {
      $productSearch.on('input', function() {
        const query = $(this).val().trim();
        currentQuery = query;
        clearTimeout(searchTimeout);

        if (!query) {
          resetResults();
          return;
        }

        searchTimeout = setTimeout(function() {
          $productSpinner.show();
          fetchProducts(query);
        }, 200);
      });

      $productSearch.on('keydown', function(e) {
        if (!$productResults.is(':visible')) {
          return;
        }

        const $items = $productResults.find('.list-group-item');
        if (!$items.length) {
          return;
        }

        if (e.key === 'ArrowDown') {
          e.preventDefault();
          // Find next enabled item
          let nextIndex = activeIndex;
          let attempts = 0;
          do {
            nextIndex = nextIndex >= $items.length - 1 ? 0 : nextIndex + 1;
            attempts++;
            if (attempts > $items.length) break; // Prevent infinite loop
          } while (attempts <= $items.length && ($items.eq(nextIndex).prop('disabled') || $items.eq(nextIndex).data('isDisabled')));
          setActiveItem(nextIndex);
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          // Find previous enabled item
          let prevIndex = activeIndex;
          let attempts = 0;
          do {
            prevIndex = prevIndex <= 0 ? $items.length - 1 : prevIndex - 1;
            attempts++;
            if (attempts > $items.length) break; // Prevent infinite loop
          } while (attempts <= $items.length && ($items.eq(prevIndex).prop('disabled') || $items.eq(prevIndex).data('isDisabled')));
          setActiveItem(prevIndex);
        } else if (e.key === 'Enter') {
          if (activeIndex >= 0) {
            const $activeItem = $items.eq(activeIndex);
            if (!$activeItem.prop('disabled') && !$activeItem.data('isDisabled')) {
              e.preventDefault();
              $activeItem.trigger('click');
            }
          }
        } else if (e.key === 'Escape') {
          resetResults();
        }
      });

      $productResults.on('mousedown', '.list-group-item', function(e) {
        e.preventDefault();
      });

      $productResults.on('mouseenter', '.list-group-item', function() {
        // Skip disabled items when hovering
        if (!$(this).prop('disabled') && !$(this).data('isDisabled')) {
          setActiveItem($(this).index());
        }
      });

      $productResults.on('click', '.list-group-item', function() {
        if (!$(this).data('productId')) {
          return; // Non-selectable (e.g., No results found)
        }
        // Prevent adding disabled products
        if ($(this).data('isDisabled') || $(this).prop('disabled')) {
          return;
        }
        const productId = $(this).data('productId');
        const productText = $(this).data('productText');
        // Get price from data attribute (for orders, we use price as sale price)
        let productPrice = $(this).data('productPrice');
        // Fallback: try to get from productDataMap if not in data attribute
        if (productPrice === undefined && productDataMap[productId]) {
          const item = productDataMap[productId];
          productPrice = item.price !== undefined ? parseFloat(item.price) : (item.unit_cost !== undefined ? parseFloat(item.unit_cost) : 0);
        }
        productPrice = productPrice || 0;
        
        if (typeof window.addProductToTable === 'function') {
          window.addProductToTable(productId, productText, 1, productPrice);
        }
        $productSearch.val('').trigger('focus');
        resetResults();
      });

      $(document).on('click', function(e) {
        if (!$(e.target).closest('#product-search-container').length) {
          resetResults();
        }
      });

      setTimeout(function() {
        $productSearch.trigger('focus');
      }, 0);
    }

    // Calculate totals (quantity and amount)
    function calculateTotal() {
      var totalQty = 0;
      var totalAmount = 0;
      $('#products-table tbody tr:not(.total-row)').each(function() {
        var $row = $(this);
        var qty = parseFloat($row.find('.quantity-input').val()) || 0;
        var cost = parseFloat($row.find('.cost-input').val()) || 0;
        var sub = qty * cost;
        $row.find('.subtotal-cell').text(currencySymbol + sub.toFixed(2));
        totalQty += qty;
        totalAmount += sub;
      });
      $('.total-quantity').text(totalQty.toFixed(2));
      $('.total-amount').text(currencySymbol + totalAmount.toFixed(2));
    }

    // Add product row - make it available globally for initialization
    window.addProductToTable = function(productId, productText, quantity = 1, unit_price = 0) {
      // Check if product already exists in the table
      var existingRow = $('#products-table tbody tr[data-product-id="' + productId + '"]:not(.total-row)');
      
      if (existingRow.length > 0) {
        // Product exists, increment quantity
        var quantityInput = existingRow.find('.quantity-input');
        var currentQty = parseFloat(quantityInput.val()) || 0;
        var newQty = currentQty + quantity;
        quantityInput.val(newQty);
        calculateTotal();
        return;
      }
      
      // Product doesn't exist, add new row
      var row = '<tr data-product-id="' + productId + '">' +
        '<td>' + productText + '<input type="hidden" name="products[' + productId + '][product_id]" value="' + productId + '"></td>' +
        '<td><input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)" class="form-control form-control-sm cost-input" name="products[' + productId + '][unit_cost]" value="' + unit_price + '" autocomplete="off"></td>' +
        '<td><input type="text" onkeypress="return /^[0-9]+$/.test(event.key)" class="form-control form-control-sm quantity-input" name="products[' + productId + '][quantity]" value="' + quantity + '" autocomplete="off"></td>' +
        '<td class="subtotal-cell">' + currencySymbol + '0.00</td>' +
        '<td><a href="javascript:;" title="Remove" class="remove-product"><i class="icon-base ti tabler-x"></i></a></td>' +
        '</tr>';
      
      // Insert before the total-row, or prepend if total-row doesn't exist
      var $totalRow = $('#products-table tbody tr.total-row');
      if ($totalRow.length > 0) {
        $totalRow.before(row);
      } else {
        $('#products-table tbody').prepend(row);
      }
      calculateTotal();
    };

    // Remove product row
    $(document).on('click', '.remove-product', function() {
      $(this).closest('tr').remove();
      calculateTotal();
      saveFormStateDebounced();
    });

    // Update totals on input change
    $(document).on('input', '.quantity-input, .cost-input', function() {
      calculateTotal();
      saveFormStateDebounced();
    });

    // Calculate totals on initial load to format with currency symbol
    setTimeout(function() {
      calculateTotal();
    }, 300);

    function buildProductRowHtml(productId, productText, quantity, unit_price) {
      const safeQty = (quantity == null || quantity === '') ? '1' : String(quantity);
      const safePrice = (unit_price == null || unit_price === '') ? '0.00' : String(unit_price);
      return '' +
        '<tr data-product-id="' + productId + '">' +
          '<td>' + productText + '<input type="hidden" name="products[' + productId + '][product_id]" value="' + productId + '"></td>' +
          '<td><input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)" class="form-control form-control-sm cost-input" name="products[' + productId + '][unit_cost]" value="' + safePrice + '" autocomplete="off"></td>' +
          '<td><input type="text" onkeypress="return /^[0-9]+$/.test(event.key)" class="form-control form-control-sm quantity-input" name="products[' + productId + '][quantity]" value="' + safeQty + '" autocomplete="off"></td>' +
          '<td class="subtotal-cell">' + currencySymbol + '0.00</td>' +
          '<td><a href="javascript:;" title="Remove" class="remove-product"><i class="icon-base ti tabler-x"></i></a></td>' +
        '</tr>';
    }

    function restoreFormState() {
      try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) return;
        const state = JSON.parse(raw);
        
        // Restore date
        if (state.date) {
          $('#date').val(state.date);
          // Update flatpickr if it exists
          const flatpickrInstance = $('#date').data('flatpickr');
          if (flatpickrInstance) {
            flatpickrInstance.setDate(state.date, false);
          }
        }
        
        // Restore customer_id first
        if (state.customer_id && $('#customer_id option[value="' + state.customer_id + '"]').length) {
          $('#customer_id').val(state.customer_id).trigger('change.select2');
          
          // Restore address_id after customer branches are loaded
          if (state.address_id) {
            pendingAddressId = state.address_id;
            // Trigger customer change to load branches
            $customerSelect.trigger('change');
          }
        }
        
        // Restore shipping_charge
        if (state.shipping_charge !== undefined && state.shipping_charge !== null && state.shipping_charge !== '') {
          $('#shipping_charge').val(state.shipping_charge);
        }

        // Restore delivery_note (both hidden input and quill if available)
        if (typeof state.delivery_note === 'string') {
          $('#note').val(state.delivery_note);
          if (typeof quill !== 'undefined' && quill) {
            quill.root.innerHTML = state.delivery_note || '';
          }
        }

        // Restore products
        if (Array.isArray(state.products)) {
          // Remove any existing non-total rows first
          $('#products-table tbody tr:not(.total-row)').remove();
          const $totalRow = $('#products-table tbody tr.total-row');
          state.products.forEach(function(p) {
            if (!p || !p.productId || !p.productText) return;
            const rowHtml = buildProductRowHtml(p.productId, p.productText, p.quantity, p.unit_cost);
            // Insert before the total row so total stays at bottom
            if ($totalRow.length > 0) {
              $totalRow.before(rowHtml);
            } else {
              $('#products-table tbody').prepend(rowHtml);
            }
          });
          calculateTotal();
        }
      } catch (e) {
        // ignore malformed JSON
      }
    }

    // Save on basic input changes
    $('#date, #shipping_charge').on('input change', saveFormStateDebounced);
    $('#customer_id, #address_id').on('change', saveFormStateDebounced);

    // Enhance existing handlers to save state as well
    const originalAddProductToTable = window.addProductToTable;
    window.addProductToTable = function(productId, productText, quantity, unit_price) {
      originalAddProductToTable(productId, productText, quantity, unit_price);
      saveFormStateDebounced();
    };

    // On initial load, restore any saved state after a short delay to ensure Select2 is ready
    setTimeout(function() {
      restoreFormState();
      // Calculate totals on initial load to format with currency symbol
      calculateTotal();
    }, 100);

    // Clear storage on successful submit
    $('#addOrderForm').on('submit', function() {
      clearPendingSave();
      try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
    });

    // Clear storage on logout
    $(document).on('click', 'a[href*="logout"]', function() {
      clearPendingSave();
      try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
    });
    $(document).on('submit', 'form[action*="logout"]', function() {
      clearPendingSave();
      try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
    });

    // FormValidation for order add form
    const addOrderForm = document.getElementById('addOrderForm');
    if (addOrderForm) {
      const fv = FormValidation.formValidation(addOrderForm, {
        fields: {
          customer_id: {
            validators: {
              notEmpty: {
                message: 'Customer is required'
              }
            }
          },
          date: {
            validators: {
              notEmpty: {
                message: 'Date is required'
              },
              callback: {
                message: 'Enter a valid date in dd/mm/yyyy hh:mm format',
                callback: function(input) {
                  const value = (input.value || '').trim();
                  if (!value) return false;
                  const re = /^\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}$/;
                  if (!re.test(value)) return false;
                  try {
                    const parsed = flatpickr.parseDate(value, 'd/m/Y H:i');
                    return parsed instanceof Date && !isNaN(parsed.getTime());
                  } catch (e) {
                    return false;
                  }
                }
              }
            }
          },
          address_id: {
            validators: {
              notEmpty: {
                message: 'Address is required'
              }
            }
          },
          products: {
            selector: '#products-table',
            validators: {
              callback: {
                message: 'Please add at least one product',
                callback: function() {
                  return $('#products-table tbody tr:not(.total-row)').length > 0;
                }
              }
            }
          },
          cost_price: {
            selector: '#products-table',
            validators: {
              callback: {
                message: 'Sale price is required and must be a valid number (0 or greater)',
                callback: function() {
                  let isValid = true;
                  $('#products-table tbody tr:not(.total-row)').each(function() {
                    const costInput = $(this).find('.cost-input');
                    const costValue = costInput.val().trim();
                    
                    // Check if required (not empty)
                    if (!costValue || costValue === '') {
                      isValid = false;
                      return false; // break loop
                    }
                    
                    // Check if numeric
                    if (isNaN(costValue) || !/^\d+(\.\d+)?$/.test(costValue)) {
                      isValid = false;
                      return false; // break loop
                    }
                    
                    // Check if >= 0
                    const costNum = parseFloat(costValue);
                    if (costNum < 0) {
                      isValid = false;
                      return false; // break loop
                    }
                  });
                  return isValid;
                }
              }
            }
          },
          quantity: {
            selector: '#products-table',
            validators: {
              callback: {
                message: 'Quantity is required and must be a valid number (at least 1)',
                callback: function() {
                  let isValid = true;
                  $('#products-table tbody tr:not(.total-row)').each(function() {
                    const qtyInput = $(this).find('.quantity-input');
                    const qtyValue = qtyInput.val().trim();
                    
                    // Check if required (not empty)
                    if (!qtyValue || qtyValue === '') {
                      isValid = false;
                      return false; // break loop
                    }
                    
                    // Check if numeric (integer)
                    if (isNaN(qtyValue) || !/^\d+$/.test(qtyValue)) {
                      isValid = false;
                      return false; // break loop
                    }
                    
                    // Check if >= 1
                    const qtyNum = parseFloat(qtyValue);
                    if (qtyNum < 1) {
                      isValid = false;
                      return false; // break loop
                    }
                  });
                  return isValid;
                }
              }
            }
          },
          shipping_charge: {
            validators: {
              callback: {
                message: 'Shipping charge must be a valid number (0 or greater)',
                callback: function(input) {
                  const value = (input.value || '').trim();
                  // Allow empty (optional field)
                  if (!value || value === '') {
                    return true;
                  }
                  // Check if numeric
                  if (isNaN(value) || !/^\d+(\.\d+)?$/.test(value)) {
                    return false;
                  }
                  // Check if >= 0
                  const numValue = parseFloat(value);
                  return numValue >= 0;
                }
              }
            }
          }
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            eleValidClass: 'is-valid',
            rowSelector: function(field, ele) {
              if (field === 'products' || field === 'cost_price' || field === 'quantity') {
                return '.card-body';
              }
              return '.form-control-validation';
            }
          }),
          submitButton: new FormValidation.plugins.SubmitButton(),
          autoFocus: new FormValidation.plugins.AutoFocus()
        }
      });

      // Revalidate products, cost_price, and quantity fields when rows change
      function revalidateProductFields() {
        fv.revalidateField('products');
        fv.revalidateField('cost_price');
        fv.revalidateField('quantity');
      }
      
      $(document).on('click', '.remove-product', function() {
        revalidateProductFields();
      });
      $(document).on('click', '#product-search-results .list-group-item, #product-search-results button', function() {
        revalidateProductFields();
      });
      
      // Revalidate cost_price and quantity on input change
      $(document).on('input', '.cost-input', function() {
        fv.revalidateField('cost_price');
      });
      $(document).on('input', '.quantity-input', function() {
        fv.revalidateField('quantity');
      });
      $(document).on('input', '#shipping_charge', function() {
        fv.revalidateField('shipping_charge');
      });
      
      const tbody = document.querySelector('#products-table tbody');
      if (tbody) {
        const observer = new MutationObserver(function() {
          revalidateProductFields();
        });
        observer.observe(tbody, { childList: true, subtree: false });
      }

      // Submit form after successful validation
      fv.on('core.form.valid', function () {
        clearPendingSave();
        try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
        addOrderForm.submit();
      });
    }
  }
});

