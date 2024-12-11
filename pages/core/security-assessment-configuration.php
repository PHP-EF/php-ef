<?php
  require_once(__DIR__.'/../../inc/inc.php');
  if ($ib->rbac->checkAccess("ADMIN-SECASS") == false) {
    die();
  }
?>

<style>
.card {
  padding: 10px;
}
</style>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-12 mx-auto">
      <h2 class="h3 mb-4 page-title">Security Assessment Report Generator Configuration</h2>

      <div class="my-4">
        <div class="card border-secondary">
          <div class="card-title">
            <h5>Threat Actor Configuration</h5>
            <p>Use the following to configure the known Threat Actors. This allows populating Threat Actors with Images / Report Links during Security Assessment Report generation.</p>
          </div>
          <table  data-url="/api?f=getThreatActorConfig"
            data-toggle="table"
            data-search="true"
            data-filter-control="true"
            data-show-refresh="true"
            data-pagination="true"
            data-toolbar="#toolbar"
            data-sort-name="Name"
            data-sort-order="asc"
            data-page-size="25"
            data-buttons="threatActorButtons"
            data-buttons-order="btnAddThreatActor,refresh"
            class="table table-striped" id="threatActorTable">

            <thead>
              <tr>
                <th data-field="state" data-checkbox="true"></th>
                <th data-field="Name" data-sortable="true">Threat Actor</th>
                <th data-field="PNG" data-sortable="true">PNG Image</th>
                <th data-field="SVG" data-sortable="true">SVG Image</th>
                <th data-field="URLStub" data-sortable="true">URL Stub</th>
                <th data-formatter="threatActorActionFormatter" data-events="actionEvents">Actions</th>
              </tr>
            </thead>
          </table>
        </div>

        <hr>

        <div class="card border-secondary">
          <div class="card-title">
            <h5>Template Configuration</h5>
            <p>Use the following to configure the template for the Security Assessment Report Generator.</p>
          </div>
          <table  data-url="/api?f=getSecurityAssessmentTemplates"
            data-toggle="table"
            data-search="true"
            data-filter-control="true"
            data-show-refresh="true"
            data-pagination="true"
            data-toolbar="#toolbar"
            data-sort-name="Status"
            data-sort-order="asc"
            data-page-size="25"
            data-buttons="templateButtons"
            data-buttons-order="btnAddTemplate,refresh"
            class="table table-striped" id="templateTable">

            <thead>
              <tr>
                <th data-field="state" data-checkbox="true"></th>
                <th data-field="Status" data-sortable="true">Status</th>
                <th data-field="TemplateName" data-sortable="true">Name</th>
                <th data-field="Description" data-sortable="true">Description</th>
                <th data-field="ThreatActorSlide" data-sortable="true">Threat Actor Slide</th>
                <th data-field="FileName" data-sortable="true">File Name</th>
                <th data-field="Created" data-sortable="true">Upload Date</th>
                <th data-formatter="templateActionFormatter" data-events="actionEvents">Actions</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- Edit Threat Actor Modal -->
