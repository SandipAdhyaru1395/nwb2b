/**
 * App eCommerce Add Product Script
 */
'use strict';

//Javascript to handle the e-commerce product add page

(function () {
  // Comment editor

  let quill;
  const commentEditor = document.querySelector('.comment-editor');

  if (commentEditor) {
    quill = new Quill(commentEditor, {
      modules: {
        toolbar: '.comment-toolbar'
      },
      placeholder: 'Product Description',
      theme: 'snow'
    });
  }

  // previewTemplate: Updated Dropzone default previewTemplate

  // ! Don't change it unless you really know what you are doing

  const previewTemplate = `<div class="dz-preview dz-file-preview">
<div class="dz-details">
  <div class="dz-thumbnail">
    <img data-dz-thumbnail>
    <span class="dz-nopreview">No preview</span>
    <div class="dz-success-mark"></div>
    <div class="dz-error-mark"></div>
    <div class="dz-error-message"><span data-dz-errormessage></span></div>
    <div class="progress">
      <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuemin="0" aria-valuemax="100" data-dz-uploadprogress></div>
    </div>
  </div>
  <div class="dz-filename" data-dz-name></div>
  <div class="dz-size" data-dz-size></div>
</div>
</div>`;

  // ? Start your code from here

  // Basic Dropzone

  const dropzoneBasic = document.querySelector('#dropzone-basic');
  if (dropzoneBasic) {
    const myDropzone = new Dropzone(dropzoneBasic, {
      url: "#",
      previewTemplate: previewTemplate,
      autoProcessQueue: false,
      parallelUploads: 1,
      maxFilesize: 5,
      acceptedFiles: '.jpg,.jpeg,.png',
      addRemoveLinks: true,
      maxFiles: 1
    });

    myDropzone.on("addedfile", function (file) {
      const dataTransfer = new DataTransfer();
      dataTransfer.items.add(file);
      document.getElementById("productImage").files = dataTransfer.files;
    });
  }

  // Basic Tags

  const tagifyBasicEl = document.querySelector('#ecommerce-product-tags');
  const TagifyBasic = new Tagify(tagifyBasicEl);

  //For form validation
  const editProductForm = document.getElementById('editProductForm');

  if (editProductForm) {
    //Add New customer Form Validation
    const fv = FormValidation.formValidation(editProductForm, {
      fields: {
        brands: {
          selector: 'select[name="brands[]"]', // target the checkbox group
          validators: {
            notEmpty: {
              message: 'Please select at least one brand'
            }
          }
        },
        step: {
          validators: {
            notEmpty: {
              message: 'Step quantity is required'
            },
            numeric: {
              message: 'Step quantity must be a number'
            },
            greaterThan: {
              message: 'Must be greater than 0',
              min: 1
            }
          }
        },
        productTitle: {
          validators: {
            notEmpty: {
              message: 'Please enter product name'
            }
          }
        },
        productSku: {
          validators: {
            notEmpty: {
              message: 'Please enter product sku'
            }
          }
        },
        productPrice: {
          validators: {
            notEmpty: {
              message: 'Please enter product price'
            },
            numeric: {
              message: 'The discounted price must be a number'
            },
          }
        },
        brand_id: {
          validators: {
            notEmpty: {
              message: 'Brand is required'
            },
          }
        },
        categories: {
          selector: 'input[name="categories[]"]', // target the checkbox group
          validators: {
            notEmpty: {
              message: 'Please select at least one category'
            }
          }
        }
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
    fv.on('core.form.valid', function () {
      let content = quill.root.innerHTML;
      if (content === '<p><br></p>') {
        content = ''; // Treat as empty
      }
      document.getElementById("productDescription").value = content;
      // Submit the form manually
      editProductForm.submit();
    });
  }
})();

//Jquery to handle the e-commerce product edit page
$(function () {
  // Select2 for all dropdowns
  var select2 = $('.select2');
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      var isBrandsDropdown = $this.attr('name') === 'brands[]';

      $this.wrap('<div class="position-relative"></div>').select2({
        dropdownParent: $this.parent(),
        placeholder: $this.data('placeholder'), // for dynamic placeholder
        closeOnSelect: isBrandsDropdown ? false : true, // keep dropdown open only for brands
        templateResult: function (data) {
          if (!data.id) {
            return data.text;
          }
          
          // Only add checkboxes for brands dropdown
          if (isBrandsDropdown) {
            // Create the checkbox input
            var $result = $('<span class="brand-option"></span>');
            var checkbox = $('<input class="form-check-input" type="checkbox" style="margin-right:5px;" data-value="' + data.id + '" />');

            // Check if the option is selected and update checkbox state
            if (data.selected) {
              checkbox.prop('checked', true);
            }

            $result.append(checkbox);
            $result.append(' ' + data.text); // Add the option text

            return $result;
          } else {
            // Regular dropdown without checkboxes
            return data.text;
          }
        },
        templateSelection: function (data) {
          return data.text;
        }
      });

      // Only add checkbox event handlers for brands dropdown
      if (isBrandsDropdown) {
        // Keep checkboxes in sync with selections
        $this.on('select2:select', function (e) {
          var el = $(e.params.data.element);
          el.prop('selected', true);
          
          // Clear the search input after selection
          setTimeout(function() {
            // $this.select2('close');
            // Clear the search input
            $('.select2-search__field').val('').focus();
          }, 50);
          
          // Update the specific checkbox in the dropdown to show as checked
          setTimeout(function() {
            $('.select2-dropdown').find('input[data-value="' + e.params.data.id + '"]').prop('checked', true);
          }, 10);
        });

        $this.on('select2:unselect', function (e) {
          var el = $(e.params.data.element);
          el.prop('selected', false);

          // Uncheck the specific checkbox when the item is unselected
          setTimeout(function() {
            $('.select2-dropdown').find('input[data-value="' + e.params.data.id + '"]').prop('checked', false);
          }, 10);
        });
      }
    });
  }
});
