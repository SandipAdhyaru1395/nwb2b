/**
 * App Settings Script
 */
'use strict';

//Javascript to handle the e-commerce settings page

document.addEventListener('DOMContentLoaded', function (e) {


  // Dropzone

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
      const input = document.getElementById("companyLogo");
      if (input) input.files = dataTransfer.files;
    });
  }

  // End Dropzone
  
});