<div class="modal fade" id="editThreatActorModal" tabindex="-1" role="dialog" aria-labelledby="editThreatActorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editThreatActorModalLabel">Threat Actor Information</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"></span>
        </button>
      </div>
      <div class="modal-body" id="editThreatActorModalBody">
        <div class="form-group">
          <div class="form-group" hidden>
            <input type="text" class="form-control info-field" id="threatActorId" aria-describedby="threatActorIdHelp" name="threatActorId" disabled>
          </div>
          <label for="threatActorName">Threat Actor Name</label>
          <input type="text" class="form-control" id="threatActorName" aria-describedby="threatActorNameHelp" disabled>
          <small id="threatActorNameHelp" class="form-text text-muted">The name of the Threat Actor.</small>
        </div>
        <div class="form-group">
          <label for="threatActorURLStub">URL Stub</label>
          <input type="text" class="form-control" id="threatActorURLStub" aria-describedby="threatActorURLStubHelp">
          <small id="threatActorURLStubHelp" class="form-text text-muted">The Threat Actor Report <b>URL</b> to use when generating Security Assessment reports.</small>
        </div>
        <div class="form-group row">
          <label for="threatActorIMGSVG" class="col-form-label">SVG Image</label>
          <div class="col-sm-5">
            <input type="file" class="form-control" id="threatActorIMGSVG" accept=".svg" aria-describedby="threatActorIMGSVGHelp">
            <small id="threatActorIMGSVGHelp" class="form-text text-muted">Upload an SVG image.</small>
          </div>
          <div class="col-sm-5">
            <img id="imagePreviewSVG" src="" alt="PNG Image Preview" style="display:none; margin-top: 10px; max-width: 100%;">
          </div>
        </div>
        <div class="form-group row">
          <label for="threatActorIMGPNG" class="col-form-label">PNG Image</label>
          <div class="col-sm-5">
            <input type="file" class="form-control" id="threatActorIMGPNG" accept=".png" aria-describedby="threatActorIMGPNGHelp">
            <small id="threatActorIMGPNGHelp" class="form-text text-muted">Upload an PNG image.</small>
          </div>
          <div class="col-sm-5">
            <img id="imagePreviewPNG" src="" alt="PNG Image Preview" style="display:none; margin-top: 10px; max-width: 100%;">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary" id="editThreatActorSubmit">Save</button>
      </div>
    </div>
  </div>
</div>

<!-- New Threat Actor Modal -->
<div class="modal fade" id="newThreatActorModal" tabindex="-1" role="dialog" aria-labelledby="newThreatActorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newThreatActorModalLabel">New Threat Actor Wizard</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"></span>
        </button>
      </div>
      <div class="modal-body" id="newThreatActorModelBody">
	      <p>Enter the Threat Actor's Name below to add it to the list.</p>
        <div class="form-group">
          <label for="newThreatActorName">Threat Actor Name</label>
          <input type="text" class="form-control" id="newThreatActorName" aria-describedby="newThreatActorNameHelp">
          <small id="newThreatActorNameHelp" class="form-text text-muted">The name of the Threat Actor to add to the list.</small>
        </div>
        <div class="form-group">
          <label for="newThreatActorURLStub">URL Stub</label>
          <input type="text" class="form-control" id="newThreatActorURLStub" aria-describedby="newThreatActorURLStubHelp">
          <small id="newThreatActorURLStubHelp" class="form-text text-muted">The Threat Actor Report <b>URL</b> to use when generating Security Assessment reports.</small>
        </div>
        <div class="form-group row">
          <label for="newThreatActorIMGSVG" class="col-form-label">SVG Image</label>
          <div class="col-sm-5">
            <input type="file" class="form-control" id="newThreatActorIMGSVG" accept=".svg" aria-describedby="newThreatActorIMGSVGHelp">
            <small id="newThreatActorIMGSVGHelp" class="form-text text-muted">Upload an SVG image.</small>
          </div>
          <div class="col-sm-5">
            <img id="newImagePreviewSVG" src="" alt="PNG Image Preview" style="display:none; margin-top: 10px; max-width: 100%;">
          </div>
        </div>
        <div class="form-group row">
          <label for="newThreatActorIMGPNG" class="col-form-label">PNG Image</label>
          <div class="col-sm-5">
            <input type="file" class="form-control" id="newThreatActorIMGPNG" accept=".png" aria-describedby="newThreatActorIMGPNGHelp">
            <small id="newThreatActorIMGPNGHelp" class="form-text text-muted">Upload an PNG image.</small>
          </div>
          <div class="col-sm-5">
            <img id="newImagePreviewPNG" src="" alt="PNG Image Preview" style="display:none; margin-top: 10px; max-width: 100%;">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary" id="newThreatActorSubmit">Save</button>
      </div>
    </div>
  </div>
</div>

<!-- -------------------- -->

