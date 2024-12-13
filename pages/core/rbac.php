<?php
  require_once(__DIR__.'/../../inc/inc.php');
  if ($ib->rbac->checkAccess("ADMIN-RBAC") == false) {
    die();
  }

?>
<style>
.card {
  padding: 10px;
}
</style>

<div class="section">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <center>
            <h4>Role Based Access</h4>
            <p>Use the following to configure Role Based Access. This allows providing granular control over which areas of the Infoblox SA Tools Portal users have access to.</p>
          </center>
        </div>
      </div>
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-title ms-3 mt-2">
          <h5>Groups</h5>
          <p>Use the following to configure Access Groups. These are mapped to users and used for grouping together one or more roles.</p>
        </div>
        <div class="container">
          <table data-url="/api/v2/rbac/groups"
            data-data-field="data"
            data-toggle="table"
            data-search="true"
            data-filter-control="true"
            data-show-refresh="true"
            data-pagination="true"
            data-toolbar="#toolbar"
            data-sort-name="Name"
            data-sort-order="asc"
            data-page-size="25"
            data-buttons="rbacGroupsButtons"
            data-buttons-order="btnAddGroup,refresh"
            class="table table-striped" id="rbacGroupsTable">

            <thead>
              <tr>
                <th data-field="state" data-checkbox="true"></th>
                <th data-field="Name" data-sortable="true">Group Name</th>
                <th data-field="Description" data-sortable="true">Group Description</th>
                <th data-formatter="actionFormatter" data-events="groupsActionEvents">Actions</th>
              </tr>
            </thead>
            <tbody id="rbacgroups"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-title ms-3 mt-2">
          <h5>Roles</h5>
          <p>Use the following to configure Roles. These are mapped to Groups and provide access to specific website resources.</p>
        </div>
        <div class="container">
          <table  data-url="/api?f=GetRBACRoles"
            data-toggle="table"
            data-search="true"
            data-filter-control="true"
            data-show-refresh="true"
            data-pagination="true"
            data-toolbar="#toolbar"
            data-sort-name="name"
            data-sort-order="asc"
            data-page-size="25"
            data-buttons="rbacRolesButtons"
            data-buttons-order="btnAddGroup,refresh"
            class="table table-striped" id="rbacRolesTable">

            <thead>
              <tr>
                <th data-field="state" data-checkbox="true"></th>
                <th data-field="name" data-sortable="true">Role Name</th>
                <th data-field="description" data-sortable="true">Role Description</th>
                <th data-formatter="actionFormatter" data-events="rolesActionEvents">Actions</th>
              </tr>
            </thead>
            <tbody id="rbacroles"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Edit Group Modal -->
<div class="modal fade" id="groupEditModal" tabindex="-1" role="dialog" aria-labelledby="groupEditModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="groupEditModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"></span>
        </button>
      </div>
      <div class="modal-body" id="editModelBody">
        <h4>Group Information</h4>
	      <div class="form-group">
          <input type="text" class="form-control" id="editGroupID" aria-describedby="editGroupIDHelp" hidden>
          <div class="input-group mb-1">
            <input type="text" class="form-control" id="editGroupDescription" aria-describedby="editGroupDescriptionHelp">
            <div class="input-group-append">
	            <span class="input-group-text">
                <button class="btn btn-primary" id="editGroupDescriptionSaveButton">Save</button>
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

<!-- Edit Role Modal -->
<div class="modal fade" id="roleEditModal" tabindex="-1" role="dialog" aria-labelledby="roleEditModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="roleEditModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"></span>
        </button>
      </div>
      <div class="modal-body" id="editModelBody">
        <h4>Role Information</h4>
        <form>
          <div class="form-group" hidden>
            <input type="text" class="form-control" id="editRoleId">
      	  </div>
          <div class="form-group">
            <label id="editRoleNameLabel" for="editRoleName">Role Name</label>
            <input type="text" class="form-control" id="editRoleName" aria-describedby="editRoleNameHelp">
            <small id="editRoleNameHelp" class="form-text text-muted">The name of the role.</small>
      	  </div>
          <div class="form-group">
            <label id="editRoleDescriptionLabel" for="editRoleDescription">Role Description</label>
            <input type="text" class="form-control" id="editRoleDescription" aria-describedby="editRoleDescriptionHelp">
            <small id="editRoleDescriptionHelp" class="form-text text-muted">The role description.</small>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" id="editRoleSaveButton">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- New Item Modal -->
