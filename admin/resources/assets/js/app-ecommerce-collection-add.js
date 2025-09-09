/**
 * App eCommerce Add Collection Script
 */
'use strict';

//Javascript to handle the e-commerce collection add page

(function () {

  // Basic Dropzone
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
      document.getElementById("collectionImage").files = dataTransfer.files;
    });
  }

  // Basic Tags

  const tagifyBasicEl = document.querySelector('#collection-tags');
  const TagifyBasic = new Tagify(tagifyBasicEl);

  //For form validation
  const addCollectionForm = document.getElementById('addCollectionForm');

  if (addCollectionForm) {
    //Add New customer Form Validation
    const fv = FormValidation.formValidation(addCollectionForm, {
      fields: {
        collectionTitle: {
          validators: {
            notEmpty: {
              message: 'Please enter collection name'
            }
          }
        },
        brand_id: {
          validators: {
            notEmpty: {
              message: 'Brand is required'
            },
          }
        },
        collectionImage: {
          validators: {
            notEmpty: {
              message: 'Please upload collection image'
            }
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
     
      addCollectionForm.submit();
    });
  }
})();

//Jquery to handle the e-commerce product add page

$(function () {
  // Select2
  var select2 = $('.select2');
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      $this.wrap('<div class="position-relative"></div>').select2({
        dropdownParent: $this.parent(),
        placeholder: $this.data('placeholder') // for dynamic placeholder
      });
    });
  }

});