<!-- Edit Template Modal -->
<div class="modal fade" id="editTemplateModal" tabindex="-1" role="dialog" aria-labelledby="editTemplateModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editTemplateModalLabel">Template Information</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"></span>
        </button>
      </div>
      <div class="modal-body" id="editTemplateModalBody">
        <div class="form-group">
          <div class="form-group" hidden>
            <input type="text" class="form-control info-field" id="templateId" aria-describedby="templateIdHelp" name="templateId" disabled>
          </div>
          <label for="templateStatus">Template Status</label>
          <select id="templateStatus" class="form-select" aria-label="Template Status" aria-describedby="templateStatusHelp">
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
          <small id="templateStatusHelp" class="form-text text-muted">The current status of the template.</small>
        </div>
        <div class="form-group">
          <label for="templateName">Template Name</label>
          <input type="text" class="form-control info-field" id="templateName" aria-describedby="templateNameHelp" name="templateName">
          <small id="templateNameHelp" class="form-text text-muted">The name for the Security Assessment Template.</small>
        </div>
        <div class="form-group">
          <label for="templateDescription">Template Description</label>
          <input type="text" class="form-control info-field" id="templateDescription" aria-describedby="templateDescriptionHelp" name="templateDescription">
          <small id="templateDescriptionHelp" class="form-text text-muted">The description of the Security Assessment Template.</small>
        </div>
        <div class="form-group">
          <label for="templateThreatActorSlide">Threat Actor Slide</label>
          <input type="text" class="form-control info-field" id="templateThreatActorSlide" aria-describedby="templateThreatActorSlideHelp" name="templateThreatActorSlide">
          <small id="templateThreatActorSlideHelp" class="form-text text-muted">This is the Threat Actor template slide number.</small>
        </div>
        <div class="form-group row">
          <label for="templatePPTX" class="col-form-label">PowerPoint Template</label>
          <div class="col-sm-5">
            <input type="file" class="form-control" id="templatePPTX" accept=".pptx" aria-describedby="templatePPTXHelp">
            <small id="templatePPTXHelp" class="form-text text-muted">Upload a PowerPoint Template.</small>
          </div>
          <div class="col-sm-5">
            <img id="imagePreviewSVG" src="" alt="PNG Image Preview" style="display:none; margin-top: 10px; max-width: 100%;">
          </div>
        </div>
        <div class="form-group">
          <label for="templateFileName">Template File Name</label>
          <input type="text" class="form-control info-field" id="templateFileName" aria-describedby="templateFileNameHelp" name="templateFileName" disabled>
          <small id="templateFileNameHelp" class="form-text text-muted">The file name for the Security Assessment Template.</small>
        </div>
        <div class="form-group">
          <label for="templateUploadDate">Upload Date</label>
          <input type="text" class="form-control" id="templateUploadDate" aria-describedby="templateUploadDateHelp" disabled>
          <small id="templateUploadDateHelp" class="form-text text-muted">The upload date of this template.</small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary" id="editTemplateSubmit">Save</button>
      </div>
    </div>
  </div>
</div>

