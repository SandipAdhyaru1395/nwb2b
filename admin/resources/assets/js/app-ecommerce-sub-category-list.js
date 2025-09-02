/**
 * App eCommerce Category List
 */

'use strict';

let quillAdd;
window.quillEdit = null;

// Function to initialize Quill editor for edit offcanvas
window.initializeQuillEdit = function() {
  const commentEditorEdit = document.querySelector('#edit-sub-category-description');
  if (commentEditorEdit) {
    // Destroy existing Quill instance if it exists
    if (window.quillEdit && typeof window.quillEdit.destroy === 'function') {
      window.quillEdit.destroy();
    }
    
    // Create new Quill instance
    window.quillEdit = new Quill(commentEditorEdit, {
      modules: {
        toolbar: '.comment-toolbar-edit'
      },
      placeholder: 'Write a Comment...',
      theme: 'snow'
    });
    
    return window.quillEdit;
  } else {
    return null;
  }
}

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  let statusObj = {
      "active": { title: 'Active', class: 'bg-label-success' },
      "inactive": { title: 'Inactive', class: 'bg-label-secondary' }
    };

  // Comment editor
  

  // Initialize Quill for Add Category (this works because add offcanvas is always visible)
  const commentEditorAdd = document.querySelector('#add-sub-category-description');

  if (commentEditorAdd) {
    quillAdd = new Quill(commentEditorAdd, {
      modules: {
        toolbar: '.comment-toolbar'
      },
      placeholder: 'Write a Comment...',
      theme: 'snow'
    });
  }
  
  // Initialize Quill for Edit Category when offcanvas is shown
  const editOffcanvas = document.getElementById('offcanvasEditSubCategory');
  if (editOffcanvas) {
    // Initialize Quill editor when offcanvas starts showing
    editOffcanvas.addEventListener('show.bs.offcanvas', function () {
      initializeQuillEdit();
    });
    
    // Handle offcanvas hidden event to clean up
    editOffcanvas.addEventListener('hidden.bs.offcanvas', function () {
      if (window.quillEdit && typeof window.quillEdit.destroy === 'function') {
        window.quillEdit.destroy();
        window.quillEdit = null;
      }
    });
  }
  
  var dt_category_list_table = document.querySelector('.datatables-sub-category-list');

  


  //select2 for dropdowns in offcanvas

  var select2 = $('.select2');
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      $this.wrap('<div class="position-relative"></div>').select2({
        dropdownParent: $this.parent(),
        placeholder: $this.data('placeholder') //for dynamic placeholder
      });
    });
  }

  // Customers List Datatable

  if (dt_category_list_table) {
    var dt_category = new DataTable(dt_category_list_table, {
      ajax: baseUrl + 'subcategory/ajax-list', // JSON file to add data
      columns: [
        // columns according to JSON
        { data: 'id' },
        { data: 'id', orderable: false, render: DataTable.render.select() },
        { data: 'sub_category' },
        { data: 'category' },
        { data: 'id' },
        {data: 'status'}
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 1,
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
          responsivePriority: 4,
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
          responsivePriority: 2,
          render: function (data, type, full, meta) {
            const name = full['sub_category'];
            const categoryDetail = full['sub_category_desc'];
            
            let stateNum = Math.floor(Math.random() * 6);
            let states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
            let state = states[stateNum];
            let initials = (name.match(/\b\w/g) || []).slice(0, 2).join('').toUpperCase();

            let output = `<span class="avatar-initial rounded-2 bg-label-${state}">${initials}</span>`;
            // Creates full output for Categories and Category Detail
            const rowOutput = `
            <div class="d-flex align-items-center">
             <div class="avatar-wrapper">
                  <div class="avatar avatar me-2 me-sm-4 rounded-2 bg-label-secondary">${output}</div>
                </div>
              <div class="d-flex align-items-center">
                <div class="d-flex flex-column justify-content-center">
                  <span class="text-heading text-wrap fw-medium">${name}</span>
                  ${categoryDetail ? `<span class="text-truncate mb-0 d-none d-sm-block"><small>${categoryDetail}</small></span>` : ''}
                </div>
              </div>
            </div>
            `;
            return rowOutput;
          }
        },
        {
          targets: 3,
          render: function (data, type, full, meta) {
            const name = full['category'];
            
            let stateNum = Math.floor(Math.random() * 6);
            let states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
            let state = states[stateNum];
            let initials = (name.match(/\b\w/g) || []).slice(0, 2).join('').toUpperCase();

            let output = `<span class="avatar-initial rounded-2 bg-label-${state}">${initials}</span>`;
            // Creates full output for Categories and Category Detail
            const rowOutput = `
            <div class="d-flex align-items-center">
             <div class="avatar-wrapper">
                  <div class="avatar avatar me-2 me-sm-4 rounded-2 bg-label-secondary">${output}</div>
                </div>
              <div class="d-flex align-items-center">
                <div class="d-flex flex-column justify-content-center">
                  <span class="text-heading text-wrap fw-medium">${name}</span>
                </div>
              </div>
            </div>
            `;
            
            return rowOutput;
          }
        },
        {
          // For status
          targets: 4,
          render: function (data, type, full, meta) {
            const status = full['status'];
            // Creates full output for row
            const rowOutput =
              '<span class="badge ' +
              statusObj[status].class +
              '" text-capitalized>' +
              statusObj[status].title +
              '</span>';
            return rowOutput;
          }
        },
        {
          // Actions
          targets: -1,
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            return `
              <div class="d-flex align-items-sm-center justify-content-sm-center">
                <button class="btn btn-text-secondary rounded-pill waves-effect btn-icon"  data-bs-toggle="offcanvas"
                  data-bs-target="#offcanvasEditSubCategory" data-id="${full['id']}"><i class="icon-base ti tabler-edit icon-22px"></i></button>
              </div>
            `;
          }
        }
      ],
      select: {
        style: 'multi',
        selector: 'td:nth-child(2)'
      },
      // order: [0, 'desc'],
      displayLength: 7,
      layout: {
        topStart: {
          rowClass: 'row m-3 my-0 justify-content-between',
          features: [
            {
              search: {
                placeholder: 'Search Category',
                text: '_INPUT_'
              }
            }
          ]
        },
        topEnd: {
          rowClass: 'row m-3 my-0 justify-content-between',
          features: {
            pageLength: {
              menu: [7, 10, 25, 50, 100],
              text: '_MENU_'
            },
            buttons: [
              {
                text: `<i class="icon-base ti tabler-plus icon-16px me-0 me-sm-2"></i><span class="d-none d-sm-inline-block">Add Sub Category</span>`,
                className: 'add-new btn btn-primary',
                attr: {
                  'data-bs-toggle': 'offcanvas',
                  'data-bs-target': '#offcanvasAddSubCategory'
                }
              }
            ]
          }
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
              return 'Details of ' + data['sub_category'];
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

  // Event delegation for edit button clicks
  document.addEventListener('click', function(e) {
    if (e.target.closest('[data-bs-target="#offcanvasEditSubCategory"]')) {
      // Get the row data from DataTable
      const row = dt_category.row(e.target.closest('tr'));
      const data = row.data();
      
      // Populate the edit form with data
      setTimeout(() => {
        const titleInput = document.getElementById('sub-category-title');
        const statusSelect = document.getElementById('sub-category-status');
        
        if (titleInput) titleInput.value = data.sub_category || '';
        if (statusSelect) statusSelect.value = data.status || 'active';
        
        // Set Quill content if available
        if (quillEdit && data.sub_category_desc) {
          quillEdit.root.innerHTML = data.sub_category_desc;
        }
        
        // If Quill is not ready yet, set content after a longer delay
        if (!quillEdit && data.sub_category_desc) {
          setTimeout(() => {
            if (quillEdit && data.sub_category_desc) {
              quillEdit.root.innerHTML = data.sub_category_desc;
            }
          }, 300);
        }
      }, 100); // Small delay to ensure offcanvas is shown
    }
  });

  // Filter form control to default size
  // ? setTimeout used for category-list table initialization
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
      { selector: '.dt-buttons.btn-group', classToAdd: 'mb-md-0 mb-6' },
      { selector: '.dt-search .form-control', classToRemove: 'form-control-sm', classToAdd: 'ms-0' },
      { selector: '.dt-search', classToAdd: 'mb-0 mb-md-6' },
      { selector: '.dt-length .form-select', classToRemove: 'form-select-sm' },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2', classToAdd: 'border-top' },
      { selector: '.dt-layout-end', classToAdd: 'gap-md-2 gap-0 mt-0' },
      { selector: '.dt-layout-start', classToAdd: 'mt-0' },
      { selector: '.dt-layout-full', classToRemove: 'col-md col-12', classToAdd: 'table-responsive' }
    ];

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
  
  //For form validation
  const addSubCategoryForm = document.getElementById('addSubCategoryForm');

  if (addSubCategoryForm) {
    //Add New customer Form Validation
    const fv = FormValidation.formValidation(addSubCategoryForm, {
      fields: {
        subcategoryTitle: {
          validators: {
            notEmpty: {
              message: 'Please enter category title'
            }
          }
        },
        category_id: {
          validators: {
            notEmpty: {
              message: 'Please select category'
            }
          }
        },
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          // Use this for enabling/changing valid/invalid class
          eleValidClass: 'is-valid',
          rowSelector: function (field, ele) {
            // field is the field name & ele is the field element
            return '.form-control-validation';
          }
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        // Don't use defaultSubmit, we'll handle submission manually
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    });
    
    // Handle form submission with Quill editor content
    fv.on('core.form.valid', function() {

      // Check if Quill editor exists
      if (quillAdd) {
        // Get the content of the Quill editor
        const quillContentAdd = quillAdd.root.innerHTML;
        
        // Set the content to the hidden input
        const hiddenInput = document.getElementById('sub-category-description-hidden');
        hiddenInput.value = quillContentAdd;
      }
      
      // Submit the form manually
      addSubCategoryForm.submit();
    });
  }


  const updateSubCategoryForm = document.getElementById('updateSubCategoryForm');

  if (updateSubCategoryForm) {
    //Add New customer Form Validation
    const fvEdit = FormValidation.formValidation(updateSubCategoryForm, {
      fields: {
        subcategoryTitle: {
          validators: {
            notEmpty: {
              message: 'Please enter category title'
            }
          }
        },
        category_id: {
          validators: {
            notEmpty: {
              message: 'Please select category'
            }
          }
        },
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          // Use this for enabling/changing valid/invalid class
          eleValidClass: 'is-valid',
          rowSelector: function (field, ele) {
            // field is the field name & ele is the field element
            return '.form-control-validation';
          }
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        // Don't use defaultSubmit, we'll handle submission manually
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    });
    
    // Handle form submission with Quill editor content
    fvEdit.on('core.form.valid', function() {

      // Check if Quill editor exists
      if (window.quillEdit) {
        // Get the content of the Quill editor
        const quillContentEdit = window.quillEdit.root.innerHTML;
        // Set the content to the hidden input
        const hiddenInputEdit = document.getElementById('sub-category-description-hidden-edit');

        hiddenInputEdit.value = quillContentEdit;
      }
      
      // Submit the form manually
      updateSubCategoryForm.submit();
    });
  }
});
