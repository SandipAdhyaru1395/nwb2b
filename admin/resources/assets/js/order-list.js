/**
 * app-ecommerce-order-list Script
 */

'use strict';

// Datatable (js)

document.addEventListener('DOMContentLoaded', function (e) {
  let borderColor, bodyBg, headingColor, currencySymbol;

  borderColor = config.colors.borderColor;
  bodyBg = config.colors.bodyBg;
  headingColor = config.colors.headingColor;
  currencySymbol = window.currencySymbol || '';

  // Variable declaration for table

  const dt_order_table = document.querySelector('.datatables-order'),
    orderAdd = baseUrl + 'order/add',
    statusObj = {
      'Completed': { title: 'Completed', class: 'bg-success' },
      'Returned': { title: 'Returned', class: 'bg-danger' }
    },
    paymentObj = {
      'Due': { title: 'Due', class: 'bg-danger' },
      'Paid': { title: 'Paid', class: 'bg-success' },
      'Partial': { title: 'Partial', class: 'bg-warning' },
    };

  // E-commerce Products datatable

  if (dt_order_table) {
    const dt_products = new DataTable(dt_order_table, {
      processing: true,
      stateSave: true,
      serverSide: true,
      ajax: baseUrl + 'order/list/ajax',
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'id', orderable: false, render: DataTable.render.select() },
        { data: 'order_date' },
        { data: 'order_number' },
        { data: 'customer_name' },
        { data: 'total_amount' },
        { data: 'paid_amount' },
        { data: 'unpaid_amount' },
        { data: 'vat_amount' },
        { data: 'order_status' },
        { data: 'payment_status' },
        { data: 'has_credit_note' },
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
          // For Checkboxes
          targets: 1,
          orderable: false,
          searchable: false,
          responsivePriority: 3,
          checkboxes: true,
          render: function () {
            return '<input type="checkbox" class="dt-checkboxes form-check-input">';
          },
          checkboxes: {
            selectAllRender: '<input type="checkbox" class="form-check-input">'
          }
        },
        {
          // Date
          targets: 2,
          render: function (data, type, full, meta) {
            const date = new Date(full.order_date);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const seconds = String(date.getSeconds()).padStart(2, '0');
            const formattedDate = `${day}/${month}/${year}`;
            const formattedTime = `${hours}:${minutes}:${seconds}`;
            return `<div class="d-flex flex-column">
              <span>${formattedDate}</span>
              <small class="text-muted">${formattedTime}</small>
            </div>`;
          }
        },
        {
          // Reference No
          targets: 3,
          render: function (data, type, full, meta) {
            const orderType = full['type'] || 'SO';
            const orderNumber = full['order_number'] || '';
            let display = '#' + orderType + orderNumber;
            
            // If CN type, show parent order number
            if (orderType === 'CN' && full['parent_order_display']) {
              display += ' (#' + full['parent_order_display'] + ')';
            }
            
            // If SO type, show credit note number
            if (orderType === 'SO' && full['credit_note_display']) {
              display += ' (#' + full['credit_note_display'] + ')';
            }
            
            return '<span>' + display + '</span>';
          }
        },
        {
          // Customer
          targets: 4,
          responsivePriority: 1,
          render: function (data, type, full, meta) {
            const name = full['customer_name'] || '';
            const email = full['customer_email'] || '';
            return `
              <div class="d-flex justify-content-start align-items-center order-name text-nowrap">
                <div class="d-flex flex-column">
                  ${name ? `<span class="fw-medium">${name}</span>` : ''}
                  ${email ? `<small class="text-muted">${email}</small>` : ''}
                </div>
              </div>`;
          }
        },
        {
          // Grand Total
          targets: 5,
          render: function (data, type, full, meta) {
            const amount = parseFloat(full['total_amount'] || 0);
            return `<span class="text-nowrap">${currencySymbol}${amount.toFixed(2)}</span>`;
          }
        },
        {
          // Paid
          targets: 6,
          render: function (data, type, full, meta) {
            const amount = parseFloat(full['paid_amount'] || 0);
            return `<span class="text-nowrap">${currencySymbol}${amount.toFixed(2)}</span>`;
          }
        },
        {
          // Balance
          targets: 7,
          render: function (data, type, full, meta) {
            const amount = parseFloat(full['unpaid_amount'] || 0);
            return `<span class="text-nowrap">${currencySymbol}${amount.toFixed(2)}</span>`;
          }
        },
        {
          // Total VAT
          targets: 8,
          render: function (data, type, full, meta) {
            const amount = parseFloat(full['vat_amount'] || 0);
            return `<span class="text-nowrap">${currencySymbol}${amount.toFixed(2)}</span>`;
          }
        },
        {
          // Sale Status
          targets: 9,
          width: '80px',
          render: function (data, type, full, meta) {
            const status = full['order_status'];
            const statusInfo = statusObj[status] || { title: '', class: 'bg-label-secondary' };
            return `
              <span class="badge px-2 ${statusInfo.class} text-capitalized">
                ${statusInfo.title}
              </span>`;
          }
        },
        {
          // Payment Status
          targets: 10,
          width: '90px',
          render: function (data, type, full, meta) {
            const payment = full['payment_status'];
            const paymentStatus = paymentObj[payment];
            return `
            <span class="badge px-2 ${paymentStatus.class} text-capitalized">
              ${paymentStatus.title}
            </span>`;
          }
        },
        {
          // Hide has_credit_note column
          targets: 11,
          visible: false,
          searchable: false
        },
        {
          targets: -1,
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
              return `
              <div class="d-flex justify-content-sm-start align-items-sm-center">
                <button class="btn btn-text-secondary rounded-pill waves-effect btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                  <i class="icon-base ti tabler-dots-vertical"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end m-0">
                  <a href="${baseUrl}order/invoice/${full['id']}" target="_blank" class="dropdown-item">View Invoice</a>
                  ${(!full['has_credit_note'] || full['has_credit_note'] == 0) && (!full['type'] || full['type'] !== 'CN') ? `<a href="${baseUrl}order/edit/${full['id']}" class="dropdown-item">Edit</a>` : ''}
                  ${parseFloat(full['unpaid_amount'] || 0) > 0 ? `<a href="javascript:void(0);" class="dropdown-item add-payment-btn" data-id="${full['id']}" data-order-number="${(full['type'] || 'SO') + (full['order_number'] || '')}" data-unpaid="${full['unpaid_amount'] || 0}">Add Payment</a>` : ''}
                  ${parseFloat(full['paid_amount'] || 0) > 0 ? `<a href="javascript:void(0);" class="dropdown-item view-payments-btn" data-id="${full['id']}" data-order-number="${(full['type'] || 'SO') + (full['order_number'] || '')}">View Payments</a>` : ''}
                  ${(!full['has_credit_note'] || full['has_credit_note'] == 0) && (!full['type'] || full['type'] !== 'CN') ? `<a href="${baseUrl}order/credit-note/add/${full['id']}" class="dropdown-item">Credit Note</a>` : ''}
                  <a href="javascript:void(0);" class="dropdown-item delete-record" data-id="${full['id']}">Delete</a>
                </div>
              </div>`;
          }
        }
      ],
      select: {
        style: 'multi',
        selector: 'td:nth-child(2)'
      },
      order: [2, 'desc'],
      layout: {
        topStart: {
          search: {
            placeholder: 'Search Order',
            text: '_INPUT_'
          }
        },
        topEnd: {
          rowClass: 'row mx-3 my-0 justify-content-between',
          features: [
            {
              pageLength: {
                menu: [7, 10, 25, 50, 100],
                text: '_MENU_'
              }
            },
            {
              buttons: [
                {
                  extend: 'collection',
                  className: 'btn btn-label-primary dropdown-toggle me-4',
                  text: '<span class="d-flex align-items-center gap-1"><i class="icon-base ti tabler-upload icon-xs"></i> <span class="d-none d-sm-inline-block">Export</span></span>',
                  buttons: [
                    {
                      extend: 'print',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-printer me-1"></i>Print</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6, 7, 8, 9, 10],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;
                            const el = new DOMParser().parseFromString(inner, 'text/html').body.childNodes;
                            let result = '';
                            el.forEach(item => {
                              if (item.classList && item.classList.contains('user-name')) {
                                result += item.lastChild.firstChild.textContent;
                              } else {
                                result += item.textContent || item.innerText || '';
                              }
                            });
                            return result;
                          }
                        }
                      },
                      customize: function (win) {
                        win.document.body.style.color = headingColor;
                        win.document.body.style.borderColor = borderColor;
                        win.document.body.style.backgroundColor = bodyBg;
                        const table = win.document.body.querySelector('table');
                        table.classList.add('compact');
                        table.style.color = 'inherit';
                        table.style.borderColor = 'inherit';
                        table.style.backgroundColor = 'inherit';
                      }
                    },
                    {
                      extend: 'csv',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-file me-1"></i>Csv</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6, 7, 8, 9, 10],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;
                            const el = new DOMParser().parseFromString(inner, 'text/html').body.childNodes;
                            let result = '';
                            el.forEach(item => {
                              if (item.classList && item.classList.contains('user-name')) {
                                result += item.lastChild.firstChild.textContent;
                              } else {
                                result += item.textContent || item.innerText || '';
                              }
                            });
                            return result;
                          }
                        }
                      }
                    },
                    {
                      extend: 'excel',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-upload me-1"></i>Excel</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6, 7, 8, 9, 10],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;
                            const el = new DOMParser().parseFromString(inner, 'text/html').body.childNodes;
                            let result = '';
                            el.forEach(item => {
                              if (item.classList && item.classList.contains('user-name')) {
                                result += item.lastChild.firstChild.textContent;
                              } else {
                                result += item.textContent || item.innerText || '';
                              }
                            });
                            return result;
                          }
                        }
                      }
                    },
                    {
                      extend: 'pdf',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-file-text me-1"></i>Pdf</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6, 7, 8, 9, 10],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;
                            const el = new DOMParser().parseFromString(inner, 'text/html').body.childNodes;
                            let result = '';
                            el.forEach(item => {
                              if (item.classList && item.classList.contains('user-name')) {
                                result += item.lastChild.firstChild.textContent;
                              } else {
                                result += item.textContent || item.innerText || '';
                              }
                            });
                            return result;
                          }
                        }
                      }
                    },
                    {
                      extend: 'copy',
                      text: `<i class="icon-base ti tabler-copy me-1"></i>Copy`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6, 7, 8, 9, 10],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;
                            const el = new DOMParser().parseFromString(inner, 'text/html').body.childNodes;
                            let result = '';
                            el.forEach(item => {
                              if (item.classList && item.classList.contains('user-name')) {
                                result += item.lastChild.firstChild.textContent;
                              } else {
                                result += item.textContent || item.innerText || '';
                              }
                            });
                            return result;
                          }
                        }
                      }
                    }
                  ]
                },
                {
                  text: '<i class="icon-base ti tabler-plus me-0 me-sm-1 icon-16px"></i><span class="d-none d-sm-inline-block">Add Order</span>',
                  className: 'add-new btn btn-primary',
                  action: function () {
                    window.location.href = orderAdd;
                  }
                }
              ]
            }
          ]
        },
        bottomStart: {
          rowClass: 'row mx-3 justify-content-between',
          features: ['info']
        },
        bottomEnd: 'paging'
      },
      language: {
        paginate: {
          next: '<i class="icon-base ti tabler-chevron-right scaleX-n1-rtl icon-18px"></i>',
          previous: '<i class="icon-base ti tabler-chevron-left scaleX-n1-rtl icon-18px"></i>',
          first: '<i class="icon-base ti tabler-chevrons-left scaleX-n1-rtl icon-18px"></i>',
          last: '<i class="icon-base ti tabler-chevrons-right scaleX-n1-rtl icon-18px"></i>'
        }
      },
      // For responsive popup
      responsive: {
        details: {
          display: DataTable.Responsive.display.modal({
            header: function (row) {
              const data = row.data();
              const orderType = data['type'] || 'SO';
              const orderNumber = data['order_number'] || '';
              return 'Details of Order #' + orderType + orderNumber;
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            const data = columns
              .map(function (col) {
                return col.title !== '' // Do not show row in modal popup if title is blank (for check box)
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
      },
      initComplete: function () {
        const api = this.api();

        // Row click to open details modal (ignore checkbox and actions columns)
        dt_order_table.querySelector('tbody').addEventListener('click', function (e) {
          // Don't open modal if clicking on actions dropdown or its menu items
          if (e.target.closest('.dropdown-toggle') || e.target.closest('.dropdown-menu') || e.target.closest('.dropdown-item')) {
            return;
          }

          const cell = e.target.closest('td');
          if (!cell) return;
          const cellIndex = cell.cellIndex;
          // Ignore control (0), checkbox (1), actions (last)
          const lastIndex = dt_products.columns().count() - 1;
          if (cellIndex === 0 || cellIndex === 1 || cellIndex === lastIndex) return;

          const rowEl = e.target.closest('tr');
          if (!rowEl) return;
          const row = dt_products.row(rowEl);
          if (!row || !row.data()) return;
          const id = row.data().id;
          if (!id) return;

          // Fetch details via AJAX and show modal
          const url = baseUrl + 'order/show/ajax/' + id;
          fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (res) { return res.json(); })
            .then(function (payload) {
              if (!payload || !payload.html) return;
              const modalEl = document.getElementById('order-view-modal');
              const contentEl = document.getElementById('order-view-modal-content');
              if (!modalEl || !contentEl) return;
              contentEl.innerHTML = payload.html;
              const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
              modal.show();
            })
            .catch(function () { /* ignore */ });
        });
      }
    });

    // Set datatable instance for payment modal to reload after payment is added
    if (typeof window.setPaymentDatatableInstance === 'function') {
      window.setPaymentDatatableInstance(dt_products);
    }

    // Delete order with confirmation (same flow as details page)
    document.addEventListener('click', function (e) {
      const trigger = e.target.closest('.delete-record');
      if (!trigger) return;
      e.preventDefault();
      e.stopPropagation();

      // Prefer id from data attribute; fallback to row data
      let orderId = trigger.getAttribute('data-id');
      if (!orderId) {
        const tr = trigger.closest('tr');
        if (tr) {
          const row = dt_products.row(tr);
          const data = row && row.data && row.data();
          orderId = data && data.id;
        }
      }
      if (!orderId) return;

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
          window.location.href = baseUrl + 'order/delete/' + orderId;
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
    });

    // Set datatable instance for payment view modal to reload after payment is deleted
    if (typeof window.setPaymentViewDatatableInstance === 'function') {
      window.setPaymentViewDatatableInstance(dt_products);
    }

    // Load order statistics
    loadOrderStatistics(currencySymbol);
  }

  // Function to load and display order statistics
  function loadOrderStatistics(currencySymbol) {
    if (!currencySymbol) {
      currencySymbol = window.currencySymbol || '';
    }
    fetch(baseUrl + 'order/statistics', {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success && data.statistics) {
        const stats = data.statistics;
        
        // Update Grand Total
        const grandTotalEl = document.getElementById('widget-grand-total');
        if (grandTotalEl) {
          grandTotalEl.textContent = currencySymbol + parseFloat(stats.grand_total || 0).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          });
        }
        
        // Update Paid
        const paidEl = document.getElementById('widget-paid');
        if (paidEl) {
          paidEl.textContent = currencySymbol + parseFloat(stats.paid || 0).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          });
        }
        
        // Update Balance
        const balanceEl = document.getElementById('widget-balance');
        if (balanceEl) {
          balanceEl.textContent = currencySymbol + parseFloat(stats.balance || 0).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          });
        }
        
        // Update Payment Status Counts for SO (Sales Orders)
        const dueCountSoEl = document.getElementById('widget-due-count-so');
        if (dueCountSoEl) {
          dueCountSoEl.textContent = stats.due_count_so || 0;
        }
        
        const partialCountSoEl = document.getElementById('widget-partial-count-so');
        if (partialCountSoEl) {
          partialCountSoEl.textContent = stats.partial_count_so || 0;
        }
        
        const paidCountSoEl = document.getElementById('widget-paid-count-so');
        if (paidCountSoEl) {
          paidCountSoEl.textContent = stats.paid_count_so || 0;
        }
        
        // Update Payment Status Counts for CN (Credit Notes)
        const dueCountCnEl = document.getElementById('widget-due-count-cn');
        if (dueCountCnEl) {
          dueCountCnEl.textContent = stats.due_count_cn || 0;
        }
        
        const partialCountCnEl = document.getElementById('widget-partial-count-cn');
        if (partialCountCnEl) {
          partialCountCnEl.textContent = stats.partial_count_cn || 0;
        }
        
        const paidCountCnEl = document.getElementById('widget-paid-count-cn');
        if (paidCountCnEl) {
          paidCountCnEl.textContent = stats.paid_count_cn || 0;
        }
        
        // Update total count (SO + CN)
        const totalCountSo = (stats.due_count_so || 0) + (stats.partial_count_so || 0) + (stats.paid_count_so || 0);
        const totalCountCn = (stats.due_count_cn || 0) + (stats.partial_count_cn || 0) + (stats.paid_count_cn || 0);
        const totalCount = totalCountSo + totalCountCn;
        const totalCountEl = document.getElementById('widget-payment-status-count');
        if (totalCountEl) {
          totalCountEl.textContent = totalCount;
        }
      }
    })
    .catch(error => {
      console.error('Error loading order statistics:', error);
    });
  }

  // Expose loadOrderStatistics globally so it can be called from other scripts
  window.loadOrderStatistics = loadOrderStatistics;

  // Filter form control to default size
  // ? setTimeout used for order-list table initialization
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-buttons .btn:not(.btn-primary)', classToRemove: 'btn-secondary', classToAdd: 'btn-label-secondary' },
      { selector: '.dt-search .form-control', classToRemove: 'form-control-sm', classToAdd: 'ms-0' },
      { selector: '.dt-length .form-select', classToRemove: 'form-select-sm' },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
      { selector: '.dt-layout-end', classToAdd: 'gap-md-2 gap-0' },
      { selector: '.dt-layout-full', classToRemove: 'col-md col-12', classToAdd: 'table-responsive' }
    ];

    // Delete record
    elementsToModify.forEach(({ selector, classToRemove, classToAdd }) => {
      document.querySelectorAll(selector).forEach(element => {
        if (classToRemove) {
          classToRemove.split(' ').forEach(className => element.classList.remove(className));
        }
        if (classToAdd) {
          classToAdd.split(' ').forEach(className => element.classList.add(className));
        }
      });
    });
  }, 100);
});