<div class="modal fade" id="newItemModal" tabindex="-1" role="dialog" aria-labelledby="newItemModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newItemModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"></span>
        </button>
      </div>
      <div class="modal-body" id="newItemModalBody">
        <div id="modal-body-heading"></div>
        <form>
          <div class="form-group">
            <label id="newItemNameLabel" for="newItemName"></label>
            <input type="text" class="form-control" id="newItemName" aria-describedby="newItemNameHelp">
            <small id="newItemNameHelp" class="form-text text-muted"></small>
      	  </div>
          <div class="form-group">
            <label id="newItemDescriptionLabel" for="newItemDescription"></label>
            <input type="text" class="form-control" id="newItemDescription" aria-describedby="newItemDescriptionHelp">
            <small id="newItemDescriptionHelp" class="form-text text-muted"></small>
          </div>
          <button id="newItemSubmit" class="btn btn-primary preventDefault" onclick="">Submit</button>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>



<script>
  function actionFormatter(value, row, index) {
    var actions = '<a class="edit" title="Edit"><i class="fa fa-pencil"></i></a>&nbsp;'
    if (!row['Protected']) {
      actions += '<a class="delete" title="Delete"><i class="fa fa-trash"></i></a>'
    }
    return actions
  }

  function rbacGroupsButtons() {
    return {
      btnAddGroup: {
        text: "Add Group",
        icon: "bi-plus-lg",
        event: function() {
          $('#newItemModal').modal('show');
          $('#newItemModal input').val('');
          $('#newItemModalLabel').text('New Access Group Wizard');
          $('#modal-body-heading').html('<p>Enter the Access Group Name below to add it to the Role Based Access List.</p><p>You will need to edit it once created to apply the necessary permissions.</p>');
          $('#newItemNameLabel').text('Group Name');
          $('#newItemDescriptionLabel').text('Group Description');
          $('#newItemNameHelp').text('The name of the Access Group to add to the Role Based Access Control.');
          $('#newItemDescriptionHelp').text('The description for the new group.');
          $('#newItemSubmit').attr('onclick','newGroup()')
        },
        attributes: {
          title: "Add a new group",
          style: "background-color:#4bbe40;border-color:#4bbe40;"
        }
	    }
    }
  }

  function rbacRolesButtons() {
    return {
      btnAddGroup: {
        text: "Add Role",
        icon: "bi-plus-lg",
        event: function() {
          $('#newItemModal').modal('show');
          $('#newItemModal input').val('');
          $('#newItemModalLabel').text('New Role Wizard');
          $('#modal-body-heading').html('<p>Enter the Role Name below to add it to the Role list.</p>');
          $('#newItemNameLabel').text('Role Name');
          $('#newItemDescriptionLabel').text('Role Description');
          $('#newItemNameHelp').text('The name of the Role to add to the Role list.');
          $('#newItemDescriptionHelp').text('The description for the new role.');
          $('#newItemSubmit').attr('onclick','newRole()')
        },
        attributes: {
          title: "Add a new role",
          style: "background-color:#4bbe40;border-color:#4bbe40;"
        }
	    }
    }
  }

  function editRole(row) {
    $('#editRoleId').val('').val(row.id);
    $('#editRoleName').val('').val(row.name);
    $('#editRoleDescription').val('').val(row.description);
    $('#groupEditModalLabel').val('').text(row.name);
  }

  function editGroup(row) {
    $('#editGroupID').val(row.id);
    var div = document.getElementById('modalListGroup');
    $('#editGroupDescription').val(row['Description']);
    $.getJSON('/api?f=GetRBACRoles', function(roleinfo) {
      div.innerHTML = "";
      for (var role in roleinfo) {
        div.innerHTML += `
          <div class="list-group-item">
            <div class="row align-items-center">
              <div class="col">
                <strong class="mb-2">${roleinfo[role]['name']}</strong>
                <p class="text-muted mb-0">${roleinfo[role]['description']}</p>
              </div>
              <div class="col-auto">
                <div class="custom-control custom-switch">
                  <input type="checkbox" class="custom-control-input toggle" id="${roleinfo[role]['name']}">
                  <label class="custom-control-label" for="${roleinfo[role]['name']}"></label>
                </div>
	            </div>
            </div>
          </div>`
      };
      $('#groupEditModalLabel').text(row.Name);
      if (row.PermittedResources) {
        var PermittedResources = row.PermittedResources.split(',');
        for (var resource in PermittedResources) {
          $("#"+PermittedResources[resource]).prop("checked", "true");
        }
      }
      $('.toggle').on('click', function(event) {
        let id = $('#editGroupID').val();
        let toggle = $('#'+event.target.id).prop('checked') ? 'enabled' : 'disabled';
        let group = $('#groupEditModalLabel').text();
        let targetid = event.target.id
        $.ajax({
          url: '/api/v2/rbac/group/'+id,
          type: 'PUT',
          data: JSON.stringify({
            key: targetid,
            value: toggle
          }),
          contentType: 'application/json',
          success: function(setRBACResults) {
            if (setRBACResults['response']['result'] == 'Success') {
              if (toggle == 'enabled') {
                toast("Success", "", "Successfully added " + targetid + " to " + group, "success");
              } else if (toggle == 'disabled') {
                toast("Success", "", "Successfully removed " + targetid + " to " + group, "success");
              }
              $('#rbacGroupsTable').bootstrapTable('refresh');
            } else if (setRBACResults['response']['result'] == 'Error') {
              toast(setRBACResults['response']['result'], "", setRBACResults['response']['message'], "danger", "30000");
            } else {
              if (toggle == 'enabled') {
                toast("Error", "", "Failed to add " + targetid + " to " + group, "danger");
              } else if (toggle == 'disabled') {
                toast("Error", "", "Failed to remove " + targetid + " from " + group, "danger");
              }
            }
          },
          error: function() {
            toast("Error", "", "Failed to remove " + targetid + " from " + group, "danger");
          }
        });
      });
    });
  }

  window.groupsActionEvents = {
    'click .edit': function (e, value, row, index) {
      editGroup(row);
      $('#groupEditModal').modal('show');
    },
    'click .delete': function (e, value, row, index) {
      if(confirm("Are you sure you want to delete "+row.Name+" from Role Based Access? This is irriversible.") == true) {
        $.getJSON('/api?f=DeleteRBACGroup&id='+encodeURIComponent(row.id), function(removeRBACResults) {
          if (removeRBACResults['Status'] == 'Success') {
            toast("Success","","Successfully deleted "+row.Name+" from Role Based Access","success");
            $('#rbacGroupsTable').bootstrapTable('refresh');
          } else if (removeRBACResults['Status'] == 'Error') {
            toast(removeRBACResults['Status'],"",removeRBACResults['Message'],"danger","30000");
          } else {
            toast("Error","","Failed to delete "+row.Name+" from Role Based Access","danger");
          }
        });
      }
    }
  }

  window.rolesActionEvents = {
    'click .edit': function (e, value, row, index) {
      editRole(row);
      $('#roleEditModal').modal('show');
    },
    'click .delete': function (e, value, row, index) {
      if(confirm("Are you sure you want to delete the "+row.name+" role? This is irriversible.") == true) {
        $.getJSON('/api?f=DeleteRBACRole&id='+encodeURIComponent(row.id), function(removeRBACRoleResults) {
          if (removeRBACRoleResults['Status'] == 'Success') {
            toast("Success","","Successfully deleted role: "+row.name,"success");
            $('#rbacRolesTable').bootstrapTable('refresh');
          } else if (removeRBACRoleResults['Status'] == 'Error') {
            toast(removeRBACRoleResults['Status'],"",removeRBACRoleResults['Message'],"danger","30000");
          } else {
            toast("Error","","Failed to delete "+row.Name+" from Role Based Access","danger");
          }
        });
      }
    }
  }

  $('#editRoleSaveButton').on('click', function(elem) {
    let id = $('#editRoleId').val();
    let name = $('#editRoleName').val();
    let description = $('#editRoleDescription').val();
    $.getJSON('/api?f=SetRBACRole&id='+encodeURIComponent(id)+'&name='+encodeURIComponent(name)+'&description='+encodeURIComponent(description),function(setRBACRoleResults) {
      if (setRBACRoleResults['Status'] == 'Success') {
        toast("Success","","Successfully edited "+name+" description to: "+description,"success");
        $('#rbacRolesTable').bootstrapTable('refresh');
        $('#roleEditModal').modal('hide');
      } else if (setRBACRoleResults['Status'] == 'Error') {
        toast(setRBACRoleResults['Status'],"",setRBACRoleResults['Message'],"danger","30000");
      } else {
        toast("Error","","Failed to edit "+name,"danger");
      }
     });
  });

  $('#editGroupDescriptionSaveButton').on('click', function(elem) {
    let id = $('#editGroupID').val();
    let group = $('#groupEditModalLabel').text();
    let description = $('#editGroupDescription').val();
    $.ajax({
      url: '/api/v2/rbac/group/'+id,
      type: 'PUT',
      data: JSON.stringify({
        description: description
      }),
      contentType: 'application/json',
      success: function(setRBACResults) {
        if (setRBACResults['response']['result'] == 'Success') {
          toast("Success", "", "Successfully edited " + group + " description to: " + description, "success");
          $('#rbacGroupsTable').bootstrapTable('refresh');
          $('#groupEditModal').modal('hide');
        } else if (setRBACResults['response']['result'] == 'Error') {
          toast(setRBACResults['response']['result'], "", setRBACResults['response']['message'], "danger", "30000");
        } else {
          toast("Error", "", "Failed to edit " + group + " description", "danger");
        }
      }
    });
  });

  function newGroup() {
    let groupName = $('#newItemName').val();
    let groupDescription = $('#newItemDescription').val();
    $.getJSON('/api?f=NewRBACGroup&name='+encodeURIComponent(groupName)+'&description='+encodeURIComponent(groupDescription)).done(function( data, status ) {
      if (data['Status'] == 'Success') {
        toast("Success","","Successfully created group: "+groupName,"success");
        $('#rbacGroupsTable').bootstrapTable('refresh');
        $('#newItemModal').modal('hide');
      } else if (data['Status'] == 'Error') {
        toast(data['Status'],"",data['Message'],"danger","30000");
      } else {
        toast("Error","","Failed to add new group","danger","30000");
      }
    }).fail(function( data, status ) {
        toast("API Error","","Failed to add new group","danger","30000");
    });
  };

  function newRole() {
    let roleName = $('#newItemName').val();
    let roleDescription = $('#newItemDescription').val();
    $.getJSON('/api?f=NewRBACRole&name='+encodeURIComponent(roleName)+'&description='+encodeURIComponent(roleDescription)).done(function( data, status ) {
      if (data['Status'] == 'Success') {
        toast("Success","","Successfully created role: "+roleName,"success");
        $('#rbacRolesTable').bootstrapTable('refresh');
        $('#newItemModal').modal('hide');
      } else if (data['Status'] == 'Error') {
        toast(data['Status'],"",data['Message'],"danger","30000");
      } else {
        toast("Error","","Failed to add new role","danger","30000");
      }
    }).fail(function( data, status ) {
        toast("API Error","","Failed to add new role","danger","30000");
    });
  };

  $('.preventDefault').click(function(event){
    event.preventDefault();
    console.log(event);
  });
</script>