<!-- New Template Modal -->
<div class="modal fade" id="newTemplateModal" tabindex="-1" role="dialog" aria-labelledby="newTemplateModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newTemplateModalLabel">New Template Wizard</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"></span>
        </button>
      </div>
      <div class="modal-body" id="newTemplateModelBody">
	      <p>Enter the new template information below to add it to the list.</p>
        <div class="form-group">
          <label for="newTemplateStatus">Template Status</label>
          <select id="newTemplateStatus" class="form-select" aria-label="Template Status" aria-describedby="newTemplateStatusHelp">
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
          <small id="newTemplateStatusHelp" class="form-text text-muted">The status of the new template.</small>
        </div>
        <div class="form-group">
          <label for="newTemplateName">Template Name</label>
          <input type="text" class="form-control info-field" id="newTemplateName" aria-describedby="newTemplateNameHelp" name="newTemplateName">
          <small id="newTemplateNameHelp" class="form-text text-muted">The name for the Security Assessment Template.</small>
        </div>
        <div class="form-group">
          <label for="newTemplateDescription">Template Description</label>
          <input type="text" class="form-control info-field" id="newTemplateDescription" aria-describedby="newTemplateDescriptionHelp" name="newTemplateDescription">
          <small id="newTemplateDescriptionHelp" class="form-text text-muted">The description of the Security Assessment Template.</small>
        </div>
        <div class="form-group">
          <label for="newTemplateThreatActorSlide">Threat Actor Slide</label>
          <input type="text" class="form-control info-field" id="newTemplateThreatActorSlide" aria-describedby="newTemplateThreatActorSlideHelp" name="newTemplateThreatActorSlide">
          <small id="newTemplateThreatActorSlideHelp" class="form-text text-muted">This is the Threat Actor template slide number.</small>
        </div>
        <div class="form-group row">
          <label for="newTemplatePPTX" class="col-form-label">PowerPoint Template</label>
          <div class="col-sm-5">
            <input type="file" class="form-control" id="newTemplatePPTX" accept=".pptx" aria-describedby="newTemplatePPTXHelp">
            <small id="newTemplatePPTXHelp" class="form-text text-muted">Upload a PowerPoint Template.</small>
          </div>
          <div class="col-sm-5">
            <img id="imagePreviewSVG" src="" alt="PNG Image Preview" style="display:none; margin-top: 10px; max-width: 100%;">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary" id="newTemplateSubmit">Save</button>
      </div>
    </div>
  </div>
</div>

