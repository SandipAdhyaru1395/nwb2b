/**
 * App eCommerce customer all
 */

'use strict';

(function () {

  const editCustomerForm = document.getElementById('editCustomerForm');

  // Add New customer Form Validation
  if (!editCustomerForm) return;
  const fv = FormValidation.formValidation(editCustomerForm, {
    fields: {
      name: {
        validators: {
          notEmpty: {
            message: 'Please enter fullname '
          }
        }
      },
      companyName: {
        validators: {
          notEmpty: {
            message: 'Please enter company name'
          }
        }
      },
      email: {
        validators: {
          notEmpty: {
            message: 'Please enter your email'
          },
          emailAddress: {
            message: 'Please enter a valid email address'
          }
        }
      },
      mobile: {
        validators: {
          notEmpty: {
            message: 'Please enter mobile number'
          },
          numeric: {
            message: 'The value is not a valid number'
          },
          stringLength: {
            min: 10,
            max: 10,
            message: 'Mobile number must be 10 digits'
          }
        },
      },
      status: {
        validators: {
          notEmpty: {
            message: 'Please select status'
          }
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
    init: instance => {
        instance.on('plugins.message.placed', function (e) {
          if (e.element.parentElement.classList.contains('input-group')) {
            e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
          }
        });
      }
  });
})();
