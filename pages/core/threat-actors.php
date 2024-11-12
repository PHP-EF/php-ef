<?php
  require_once(__DIR__.'/../../inc/inc.php');
  if (CheckAccess(null,"ADMIN-SECASS") == false) {
    die();
  }

?>

<style>
pre {
  background-color: #000;
  overflow: auto;
  font-family: 'Monaco', monospace;
  padding: 0 1em;
}

code {
  font-family: Monaco, monospace;
  font-size: $base-font-size;
  line-height: 100%;
 /background-color: #000;/
  padding: 0.2em;
  letter-spacing: -0.05em;
  word-break: normal;
  /border-radius: 5px;/
}

pre code {
  border: none;
  background: none;
  font-size: $base-font-size * 0.875;
  line-height: 1em;
  letter-spacing: normal;
  word-break: break-all;
}
</style>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-10 mx-auto">
      <div class="my-4">
        <h5 class="mb-0 mt-5">Threat Actor Configuration</h5>
        <p>Use the following to configure the known Threat Actors. This allows populating Threat Actors with Images / Report Links during Security Assessment Report generation.</p>
        <table  data-url="/api?function=getThreatActorConfig"
          data-toggle="table"
          data-search="true"
          data-filter-control="true" 
          data-show-refresh="true"
          data-pagination="true"
          data-toolbar="#toolbar"
          data-sort-name="Name"
          data-sort-order="asc"
          data-page-size="25"
          class="table table-striped" id="threatActorTable">

          <thead>
            <tr>
              <th data-field="state" data-checkbox="true"></th>
              <th data-field="Name" data-sortable="true">Threat Actor</th>
              <th data-field="IMG" data-sortable="true">Image</th>
              <th data-field="URLStub" data-sortable="true">URL Stub</th>
              <th data-formatter="actionFormatter" data-events="actionEvents">Actions</th>
            </tr>
          </thead>
          <tbody id="threatActorConfig"></tbody>
          <div class="text-left">
            <button class="btn btn-success" id="newThreatActor">New Threat Actor</button>
          </div>
        </table>
      </div>
    </div>
  </div>
</div>


<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"></span>
        </button>
      </div>
      <div class="modal-body" id="editModelBody">
        <h4>Threat Actor Information</h4>
        <div class="form-group">
          <label for="threatActorName">Threat Actor Name</label>
          <input type="text" class="form-control" id="threatActorName" aria-describedby="threatActorNameHelp" disabled>
          <small id="threatActorNameHelp" class="form-text text-muted">The name of the Threat Actor to add to the list.</small>
        </div>
        <div class="form-group">
          <label for="threatActorIMG">Image</label>
          <input type="text" class="form-control" id="threatActorIMG" aria-describedby="threatActorIMGHelp">
          <small id="threatActorIMGHelp" class="form-text text-muted">The Threat Actor image to use when generating Security Assessment reports.</small>
        </div>
        <div class="form-group">
          <label for="threatActorURLStub">URL Stub</label>
          <input type="text" class="form-control" id="threatActorURLStub" aria-describedby="threatActorURLStubHelp">
          <small id="threatActorURLStubHelp" class="form-text text-muted">The Threat Actor Report <b>URL Stub</b> to use when generating Security Assessment reports.</small>
        </div>
        <button class="btn btn-primary" id="editThreatActorSubmit">Submit</button>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
          <label for="newThreatActorIMG">Image</label>
          <input type="text" class="form-control" id="newThreatActorIMG" aria-describedby="newThreatActorIMGHelp">
          <small id="newThreatActorIMGHelp" class="form-text text-muted">The Threat Actor image to use when generating Security Assessment reports.</small>
        </div>
        <div class="form-group">
          <label for="newThreatActorURLStub">URL Stub</label>
          <input type="text" class="form-control" id="newThreatActorURLStub" aria-describedby="newThreatActorURLStubHelp">
          <small id="newThreatActorURLStubHelp" class="form-text text-muted">The Threat Actor Report <b>URL Stub</b> to use when generating Security Assessment reports.</small>
        </div>
        <button class="btn btn-primary" id="newThreatActorSubmit">Submit</button>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>



<script>
  function actionFormatter(value, row, index) {
    return [
      '<a class="edit" title="Edit">',
      '<i class="fa fa-pencil"></i>',
      '</a>&nbsp;',
      '<a class="delete" title="Delete">',
      '<i class="fa fa-trash"></i>',
      '</a>'
    ].join('')
  }

  function listThreatActor(row) {
    $('#threatActorName').val(row['Name']);
    $('#threatActorIMG').val(row['IMG']);
    $('#threatActorURLStub').val(row['URLStub']);
  }

  window.actionEvents = {
    'click .edit': function (e, value, row, index) { 
      listThreatActor(row);
      $('#editModal').modal('show');
    },
    'click .delete': function (e, value, row, index) {
      if(confirm("Are you sure you want to delete "+row.Name+" from the list of Threat Actors? This is irriversible.") == true) {
        var postArr = {}
        postArr.name = row.Name;
        $.post( "/api?function=removeThreatActorConfig", postArr).done(function( data, status ) {
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
    }
  }

  $(document).on('click', '#newThreatActor', function(event) {
    $('#newThreatActorModal').modal('show');
    $('#newThreatActorModal input').val('');
  });

  $(document).on('click', '#newThreatActorSubmit', function(event) {
    var postArr = {}
    postArr.name = encodeURIComponent($('#newThreatActorName').val())
    postArr.IMG = encodeURIComponent($('#newThreatActorIMG').val())
    postArr.URLStub = encodeURIComponent($('#newThreatActorURLStub').val())
    $.post( "/api?function=newThreatActorConfig", postArr).done(function( data, status ) {
      if (data['Status'] == 'Success') {
        toast(data['Status'],"",data['Message'],"success");
        $('#threatActorTable').bootstrapTable('refresh');
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
    var postArr = {}
    postArr.name = encodeURIComponent($('#threatActorName').val())
    postArr.IMG = encodeURIComponent($('#threatActorIMG').val())
    postArr.URLStub = encodeURIComponent($('#threatActorURLStub').val())
    $.post( "/api?function=setThreatActorConfig", postArr).done(function( data, status ) {
      if (data['Status'] == 'Success') {
        toast(data['Status'],"",data['Message'],"success");
        $('#threatActorTable').bootstrapTable('refresh');
      } else if (data['Status'] == 'Error') {
        toast(data['Status'],"",data['Message'],"danger","30000");
      } else {
        toast("Error","","Failed to update Threat Actor: "+postArr.name,"danger","30000");
      }
    }).fail(function( data, status ) {
        toast("API Error","","Failed to update Threat Actor: "+postArr.name,"danger","30000");
    }).always(function( data, status) {
      $('#editModal').modal('hide');
    })
  });

</script>
