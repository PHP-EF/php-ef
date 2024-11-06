<?php
  require_once(__DIR__.'/../../inc/inc.php');
  if (CheckAccess(null,"ADMIN-RBAC") == false) {
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
        <!-- <h2 class="h3 mb-4 page-title">Settings</h2> -->
        <div class="my-4">
<!--            <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Role Based Access</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Configuration</a>
                </li>
            </ul>-->
            <h5 class="mb-0 mt-5">Role Based Access</h5>
            <p>Use the following to configure Role Based Access for Access Groups. This allows providing granular control over which areas of the Infoblox SA Tools Portal users have access to.</p>
            <table  data-url="/api?function=GetRBAC&action=listgroups"
              data-toggle="table"
              data-search="true"
              data-filter-control="true" 
              data-show-refresh="true"
              data-pagination="true"
	      data-toolbar="#toolbar"
	      data-sort-name="Group"
	      data-sort-order="asc"
              data-page-size="25"
              class="table table-striped" id="rbacTable">

              <thead>
                <tr>
                  <th data-field="state" data-checkbox="true"></th>
		  <th data-field="Group" data-sortable="true">Group Name</th>
                  <th data-field="Description" data-sortable="true">Group Description</th>
                  <th data-formatter="actionFormatter" data-events="actionEvents">Actions</th>
                </tr>
              </thead>
              <tbody id="rbacgroups">
	      </tbody>
            <div class="text-left">
              <button class="btn btn-success" id="newgroup">New Group</button>
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
        <h4>Group Information</h4>
	<div class="form-group">
          <label for="editGroupDescription">Group Description</label>
          <div class="input-group mb-1">
            <input type="text" class="form-control" id="editGroupDescription" aria-describedby="editGroupDescriptionHelp">
            <div class="input-group-append">
	      <span class="input-group-text">
                <button class="btn btn-primary" id="groupDescriptionSaveButton">Save</button>
	      </span>
            </div>
	  </div>
          <small id="editGroupDescriptionHelp" class="form-text text-muted">The group description.</small>
	</div>
	<hr>
        <h4>Group Roles</h4>
        <p>Enable or Disable the following roles to provide granular control to specific areas of the Infoblox SA Tools Portal.</p>
	<div class="list-group mb-5 shadow" id="modalListGroup"></div> 
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- New Group Modal -->
<div class="modal fade" id="newGroupModal" tabindex="-1" role="dialog" aria-labelledby="newGroupModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newGroupModalLabel">New Access Group Wizard</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"></span>
        </button>
      </div>
      <div class="modal-body" id="newGroupModelBody">
	      <p>Enter the Access Group Name below to add it to the Role Based Access List.</p>
	      <p>You will need to edit it once created to apply the necessary permissions.</p>
        <form>
          <div class="form-group">
            <label for="groupName">Group Name</label>
            <input type="text" class="form-control" id="groupName" aria-describedby="groupNameHelp">
            <small id="groupNameHelp" class="form-text text-muted">The name of the Access Group to add to the Role Based Access Control.</small>
      	  </div>
          <div class="form-group">
            <label for="groupDescription">Group Description</label>
            <input type="text" class="form-control" id="groupDescription" aria-describedby="groupDescriptionHelp">
            <small id="groupDescriptionHelp" class="form-text text-muted">The description for the new group.</small>
          </div>
          <button class="btn btn-primary" id="newGroupSubmit">Submit</button>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>



<script>
  var $rbacGroupsTable = $('#rbacGroupsTable')

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

  function roleList(row) {
    var div = document.getElementById('modalListGroup');
    var title = document.getElementById('editModalLabel');
    $('#editGroupDescription').val(row['Description']);
    $.getJSON('/api?function=GetRBAC&action=listroles', function(roleinfo) {
      div.innerHTML = "";
      for (var key in roleinfo['Resources']) {
        div.innerHTML += `
          <div class="list-group-item">
            <div class="row align-items-center">
              <div class="col">
                <strong class="mb-2">${key}</strong>
                <p class="text-muted mb-0">${roleinfo['Resources'][key]['description']}</p>
              </div>
              <div class="col-auto">
                <div class="custom-control custom-switch">
                  <input type="checkbox" class="custom-control-input toggle" id="${key}">
                  <label class="custom-control-label" for="${key}"></label>
              </div>
	      </div>
            </div>
          </div>`
      };
      $.getJSON('/api?function=GetRBAC&group='+encodeURIComponent(row.Group), function(grouproleinfo) {
        $('#editModalLabel').text(row.Group);
        for (var key in grouproleinfo.PermittedResources) {
          $("#"+grouproleinfo.PermittedResources[key]).prop("checked", "true");
        }
      });
    });
  }

  function roleQuery(data) {
    $.getJSON('/api?function=GetRBAC&group='+encodeURIComponent(data.Group), function(grouproleinfo) {
      for (var key in grouproleinfo.PermittedResources) {
        $("#"+grouproleinfo.PermittedResources[key]).prop("checked", "true");
      }
    });
  }

  window.actionEvents = {
    'click .edit': function (e, value, row, index) { 
      roleList(row);
      $('#editModal').modal('show');
    },
    'click .delete': function (e, value, row, index) {
      if(confirm("Are you sure you want to delete "+row.Group+" from Role Based Access? This is irriversible.") == true) {
        $.getJSON('/api?function=DeleteRBAC&group='+encodeURIComponent(row.Group), function(removeRBACResults) {
          if (removeRBACResults[row.Group]) {
            toast("Error","","Failed to delete "+row.Group+" from Role Based Access","danger");
	  } else {
            toast("Success","","Successfully deleted "+row.Group+" from Role Based Access","success");
            $('#rbacTable').bootstrapTable('refresh');
	  }
        });
      }
    }
  }

  $(document).on('click', '.toggle', function(event) {
    let toggle = $('#'+event.target.id).prop('checked');
    let group = $('#editModalLabel').text();
    let targetid = event.target.id
    $.getJSON('/api?function=SetRBAC&group='+encodeURIComponent(group)+'&key='+targetid+'&value='+toggle, function(setRBACResults) {
      if (setRBACResults[group]['PermittedResources'].includes(targetid)) {
        if (toggle) {
          toast("Success","","Successfully added "+targetid+" to "+group,"success");
	} else {
          toast("Error","","Failed to add "+targetid+" to "+group,"danger");
	}
      } else {
        if (toggle) {
          toast("Error","","Failed to remove "+targetid+" from "+group,"danger");
	} else {
          toast("Success","","Successfully removed "+targetid+" from "+group,"success");
	}
      }
     }); 
  });

  $('#groupDescriptionSaveButton').on('click', function(elem) {
    let group = $('#editModalLabel').text();
    let description = $('#editGroupDescription').val();
    $.getJSON('/api?function=SetRBAC&group='+encodeURIComponent(group)+'&description='+encodeURIComponent(description),function(setRBACResults) {
      if (setRBACResults[group]['Description'] == description) {
        toast("Success","","Successfully edited "+group+" description to: "+description,"success");
	$('#rbacTable').bootstrapTable('refresh');
	$('#editModal').modal('hide');
      } else {
        toast("Error","","Failed to edit "+group+" description","danger");
      }
     });
  });

  $(document).on('click', '#newgroup', function(event) {
    $('#newGroupModal').modal('show');
  });

  $(document).on('click', '#newGroupSubmit', function(event) {
    let groupName = $('#groupName').val();
    let groupDescription = $('#groupDescription').val();
    $.getJSON('/api?function=SetRBAC&group='+encodeURIComponent(groupName)+'&description='+encodeURIComponent(groupDescription), function(newRBACGroupResults) {
    }); 
  });


</script>
