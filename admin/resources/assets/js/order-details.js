/**
 * app-ecommerce-order-details Script
 */

'use strict';

// Datatable (js)

document.addEventListener('DOMContentLoaded', function (e) {
  let currencySymbol = window.currencySymbol;

  var dt_details_table = document.querySelector('.datatables-order-details');

  // E-commerce Products datatable
  if (dt_details_table) {
    let tableTitle = document.createElement('h5');
    tableTitle.classList.add('card-title', 'mb-0');
    tableTitle.innerHTML = 'Order details';
    let tableEdit = document.createElement('h6');
    tableEdit.classList.add('m-0');
    tableEdit.innerHTML = '<a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#addItemModal" id="editProductBtn">Edit</a>';
    var dt_products = new DataTable(dt_details_table, {
      processing: true,
      serverSide: true,
      stateSave: true,
      ajax: baseUrl + 'order/items/ajax?id=' + (window.orderId || ''),
      columns: [
        // columns according to JSON
        { data: 'product_name' },
        { data: 'wallet_credit_earned' },
        { data: 'unit_price' },
        { data: 'quantity' },
        { data: 'id' },
        { data: 'id' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },
        {
          // Product column
          targets: 1,
          responsivePriority: 1,
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            const name = full['product_name'];
            const productBrand = full['product_info'] || '';
            const image = full['image_url'];
            let output;

            if (image) {
              // For Product image
              output = `
                <img src="${image?.includes('https') ? '' : assetsPath + 'storage/'}${image}" alt="product-${name}" class="rounded-2">
              `;
            } else {
              // For Product badge
              const stateNum = Math.floor(Math.random() * 6);
              const states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
              const state = states[stateNum];
              const initials = (name.match(/\b\w/g) || []).slice(0, 2).join('').toUpperCase();

              output = `<span class="avatar-initial rounded-2 bg-label-${state}">${initials}</span>`;
            }

            // Creates full output for Product name and product info
            const rowOutput = `
              <div class="d-flex justify-content-start align-items-center">
                <div class="avatar-wrapper">
                  <div class="avatar avatar-sm me-3">${output}</div>
                </div>
                <div class="d-flex flex-column" style="white-space: normal;">
                  <span class="mb-0" style="white-space: normal;">${name}</span>
                  <small>${productBrand}</small>
                </div>
              </div>`;

            return rowOutput;
          }
        },
        {
          // Wallet Credit
          targets: 2,
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            const credit = full['wallet_credit_earned'] || 0;
              return '<span>' + currencySymbol + credit + '</span>';
          }
        },
        {
          // For Price
          targets: 3,
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            const price = full['unit_price'];
            const output = '<span>' + currencySymbol + price + '</span>';
            return output;
          }
        },
        {
          // For Qty
          targets: 4,
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            const qty = full['quantity'];
            const output = '<span>' + qty + '</span>';
            return output;
          }
        },
       
        {
          // Total
          targets: 5,
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            const total = full['quantity'] * full['unit_price'];
            const output = '<span>' + currencySymbol + total + '</span>';
            return output;
          }
        },
        {
          // Actions
          targets: 6,
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            const itemId = full['id'];
            return `
              <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary edit-item" data-bs-toggle="modal" data-bs-target="#editItemModal" data-id="${itemId}" data-product-id="${full['product_id']}" data-quantity="${full['quantity']}" data-unit-price="${full['unit_price']}">
                  <i class="icon-base ti tabler-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-primary delete-item" data-id="${itemId}">
                  <i class="icon-base ti tabler-trash"></i>
                </button>
              </div>
            `;
          }
        }
      ],
      order: [1, ''],
      layout: {
        topStart: {
          rowClass: 'row card-header border-bottom mx-0 px-3',
          features: [tableTitle]
        },
        topEnd: {
          features: [tableEdit]
        },
        bottomStart: {
          rowClass: 'mt-0',
          features: []
        },
        bottomEnd: {}
      },
      responsive: {
        details: {
          display: DataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['product_name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            const data = columns
              .map(function (col) {
                return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
                  ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
                      <td>${col.title}:</td>
                      <td>${col.data}</td>
                    </tr>`
                  : '';
              })
              .join('');

            if (data) {
              const div = document.createElement('div');
              div.classList.add('table-responsive');
              const table = document.createElement('table');
              div.appendChild(table);
              table.classList.add('table');
              const tbody = document.createElement('tbody');
              tbody.innerHTML = data;
              table.appendChild(tbody);
              return div;
            }
            return false;
          }
        }
      }
    });
  }
  // Filter form control to default size
  // ? setTimeout used for order-details table initialization
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
      { selector: '.dt-layout-full', classToRemove: 'col-md col-12', classToAdd: 'table-responsive' }
    ];

    // Delete record
    elementsToModify.forEach(({ selector, classToRemove, classToAdd }) => {
      document.querySelectorAll(selector).forEach(element => {
        classToRemove.split(' ').forEach(className => element.classList.remove(className));
        if (classToAdd) {
          element.classList.add(classToAdd);
        }
      });
    });
  }, 100);
});

//sweet alert
(function () {
  const deleteOrder = document.querySelector('.delete-order');
  // Suspend User javascript
  if (deleteOrder) {
    deleteOrder.onclick = function () {
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert order!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete order!',
        customClass: {
          confirmButton: 'btn btn-primary me-2 waves-effect waves-light',
          cancelButton: 'btn btn-label-secondary waves-effect waves-light'
        },
        buttonsStyling: false
      }).then(function (result) {
          if (result.value) {
            window.location.href = baseUrl + 'order/delete/' + (window.orderId || '');
          } else if (result.dismiss === Swal.DismissReason.cancel) {
          Swal.fire({
            title: 'Cancelled',
            text: 'Cancelled Delete :)',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success waves-effect waves-light'
            }
          });
        }
      });
    };
  }

  
})();

// Product Management Functions
document.addEventListener('DOMContentLoaded', function() {
  let currencySymbol = window.currencySymbol;
  let products = window.products || [];
  let currentDataTable = null;

  // Initialize product management
  initProductManagement();

  function initProductManagement() {
    // Get the data table instance
    const dt_details_table = document.querySelector('.datatables-order-details');
    if (dt_details_table && dt_details_table.DataTable) {
      currentDataTable = dt_details_table.DataTable();
    }

   

    // Product selection change handler
    const productSelect = document.getElementById('productSelect');
    if (productSelect && window.$ && $.fn.select2) {
      const ajaxUrl = productSelect.getAttribute('data-ajax-url');
      $(productSelect).select2({
        dropdownParent: $('#addItemModal'),
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: productSelect.getAttribute('data-placeholder') || 'Search product...',
        minimumInputLength: 0,
        ajax: {
          url: ajaxUrl,
          dataType: 'json',
          delay: 250,
          data: function (params) {
            return {
              q: params.term || '',
              limit: 10
            };
          },
          processResults: function (data) {
            return { results: data.results };
          },
          cache: true
        },
        // Fetch initial 10 items
        initSelection: function (element, callback) {},
      });

      // Preload first 10 when the dropdown opens and nothing typed
      $(productSelect).on('select2:open', function() {
        const search = $('.select2-search__field');
        if (search && search.val() === '') {
          // Trigger a blank search to load first 10
          $(productSelect).select2('open');
        }
      });

      // Set unit price when selection changes
      $(productSelect).on('select2:select', function (e) {
        const data = e.params.data;
        if (data && typeof data.price !== 'undefined') {
          const priceInput = document.getElementById('unitPrice');
          if (priceInput) {
            priceInput.value = data.price;
            calculateTotals();
          }
        }
      });
    } else if (productSelect) {
      // Fallback without select2: simple change listener
      productSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.value) {
          const price = selectedOption.getAttribute('data-price');
          const priceInput = document.getElementById('unitPrice');
          if (priceInput && price) {
            priceInput.value = price;
            calculateTotals();
          }
        }
      });
    }

    // Quantity and price change handlers for both modals
    bindCalcHandlers(document.getElementById('addItemModal'));
    bindCalcHandlers(document.getElementById('editItemModal'));

    // Form submission handlers
    const itemForm = document.getElementById('itemForm');
    const editItemForm = document.getElementById('editItemForm');
    let addFormValidator = null;
    let editFormValidator = null;
    
    // if (itemForm) {
    //   // Initialize FormValidation for Add Item modal
    //   if (window.FormValidation && FormValidation.formValidation) {
    //     try {
    //       addFormValidator = FormValidation.formValidation(itemForm, {
    //         fields: {
    //           product_id: {
    //             validators: {
    //               notEmpty: {
    //                 message: 'Please select a product'
    //               }
    //             }
    //           },
    //           quantity: {
    //             validators: {
    //               notEmpty: {
    //                 message: 'Quantity is required'
    //               },
    //               regexp: {
    //                 regexp: /^\d+$/, // positive integers only
    //                 message: 'Quantity must be a positive integer'
    //               },
    //               greaterThan: {
    //                 message: 'Quantity must be greater than 0',
    //                 min: 1
    //               }
    //             }
    //           },
    //           unit_price: {
    //             validators: {
    //               notEmpty: {
    //                 message: 'Unit price is required'
    //               },
    //               regexp: {
    //                 regexp: /^(?:\d+)(?:\.\d{1,2})?$/, // positive number with up to 2 decimals
    //                 message: 'Enter a valid positive amount'
    //               },
                  
    //             }
    //           }
    //         },
    //         plugins: {
    //           trigger: new FormValidation.plugins.Trigger(),
    //           bootstrap5: new FormValidation.plugins.Bootstrap5({
    //             rowSelector: '.mb-3'
    //           }),
    //           autoFocus: new FormValidation.plugins.AutoFocus()
    //         }
    //       });
    //     } catch (err) {
    //       console.warn('FormValidation init failed:', err);
    //     }
    //   }

    //   itemForm.addEventListener('submit', function(e){
    //     // If validator exists, validate first
    //     if (addFormValidator) {
    //       e.preventDefault();
    //       addFormValidator.validate().then(function(status){
    //         if (status === 'Valid') {
    //           handleFormSubmit(e);
    //         }
    //       });
    //     } else {
    //       handleFormSubmit(e);
    //     }
    //   });
    // }
    // if (editItemForm) {
    //   // Initialize FormValidation for Edit Item modal (no product_id rule)
    //   if (window.FormValidation && FormValidation.formValidation) {
    //     try {
    //       editFormValidator = FormValidation.formValidation(editItemForm, {
    //         fields: {
    //           quantity: {
    //             validators: {
    //               notEmpty: {
    //                 message: 'Quantity is required'
    //               },
    //               regexp: {
    //                 regexp: /^\d+$/, // positive integers only
    //                 message: 'Quantity must be a positive integer'
    //               },
    //               greaterThan: {
    //                 min: 1,
    //                 message: 'Quantity must be greater than 0'
    //               }
    //             }
    //           },
    //           unit_price: {
    //             validators: {
    //               notEmpty: {
    //                 message: 'Unit price is required'
    //               },
    //               regexp: {
    //                 regexp: /^(?:\d+)(?:\.\d{1,2})?$/, // positive number up to 2 decimals
    //                 message: 'Enter a valid positive amount'
    //               }
    //             }
    //           }
    //         },
    //         plugins: {
    //           trigger: new FormValidation.plugins.Trigger(),
    //           bootstrap5: new FormValidation.plugins.Bootstrap5({
    //             rowSelector: '.mb-3'
    //           }),
    //           autoFocus: new FormValidation.plugins.AutoFocus()
    //         }
    //       });
    //     } catch (err) {
    //       console.warn('FormValidation (edit) init failed:', err);
    //     }
    //   }

    //   editItemForm.addEventListener('submit', function(e){
    //     if (editFormValidator) {
    //       e.preventDefault();
    //       editFormValidator.validate().then(function(status){
    //         if (status === 'Valid') {
    //           handleEditFormSubmit(e);
    //         }
    //       });
    //     } else {
    //       handleEditFormSubmit(e);
    //     }
    //   });
    // }

    // Delete item handler (delegated events) - open modals via data-bs only
    document.addEventListener('click', function(e) {
      if (e.target.closest('.delete-item')) {
        handleDeleteItem(e.target.closest('.delete-item'));
      }
    });

    // Modal lifecycle (open via data attributes only)
    document.addEventListener('show.bs.modal', function (event) {
      const modal = event.target;
      const trigger = event.relatedTarget;
      if (!modal) return;

      // Add Item modal: just reset
      if (modal.id === 'addItemModal') {
        resetModal();
        return;
      }

      // Edit Item modal: populate from triggering button data attributes
      if (modal.id === 'editItemModal' && trigger) {
        const itemId = trigger.getAttribute('data-id') || '';
        const productId = trigger.getAttribute('data-product-id') || '';
        const quantity = trigger.getAttribute('data-quantity') || '';
        const unitPrice = trigger.getAttribute('data-unit-price') || '';

        setModalField(modal, '[name="id"]', itemId);
        setModalField(modal, '#productId', productId);
        setModalField(modal, '[name="quantity"]', quantity);
        setModalField(modal, '[name="unit_price"]', unitPrice);
        // Initialize total for edit modal
        calculateTotalsIn(modal);
      }
    });
  }

  function bindCalcHandlers(modalEl){
    if (!modalEl) return;
    modalEl.addEventListener('input', function(e){
      const target = e.target;
      if (target && (target.id === 'quantity' || target.id === 'unitPrice')) {
        calculateTotalsIn(modalEl);
      }
    });
  }

  function openAddModal() {
    // Reset modal and show item fields
    resetModal();
    document.getElementById('addItemModalTitle').textContent = 'Add New Item';
    document.getElementById('submitButton').textContent = 'Save Item';
    document.getElementById('productSelectionSection').style.display = 'block';
    const modal = new bootstrap.Modal(document.getElementById('addItemModal'));
    modal.show();
  }


  function calculateTotalsIn(modalEl) {
    if (!modalEl) return;
    const qtyEl = modalEl.querySelector('#quantity');
    const unitEl = modalEl.querySelector('#unitPrice');
    const totalEl = modalEl.querySelector('#totalPrice');
    if (!qtyEl || !unitEl || !totalEl) return;
    const quantity = parseFloat(qtyEl.value) || 0;
    const unitPrice = parseFloat(unitEl.value) || 0;
    const totalPrice = quantity * unitPrice;
    totalEl.value = totalPrice.toFixed(2);
  }

  // Backwards compatibility for calls that used calculateTotals()
  function calculateTotals() {
    const addModal = document.getElementById('addItemModal');
    calculateTotalsIn(addModal || document);
  }

  function handleFormSubmit(e) {
    e.preventDefault();
    handleItemCreation(e);
  }

  function handleItemCreation(e) {
    const formData = new FormData(e.target);
    const itemId = document.getElementById('itemId').value;
    const isEdit = itemId !== '';
    
    const url = isEdit ? 
      `${baseUrl}order/item/update` : 
      `${baseUrl}order/item/create`;
    
    const data = Object.fromEntries(formData.entries());
    
    // Convert numeric fields
    data.quantity = parseInt(data.quantity);
    data.unit_price = parseFloat(data.unit_price);
    data.order_id = parseInt(data.order_id);

    fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify(data)
    })
    .then(async response => {
      const result = await response.json().catch(() => ({ success: false }));
      if (response.ok && result.success) {
        // Show success message
        if (typeof toastr !== 'undefined') {
          toastr.success(result.message);
        } else {
          alert(result.message);
        }
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('addItemModal'));
        modal.hide();
        
        // Refresh data table
        if (currentDataTable) {
          currentDataTable.ajax.reload();
        }
        
        // Refresh page to update totals
        setTimeout(() => {
          window.location.reload();
        }, 1000);
      } else {
        const msg = (result && result.message) ? result.message : 'Error adding item';
        if (typeof toastr !== 'undefined') toastr.error(msg);
        else alert(msg);
        // keep modal open and show validation errors if any
        if (result && result.errors) {
          Object.values(result.errors).forEach(errArr => {
            if (Array.isArray(errArr) && errArr.length && typeof toastr !== 'undefined') {
              toastr.error(errArr[0]);
            }
          });
        }
      }
    })
    .catch(error => {
      console.error('Error:', error);
      if (typeof toastr !== 'undefined') {
        toastr.error(error.message);
      } else {
        alert('Error: ' + error.message);
      }
    });
  }

  function handleEditFormSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    fetch(form.action, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: formData
    })
    .then(async r => {
      const result = await r.json().catch(() => ({ success: false }));
      if (r.ok && result.success) {
        if (typeof toastr !== 'undefined') toastr.success(result.message || 'Item updated');
        const modal = bootstrap.Modal.getInstance(document.getElementById('editItemModal'));
        modal?.hide();
        const table = document.querySelector('.datatables-order-details');
        if (table && table.DataTable) table.DataTable().ajax.reload();
        setTimeout(() => window.location.reload(), 800);
      } else {
        const msg = (result && result.message) ? result.message : 'Update failed';
        if (typeof toastr !== 'undefined') toastr.error(msg);
        else alert(msg);
        if (result && result.errors) {
          Object.values(result.errors).forEach(errArr => {
            if (Array.isArray(errArr) && errArr.length && typeof toastr !== 'undefined') {
              toastr.error(errArr[0]);
            }
          });
        }
      }
    })
    .catch(err => {
      console.error(err);
      if (typeof toastr !== 'undefined') toastr.error(err.message);
      else alert(err.message);
    });
  }

  // Product creation flow removed (only item add/edit remains)

  // Removed programmatic opening of modals. Population happens on show.bs.modal

  function handleDeleteItem(button) {
    const itemId = button.getAttribute('data-id');
    
    Swal.fire({
      title: 'Are you sure?',
      // text: "",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: {
        confirmButton: 'btn btn-primary me-2 waves-effect waves-light',
        cancelButton: 'btn btn-label-secondary waves-effect waves-light'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        fetch(`${baseUrl}order/item/delete/${itemId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
        .then(response => response.json())
        .then(result => {
          if (result.success) {
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: result.message,
              customClass: {
                confirmButton: 'btn btn-success waves-effect waves-light'
              }
            });
            
            // Refresh data table
            if (currentDataTable) {
              currentDataTable.ajax.reload();
            }
            
            // Refresh page to update totals
            setTimeout(() => {
              window.location.reload();
            }, 1000);
          } else {
            throw new Error(result.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.message,
            customClass: {
              confirmButton: 'btn btn-success waves-effect waves-light'
            }
          });
        });
      }
    });
  }

  function resetModal() {
    // Reset form
    document.getElementById('itemForm').reset();
    document.getElementById('itemId').value = '';
    const title = document.getElementById('addItemModalTitle');
    if (title) title.textContent = 'Add New Item';
    const submitBtn = document.getElementById('submitButton');
    if (submitBtn) submitBtn.textContent = 'Save Item';
    
    // Show item section, hide product section
    document.getElementById('productSelectionSection').style.display = 'block';
    
    // Clear calculated fields
    const total = document.getElementById('totalPrice');
    if (total) total.value = '';
    
    // Reset product select to first option
    const productSelect = document.getElementById('productSelect');
    if (productSelect) productSelect.selectedIndex = 0;
  }

  function setModalField(modal, selector, value) {
    const el = modal.querySelector(selector);
    if (el) el.value = value;
  }

  const editAddressForm = document.getElementById('editAddressForm');

  if (editAddressForm) {
    //Add New customer Form Validation
    const fv = FormValidation.formValidation(editAddressForm, {
      fields: {
        branch_name: {
          validators: {
            notEmpty: {
              message: 'Please enter branch name'
            }
          }
        },
        address_line1: {
          validators: {
            notEmpty: {
              message: 'Please enter address line 1'
            },
          }
        },
        city: {
          validators: {
            notEmpty: {
              message: 'Please enter city'
            }
          }
        },
        zip_code: {
          validators: {
            notEmpty: {
              message: 'Please enter postcode'
            },
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          // Use this for enabling/changing valid/invalid class
          eleValidClass: '',
          rowSelector: function (field, ele) {
            // field is the field name & ele is the field element
            return '.form-control-validation';
          }
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        // Submit the form when all fields are valid
        defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      },
    });
    
  }
});
