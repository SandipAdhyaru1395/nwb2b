/**
 * App eCommerce customer all
 */

'use strict';

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  let borderColor, bodyBg, headingColor, currencySymbol;
  currencySymbol = window.currencySymbol;
  borderColor = config.colors.borderColor;
  bodyBg = config.colors.bodyBg;
  headingColor = config.colors.headingColor;

  // Variable declaration for table
  const dt_customer_table = document.querySelector('.datatables-customers'),
    select2 = $('.select2'),
    customerView = baseUrl + 'app/ecommerce/customer/details/overview';
  if (select2.length) {
    var $this = select2;
    $this.wrap('<div class="position-relative"></div>').select2({
      placeholder: 'United States ',
      dropdownParent: $this.parent()
    });
  }

  // customers datatable
  if (dt_customer_table) {
    var dt_customer = new DataTable(dt_customer_table, {
      ajax: baseUrl + 'customer/list/ajax',
      columns: [
        // columns according to JSON
        { data: '' },
        { data: 'id', orderable: false, render: DataTable.render.select() },
        { data: 'customer' },
        { data: 'phone' },
        { data: 'credit_balance' },
        { data: 'order' },
        { data: 'total_spent' },
        { data: 'actions', orderable: false, searchable: false }
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
          targets: 2,
          responsivePriority: 1,
          render: function (data, type, full, meta) {
            const name = full['customer'];
            const email = full['email'];
            const image = full['image'];
            let output;

            if (image) {
              // For Avatar image
              output = `
                <img src="${assetsPath}img/avatars/${image}" alt="Avatar" class="rounded-circle">
              `;
            } else {
              // For Avatar badge
              const stateNum = Math.floor(Math.random() * 6);
              const states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
              const state = states[stateNum];
              const initials = (name.match(/\b\w/g) || []).slice(0, 2).join('').toUpperCase();

              output = `<span class="avatar-initial rounded-circle bg-label-${state}">${initials}</span>`;
            }
            // Creates full output for customer name and email
            const rowOutput = `
              <div class="d-flex justify-content-start align-items-center customer-name">
                <div class="avatar-wrapper">
                  <div class="avatar avatar-sm me-3">${output}</div>
                </div>
                <div class="d-flex flex-column">
                  <span class="fw-medium">${name}</span>
                  <small>${email}</small>
                </div>
              </div>`;
            return rowOutput;
          }
        },
        {
          // phone
          targets: 3,
          render: function (data, type, full, meta) {
            const phone = full['phone'] || '';
            return '<span>' + (phone ? phone : '-')+ '</span>';
          }
        },
        {
          // credit balance
          targets: 4,
          render: function (data, type, full, meta) {
            const balance = full['credit_balance'];
            return '<span class="fw-medium">' + currencySymbol + balance + '</span>';
          }
        },
        {
          // customer Status
          targets: 5,
          render: function (data, type, full, meta) {
            const status = full['order'];

            return '<span>' + status + '</span>';
          }
        },
        {
          // customer Spent
          targets: 6,
          render: function (data, type, full, meta) {
            const spent = full['total_spent'];

            return '<span class="fw-medium">' + currencySymbol + spent + '</span>';
          }
        }
        ,
        {
          // actions
          targets: 7,
          className: 'text-center',
          render: function (data, type, full, meta) {
            const id = full['id'];
            const editUrl = baseUrl + 'customer/' + id + '/overview';
            return `
              <div class="d-flex justify-content-center">
                <button class="btn btn-text-secondary rounded-pill waves-effect btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                  <i class="icon-base ti tabler-dots-vertical"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end m-0">
                  <a href="${editUrl}" class="dropdown-item">Edit</a>
                  <a href="javascript:void(0);" class="dropdown-item delete-record" data-id="${id}">Delete</a>
                </div>
              </div>`;
          }
        }
      ],
      select: {
        style: 'multi',
        selector: 'td:nth-child(2)'
      },
      order: [[2, 'desc']],
      layout: {
        topStart: {
          rowClass: 'row m-3 my-0 justify-content-between',
          features: [
            {
              search: {
                placeholder: 'Search Order',
                text: '_INPUT_'
              }
            }
          ]
        },
        topEnd: {
          features: [
            {
              pageLength: {
                menu: [10, 25, 50, 100],
                text: '_MENU_'
              }
            },
            {
              buttons: [
                {
                  extend: 'collection',
                  className: 'btn btn-label-primary dropdown-toggle',
                  text: '<span class="d-flex align-items-center gap-1"><i class="icon-base ti tabler-upload icon-xs"></i> <span class="d-none d-sm-inline-block">Export</span></span>',
                  buttons: [
                    {
                      extend: 'print',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-printer me-1"></i>Print</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Check if inner is HTML content
                            if (inner.indexOf('<') > -1) {
                              const parser = new DOMParser();
                              const doc = parser.parseFromString(inner, 'text/html');

                              // Get all text content
                              let text = '';

                              // Handle specific elements
                              const userNameElements = doc.querySelectorAll('.customer-name');
                              if (userNameElements.length > 0) {
                                userNameElements.forEach(el => {
                                  // Get text from nested structure
                                  const nameText =
                                    el.querySelector('.fw-medium')?.textContent ||
                                    el.querySelector('.d-block')?.textContent ||
                                    el.textContent;
                                  text += nameText.trim() + ' ';
                                });
                              } else {
                                // Get regular text content
                                text = doc.body.textContent || doc.body.innerText;
                              }

                              return text.trim();
                            }

                            return inner;
                          }
                        }
                      },
                      customize: function (win) {
                        win.document.body.style.color = config.colors.headingColor;
                        win.document.body.style.borderColor = config.colors.borderColor;
                        win.document.body.style.backgroundColor = config.colors.bodyBg;
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
                        columns: [2, 3, 4, 5, 6],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle customer-name elements specifically
                            const userNameElements = doc.querySelectorAll('.customer-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
                                // Get text from nested structure - try different selectors
                                const nameText =
                                  el.querySelector('.fw-medium')?.textContent ||
                                  el.querySelector('.d-block')?.textContent ||
                                  el.textContent;
                                text += nameText.trim() + ' ';
                              });
                            } else {
                              // Handle other elements (status, role, etc)
                              text = doc.body.textContent || doc.body.innerText;
                            }

                            return text.trim();
                          }
                        }
                      }
                    },
                    {
                      extend: 'excel',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-upload me-1"></i>Excel</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle customer-name elements specifically
                            const userNameElements = doc.querySelectorAll('.customer-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
                                // Get text from nested structure - try different selectors
                                const nameText =
                                  el.querySelector('.fw-medium')?.textContent ||
                                  el.querySelector('.d-block')?.textContent ||
                                  el.textContent;
                                text += nameText.trim() + ' ';
                              });
                            } else {
                              // Handle other elements (status, role, etc)
                              text = doc.body.textContent || doc.body.innerText;
                            }

                            return text.trim();
                          }
                        }
                      }
                    },
                    {
                      extend: 'pdf',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-file-text me-1"></i>Pdf</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle customer-name elements specifically
                            const userNameElements = doc.querySelectorAll('.customer-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
                                // Get text from nested structure - try different selectors
                                const nameText =
                                  el.querySelector('.fw-medium')?.textContent ||
                                  el.querySelector('.d-block')?.textContent ||
                                  el.textContent;
                                text += nameText.trim() + ' ';
                              });
                            } else {
                              // Handle other elements (status, role, etc)
                              text = doc.body.textContent || doc.body.innerText;
                            }

                            return text.trim();
                          }
                        }
                      }
                    },
                    {
                      extend: 'copy',
                      text: `<i class="icon-base ti tabler-copy me-1"></i>Copy`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4, 5, 6],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle customer-name elements specifically
                            const userNameElements = doc.querySelectorAll('.customer-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
                                // Get text from nested structure - try different selectors
                                const nameText =
                                  el.querySelector('.fw-medium')?.textContent ||
                                  el.querySelector('.d-block')?.textContent ||
                                  el.textContent;
                                text += nameText.trim() + ' ';
                              });
                            } else {
                              // Handle other elements (status, role, etc)
                              text = doc.body.textContent || doc.body.innerText;
                            }

                            return text.trim();
                          }
                        }
                      }
                    }
                  ]
                },
                {
                  text: '<span class="d-flex align-items-center gap-1"><i class="icon-base ti tabler-plus icon-xs"></i> <span class="d-none d-sm-inline-block">Add Customer</span></span>',
                  className: 'create-new btn btn-primary',
                  attr: {
                    'data-bs-toggle': 'modal',
                    'data-bs-target': '#addCustomerModal'
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
              return 'Details of ' + data['customer'];
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
      }
    });
  }

  // Filter form control to default size
  // ? setTimeout used for customer-all table initialization
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-buttons', classToAdd: 'gap-4' },
      { selector: '.dt-buttons.btn-group', classToAdd: 'mb-6 mb-md-0' },
      { selector: '.dt-buttons .btn-group', classToAdd: 'me-md-0 me-6' },
      { selector: '.dt-buttons .btn-group .btn', classToRemove: 'btn-secondary', classToAdd: 'btn-label-secondary' },
      { selector: '.dt-buttons .btn-group ~ .btn', classToRemove: 'btn-secondary' },
      { selector: '.dt-search .form-control', classToRemove: 'form-control-sm', classToAdd: 'ms-0' },
      { selector: '.dt-length .form-select', classToRemove: 'form-select-sm' },
      { selector: '.dt-length', classToAdd: 'mt-0 mt-md-6' },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
      { selector: '.dt-layout-start', classToAdd: 'mt-0' },
      { selector: '.dt-layout-end', classToAdd: 'gap-md-2 gap-0 mt-0' },
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

  // Delete customer confirmation (same UX as orders)
  document.addEventListener('click', function (e) {
    const trigger = e.target.closest('.delete-record');
    if (!trigger) return;
    e.preventDefault();
    e.stopPropagation();

    const tr = trigger.closest('tr');
    let id = trigger.getAttribute('data-id');
    if (!id && tr && dt_customer_table) {
      const dt = DataTable.dom.dataTable(dt_customer_table);
      const row = dt && dt.row && dt.row(tr);
      const data = row && row.data && row.data();
      id = data && data.id;
    }
    if (!id) return;

    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert customer!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, Delete customer!',
      customClass: {
        confirmButton: 'btn btn-primary me-2 waves-effect waves-light',
        cancelButton: 'btn btn-label-secondary waves-effect waves-light'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        // TODO: wire actual delete endpoint when available
        // window.location.href = baseUrl + 'customer/delete/' + id;
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'Cancelled Delete :)',
          icon: 'error',
          customClass: { confirmButton: 'btn btn-success waves-effect waves-light' }
        });
      }
    });
  });
});