<script>
  function threatActorActionFormatter(value, row, index) {
    return [
      '<a class="editThreatActor" title="Edit">',
      '<i class="fa fa-pencil"></i>',
      '</a>&nbsp;',
      '<a class="deleteThreatActor" title="Delete">',
      '<i class="fa fa-trash"></i>',
      '</a>'
    ].join('')
  }

  function templateActionFormatter(value, row, index) {
    return [
      '<a class="editTemplate" title="Edit">',
      '<i class="fa fa-pencil"></i>',
      '</a>&nbsp;',
      '<a class="deleteTemplate" title="Delete">',
      '<i class="fa fa-trash"></i>',
      '</a>'
    ].join('')
  }

  function threatActorButtons() {
    return {
      btnAddThreatActor: {
        text: "Add Threat Actor",
        icon: "bi-plus-lg",
        event: function() {
          $('#newThreatActorModal').modal('show');
          $('#newThreatActorModal input').val('');
          $('#newImagePreviewSVG, #newImagePreviewPNG').attr('src','').css('display','none');
        },
        attributes: {
          title: "Add a new Threat Actor",
          style: "background-color:#4bbe40;border-color:#4bbe40;"
        }
	    }
    }
  }

  function templateButtons() {
    return {
      btnAddTemplate: {
        text: "Add Security Assessment Template",
        icon: "bi-plus-lg",
        event: function() {
          $('#newTemplateModal').modal('show');
          $('#newTemplateModal input').val('');
        },
        attributes: {
          title: "Add a new Security Assessment Template",
          style: "background-color:#4bbe40;border-color:#4bbe40;"
        }
	    }
    }
  }

  function listThreatActor(row) {
    $('#editThreatActorModal input').val('');
    $('#threatActorId').val(row['id']);
    $('#imagePreviewSVG, #imagePreviewPNG').attr('src','').css('display','none');
    $('#threatActorName').val(row['Name']);
    if (row['SVG'] != "") {
      imagePreview('imagePreviewSVG','/assets/images/Threat Actors/Uploads/'+row['PNG']);
    }
    if (row['PNG'] != "") {
      imagePreview('imagePreviewPNG','/assets/images/Threat Actors/Uploads/'+row['PNG']);
    }
    $('#threatActorURLStub').val(row['URLStub']);
  }

  function listTemplate(row) {
    $('#editTemplateModal input').val('');
    $('#templateId').val(row['id']);
    $('#templateStatus').val(row['Status']);
    $('#templateName').val(row['TemplateName']);
    $('#templateDescription').val(row['Description']);
    $('#templateFileName').val(row['FileName']);
    $('#templateThreatActorSlide').val(row['ThreatActorSlide']);
    $('#templateUploadDate').val(row['Created']);
  }

  function imagePreview(elem,img) {
    const preview = document.getElementById(elem);
    if (img) {
      preview.src = img;
      preview.style.display = 'block'; // Show the image preview
    } else {
      preview.src = '';
      preview.style.display = 'none'; // Hide the image preview if no file is selected
    }
  }

  $('#threatActorIMGPNG, #threatActorIMGSVG, #newThreatActorIMGPNG, #newThreatActorIMGSVG').on('change', function(event) {
    const file = event.target.files[0];
    let target = '';
    if (event.target.id == 'threatActorIMGPNG') {
      target = 'imagePreviewPNG';
    }
    if (event.target.id == 'threatActorIMGSVG') {
      target = 'imagePreviewSVG';
    }
    if (event.target.id == 'newThreatActorIMGPNG') {
      target = 'newImagePreviewPNG';
    }
    if (event.target.id == 'newThreatActorIMGSVG') {
      target = 'newImagePreviewSVG';
    }
    const preview = document.getElementById(target);
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block'; // Show the image preview
        };
        reader.readAsDataURL(file);
    } else {
        preview.src = '';
        preview.style.display = 'none'; // Hide the image preview if no file is selected
    }
  });

  window.actionEvents = {
    'click .editThreatActor': function (e, value, row, index) {
      listThreatActor(row);
      $('#editThreatActorModal').modal('show');
    },
    'click .deleteThreatActor': function (e, value, row, index) {
      if(confirm("Are you sure you want to delete "+row.Name+" from the list of Threat Actors? This is irriversible.") == true) {
        var postArr = {}
        postArr.id = row.id;
        $.post( "/api?f=removeThreatActorConfig", postArr).done(function( data, status ) {
          if (data['Status'] == 'Success') {
            toast(data['Status'],"",data['Message'],"success");
            $('#threatActorTable').bootstrapTable('refresh');
          } else if (data['Status'] == 'Error') {
            toast(data['Status'],"",data['Message'],"danger","30000");
          } else {
            toast("Error","","Failed to remove Threat Actor: "+row.Name,"danger","30000");
          }
        }).fail(function( data, status ) {
            toast("API Error","","Failed to remove Threat Actor: "+row.Name,"danger","30000");
        })
      }
    },
    'click .editTemplate': function (e, value, row, index) {
      listTemplate(row);
      $('#editTemplateModal').modal('show');
    },
    'click .deleteTemplate': function (e, value, row, index) {
      if(confirm("Are you sure you want to delete "+row.TemplateName+" from the list of Templates? This is irriversible.") == true) {
        var postArr = {}
        postArr.id = row.id;
        $.post( "/api?f=removeSecurityAssessmentTemplate", postArr).done(function( data, status ) {
          if (data['Status'] == 'Success') {
            toast(data['Status'],"",data['Message'],"success");
            $('#templateTable').bootstrapTable('refresh');
          } else if (data['Status'] == 'Error') {
            toast(data['Status'],"",data['Message'],"danger","30000");
          } else {
            toast("Error","","Failed to remove Template: "+row.TemplateName,"danger","30000");
          }
        }).fail(function( data, status ) {
            toast("API Error","","Failed to remove Template: "+row.TemplateName,"danger","30000");
        })
      }
    }
  }

  $(document).on('click', '#newThreatActorSubmit', function(event) {
    const svgFiles = $('#newThreatActorIMGSVG')[0].files;
    const pngFiles = $('#newThreatActorIMGPNG')[0].files;
    const threatActorFileName = $('#newThreatActorName').val().toLowerCase().replace(/ /g, '-');
    var postArr = {}
    if (svgFiles[0]) {
      postArr.SVG = threatActorFileName;
    }
    if (svgFiles[0]) {
      postArr.PNG = threatActorFileName;
    }
    postArr.name = encodeURIComponent($('#newThreatActorName').val())
    postArr.URLStub = encodeURIComponent($('#newThreatActorURLStub').val())
    $.post( "/api?f=newThreatActorConfig", postArr).done(function( data, status ) {
      if (data['Status'] == 'Success') {
        toast(data['Status'],"",data['Message'],"success");
        $('#threatActorTable').bootstrapTable('refresh');
        if (svgFiles.length > 0 || pngFiles.length > 0) {
          const formData = new FormData();
          if (svgFiles[0]) {
            formData.append('svgImage', svgFiles[0]);
            formData.append('svgFileName', threatActorFileName);
          }
          if (pngFiles[0]) {
            formData.append('pngImage', pngFiles[0]);
            formData.append('pngFileName', threatActorFileName);
          }
          $.ajax({
            url: '/api?f=uploadThreatActorImage', // Replace with your PHP API endpoint
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
              toast('Success',"","Uploaded images","success");
              console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
              toast('Error',"","Error submitting images","danger");
              console.error(errorThrown);
            }
          });
        }
      } else if (data['Status'] == 'Error') {
        toast(data['Status'],"",data['Message'],"danger","30000");
      } else {
        toast("Error","","Failed to add new Threat Actor","danger","30000");
      }
    }).fail(function( data, status ) {
        toast("API Error","","Failed to add new Threat Actor","danger","30000");
    }).always(function( data, status) {
      $('#newThreatActorModal').modal('hide');
    })
  });

  $(document).on('click', '#editThreatActorSubmit', function(event) {
    const svgFiles = $('#threatActorIMGSVG')[0].files;
    const pngFiles = $('#threatActorIMGPNG')[0].files;
    const threatActorFileName = $('#threatActorName').val().toLowerCase().replace(/ /g, '-');
    var postArr = {}
    if (svgFiles[0]) {
      postArr.SVG = threatActorFileName;
    }
    if (svgFiles[0]) {
      postArr.PNG = threatActorFileName;
    }
    postArr.id = encodeURIComponent($('#threatActorId').val())
    postArr.name = encodeURIComponent($('#threatActorName').val())
    postArr.URLStub = encodeURIComponent($('#threatActorURLStub').val())
    $.post( "/api?f=setThreatActorConfig", postArr).done(function( data, status ) {
      if (data['Status'] == 'Success') {
        toast(data['Status'],"",data['Message'],"success");
        $('#threatActorTable').bootstrapTable('refresh');
        if (svgFiles.length > 0 || pngFiles.length > 0) {
          const formData = new FormData();
          if (svgFiles[0]) {
            formData.append('svgImage', svgFiles[0]);
            formData.append('svgFileName', threatActorFileName);
          }
          if (pngFiles[0]) {
            formData.append('pngImage', pngFiles[0]);
            formData.append('pngFileName', threatActorFileName);
          }
          $.ajax({
            url: '/api?f=uploadThreatActorImage', // Replace with your PHP API endpoint
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
              toast('Success',"","Uploaded images","success");
              console.log(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
              toast('Error',"","Error submitting images","danger");
              console.error(errorThrown);
            }
          });
        }
      } else if (data['Status'] == 'Error') {
        toast(data['Status'],"",data['Message'],"danger","30000");
      } else {
        toast("Error","","Failed to update Threat Actor: "+postArr.name,"danger","30000");
      }
    }).fail(function( data, status ) {
        toast("API Error","","Failed to update Threat Actor: "+postArr.name,"danger","30000");
    }).always(function( data, status) {
      $('#editThreatActorModal').modal('hide');
    })
  });

  $(document).on('click', '#newTemplateSubmit', function(event) {
    const templateFiles = $('#newTemplatePPTX')[0].files;

    var postArr = {}
    postArr.Status = encodeURIComponent($('#newTemplateStatus').val());
    postArr.TemplateName = encodeURIComponent($('#newTemplateName').val());
    postArr.Description = encodeURIComponent($('#newTemplateDescription').val());
    postArr.ThreatActorSlide = encodeURIComponent($('#newTemplateThreatActorSlide').val());
    if (templateFiles[0]) {
      postArr.FileName = $('#newTemplateName').val().toLowerCase().replace(/ /g, '-');
    }
    $.post( "/api?f=newSecurityAssessmentTemplate", postArr).done(function( data, status ) {
      if (data['Status'] == 'Success') {
        toast(data['Status'],"",data['Message'],"success");
        $('#templateTable').bootstrapTable('refresh');
        if (templateFiles.length > 0) {
          const formData = new FormData();
          formData.append('pptx', templateFiles[0]);
          formData.append('TemplateName', postArr.FileName);
          toast("Uploading","Please wait..","Uploading Template..","info","30000");
          $.ajax({
            url: '/api?f=uploadSecurityAssessmentTemplate', // Replace with your PHP API endpoint
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
              if (response['Status'] == 'Success') {
                toast(response['Status'],"",response['Message'],"success","30000");
              } else if (response['Status'] == 'Error') {
                toast(response['Status'],"",response['Message'],"danger","30000");
              } else {
                toast("Error","","Failed to add new template","danger","30000");
              }
            },
            error: function(jqXHR, textStatus, errorThrown) {
              toast('Error',"","Error uploading template","danger");
              console.error(errorThrown);
            }
          });
        }
      } else if (data['Status'] == 'Error') {
        toast(data['Status'],"",data['Message'],"danger","30000");
      } else {
        toast("Error","","Failed to add new template","danger","30000");
      }
    }).fail(function( data, status ) {
        toast("API Error","","Failed to add new template","danger","30000");
    }).always(function( data, status) {
      $('#newTemplateModal').modal('hide');
    })
  });

  $(document).on('click', '#editTemplateSubmit', function(event) {
    const templateFiles = $('#templatePPTX')[0].files;

    var postArr = {}
    postArr.id = encodeURIComponent($('#templateId').val());
    postArr.Status = encodeURIComponent($('#templateStatus').val());
    postArr.TemplateName = encodeURIComponent($('#templateName').val());
    postArr.Description = encodeURIComponent($('#templateDescription').val());
    postArr.ThreatActorSlide = encodeURIComponent($('#templateThreatActorSlide').val());
    if (templateFiles[0]) {
      postArr.FileName = $('#templateName').val().toLowerCase().replace(/ /g, '-');
    }
    $.post( "/api?f=setSecurityAssessmentTemplate", postArr).done(function( data, status ) {
      if (data['Status'] == 'Success') {
        toast(data['Status'],"",data['Message'],"success");
        $('#templateTable').bootstrapTable('refresh');
        if (templateFiles.length > 0) {
          const formData = new FormData();
          formData.append('pptx', templateFiles[0]);
          formData.append('TemplateName', postArr.FileName);
          toast("Uploading","Please wait..","Uploading Template..","info","30000");
          $.ajax({
            url: '/api?f=uploadSecurityAssessmentTemplate', // Replace with your PHP API endpoint
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
              if (response['Status'] == 'Success') {
                toast(response['Status'],"",response['Message'],"success","30000");
              } else if (response['Status'] == 'Error') {
                toast(response['Status'],"",response['Message'],"danger","30000");
              } else {
                toast("Error","","Failed to edit template","danger","30000");
              }
            },
            error: function(jqXHR, textStatus, errorThrown) {
              toast('Error',"","Error uploading template","danger");
              console.error(errorThrown);
            }
          });
        }
      } else if (data['Status'] == 'Error') {
        toast(data['Status'],"",data['Message'],"danger","30000");
      } else {
        toast("Error","","Failed to edit template","danger","30000");
      }
    }).fail(function( data, status ) {
        toast("API Error","","Failed to edit template","danger","30000");
    }).always(function( data, status) {
      $('#editTemplateModal').modal('hide');
    })
  });

</script>
