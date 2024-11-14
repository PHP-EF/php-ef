<?php
  require_once(__DIR__.'/../../inc/inc.php');
  if ($auth->checkAccess(null,"ADMIN-USERS") == false) {
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
        <h5 class="mb-0 mt-5">User/Group Configuration</h5>
        <p>Use the following to configure Users & Groups within the Infoblox SA Tools Portal.</p>
        <table id="userTable"></table>
      </div>
    </div>
  </div>
</div>


<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">User Information</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"></span>
        </button>
      </div>
      <div class="modal-body" id="editModelBody">
        <div class="form-group" hidden>
          <label for="editUserID">ID</label>
          <input type="text" class="form-control" id="editUserID" aria-describedby="editUserIDHelp">
          <small id="editUserIDHelp" class="form-text text-muted">The username for the new user.</small>
        </div>
        <div class="form-group">
          <label for="editUserName">Username</label>
          <input type="text" class="form-control" id="editUserName" aria-describedby="editUserNameHelp" disabled>
          <small id="editUserNameHelp" class="form-text text-muted">The Username for the account.</small>
        </div>
        <div class="form-group">
          <label for="editLastLogin">Last Login Date</label>
          <input type="text" class="form-control" id="editLastLogin" aria-describedby="editLastLoginHelp" disabled>
          <small id="editLastLoginHelp" class="form-text text-muted">The date when this account last logged in.</small>
        </div>
        <div class="form-group">
          <label for="editPasswordExpires">Password Expiry Date</label>
          <input type="text" class="form-control" id="editPasswordExpires" aria-describedby="editPasswordExpiresHelp" disabled>
          <small id="editPasswordExpiresHelp" class="form-text text-muted">The date/time of when the password for this account will expire.</small>
        </div>
        <div class="form-group">
          <label for="editUserPassword">Password</label>
          <input type="password" class="form-control" id="editUserPassword" aria-describedby="editUserPasswordHelp">
          <small id="editUserPasswordHelp" class="form-text text-muted">The updated password for the user.</small>
        </div>
        <div class="form-group">
          <label for="editUserPassword2">Verify Password</label>
          <input type="password" class="form-control" id="editUserPassword2" aria-describedby="editUserPassword2Help">
          <small id="editUserPassword2Help" class="form-text text-muted">Enter the updated password again.</small>
        </div>
	      <hr>
        <h4>Groups</h4>
        <p>Enable or Disable the following groups to provide granular control to specific areas of the Infoblox SA Tools Portal.</p>
	      <div class="list-group mb-5 shadow" id="modalListGroup"></div> 
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-success" id="editUserSubmit">Save</button>
      </div>
    </div>
  </div>
</div>

<!-- New User Modal -->
<div class="modal fade" id="newUserModal" tabindex="-1" role="dialog" aria-labelledby="newUserModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newUserModalLabel">New User Wizard</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"></span>
        </button>
      </div>
      <div class="modal-body" id="newUserModelBody">
	      <p>Enter the information below to create the new user.</p>
        <div class="form-group">
          <label for="newUserName">Username</label>
          <input type="text" class="form-control" id="newUserName" aria-describedby="newUserNameHelp">
          <small id="newUserNameHelp" class="form-text text-muted">The username for the new user.</small>
        </div>
        <div class="form-group">
          <label for="newUserPassword">Password</label>
          <input type="password" class="form-control" id="newUserPassword" aria-describedby="newUserPasswordHelp">
          <small id="newUserPasswordHelp" class="form-text text-muted">The password for the new user.</small>
        </div>
        <div class="form-group">
          <label for="newUserPassword2">Verify Password</label>
          <input type="password" class="form-control" id="newUserPassword2" aria-describedby="newUserPassword2Help">
          <small id="newUserPassword2Help" class="form-text text-muted">Enter the password again.</small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-success" id="newUserSubmit">Save</button>
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

  function groupsFormatter(value, row, index) {
    var html = "";
    if (groupinfo != null) {
      $(row.groups).each(function (group) {
        for (groupi in groupinfo ) {
          if (row.groups[group] === groupinfo[groupi]['id']) {
            html += '<span class="badge bg-info">'+groupinfo[groupi]['Group']+'</span>&nbsp;';
          }
        }
      });
    } else {
      $(row.groups).each(function (group) {
        html += '<span class="badge bg-info">'+row.groups[group]+'</span>&nbsp;';
      });
    }
    return html;
  }

  function listUserConfig(row) {
    $('#editUserID').val(row['id']);
    $('#editUserName').val(row['username']);
    $('#editLastLogin').val(row['lastlogin']);
    $('#editPasswordExpires').val(row['passwordexpires']);
  }

  window.actionEvents = {
    'click .edit': function (e, value, row, index) { 
      listUserConfig(row);
      listGroups(row);
      $('#editModal').modal('show');
    },
    'click .delete': function (e, value, row, index) {
      if(confirm("Are you sure you want to delete "+row.username+" from the list of Users? This is irriversible.") == true) {
        var postArr = {}
        postArr.id = row.id;
        $.post( "/api?function=removeUser", postArr).done(function( data, status ) {
          if (data['Status'] == 'Success') {
            toast(data['Status'],"",data['Message'],"success");
            populateUsers();
          } else if (data['Status'] == 'Error') {
            toast(data['Status'],"",data['Message'],"danger","30000");
          } else {
            toast("Error","","Failed to remove user: "+row.username,"danger","30000");
          }
        }).fail(function( data, status ) {
            toast("API Error","","Failed to remove user: "+row.username,"danger","30000");
        })
      }
    }
  }

  function listGroups(row) {
    var div = document.getElementById('modalListGroup');
    $.getJSON('/api?function=GetRBAC&action=listconfigurablegroups', function(groupinfo) {
      div.innerHTML = "";
      for (var key in groupinfo) {
        div.innerHTML += `
          <div class="list-group-item">
            <div class="row align-items-center">
              <div class="col">
                <strong class="mb-2">${groupinfo[key]['Group']}</strong>
                <p class="text-muted mb-0">${groupinfo[key]['Description']}</p>
              </div>
              <div class="col-auto">
                <div class="custom-control custom-switch">
                  <input type="checkbox" class="custom-control-input toggle" id="${groupinfo[key]['id']}">
                  <label class="custom-control-label" for="${groupinfo[key]['id']}"></label>
                </div>
	            </div>
            </div>
          </div>`
      };
      var groupsplit = row.groups;
      if (groupsplit[0] != "") {
        for (var group in groupsplit) {
          $("#"+groupsplit[group]).prop("checked", "true");
        }
      }
    });
  }

  $(document).on('click', '.toggle', function(event) {
    let toggle = $('#'+event.target.id).prop('checked');
    let groups = $('#editModal .toggle:checked').map(function() {
    return this.id; // or $(this).attr('id');
    }).get().join(',');
    var postArr = {}
    postArr.id = $('#editUserID').val();
    postArr.groups = groups;
    $.post( "/api?function=setUser", postArr).done(function( data, status ) {
      if (data['Status'] == 'Success') {
        toast(data['Status'],"",data['Message'],"success");
        populateUsers();
      } else if (data['Status'] == 'Error') {
        toast(data['Status'],"",data['Message'],"danger","30000");
      } else {
        toast("Error","","Failed to update user groups: "+postArr.un,"danger","30000");
      }
    }).fail(function( data, status ) {
        toast("API Error","","Failed to update groups: "+postArr.un,"danger","30000");
    })
  });

  $(document).on('click', '#newUserSubmit', function(event) {
    // Prevent the default form submission
    event.preventDefault();

    // Get values from the input fields
    var username = $('#newUserName').val().trim();
    var password = $('#newUserPassword').val().trim();
    var confirmPassword = $('#newUserPassword2').val().trim();

    // Initialize a flag for validation
    var isValid = true;

    // Check if all fields are populated
    if (!username || !password || !confirmPassword) {
      toast("Error","","All fields must be filled out","danger","30000");
      isValid = false;
    }

    // Check if passwords match
    if (password !== confirmPassword) {
      toast("Error","","Passwords do not match","danger","30000");
      isValid = false;
    }

    // Display error messages or proceed with form submission
    if (isValid) {
      var postArr = {}
      postArr.un = $('#newUserName').val()
      postArr.pw = $('#newUserPassword').val()
      $.post( "/api?function=newUser", postArr).done(function( data, status ) {
        if (data['Status'] == 'Success') {
          toast(data['Status'],"",data['Message'],"success");
          populateUsers();
          $('#newUserModal').modal('hide');
        } else if (data['Status'] == 'Error') {
          toast(data['Status'],"",data['Message'],"danger","30000");
        } else {
          toast("Error","","Failed to add new user","danger","30000");
        }
      }).fail(function( data, status ) {
          toast("API Error","","Failed to add new user","danger","30000");
      })
    }
  });

  $(document).on('click', '#editUserSubmit', function(event) {
    var postArr = {}
    postArr.id = $('#editUserID').val()
    postArr.un = $('#editUserName').val()
    postArr.pw = $('#editUserPassword').val()
    $.post( "/api?function=setUser", postArr).done(function( data, status ) {
      if (data['Status'] == 'Success') {
        toast(data['Status'],"",data['Message'],"success");
        populateUsers();
      } else if (data['Status'] == 'Error') {
        toast(data['Status'],"",data['Message'],"danger","30000");
      } else {
        toast("Error","","Failed to update user: "+postArr.un,"danger","30000");
      }
    }).fail(function( data, status ) {
        toast("API Error","","Failed to update user: "+postArr.un,"danger","30000");
    }).always(function( data, status) {
      $('#editModal').modal('hide');
    })
  });

  function userButtons() {
    return {
      btnAddUser: {
        text: "Add User",
        icon: "bi-person-fill-add",
        event: function() {
          $('#newUserModal').modal('show');
          $('#newUserModal input').val('');
        },
        attributes: {
          title: "Add a new user to the Infoblox SA Tools Portal",
          style: "background-color:#4bbe40;border-color:#4bbe40;"
        }
	    }//,
      // btnBulkDelete: {
      //   text: "Bulk Delete",
      //   icon: "bi bi-trash",
      //   event: function() {
      //     bulkDelete();
      //   },
      //   attributes: {
      //     title: "Delete one or more users from the Infoblox SA Tools Portal.",
      //     style: "background-color:#ff3c4e;border-color:#ff3c4e;"
      //   }
      // }
    }
  }

  function populateUsers() {
    $.getJSON('/api?function=getUsers', function(data) {
      if (data['Status'] == 'Error') {
        toast(data['Status'],"",data['Error'],"danger","30000");
      } else if (data['error']) {
        toast('Error',"",data['error'][0]['message'],"danger","30000");
      } else {
        $('#userTable').bootstrapTable('destroy');
        $('#userTable').bootstrapTable({
          data: data,
          sortable: true,
          pagination: true,
          search: true,
          showExport: true,
          exportTypes: ['json', 'xml', 'csv', 'txt', 'excel', 'sql'],
          showColumns: true,
          showRefresh: true,
          filterControl: true,
          filterControlVisible: false,
          showFilterControlSwitch: true,
          buttons: 'userButtons',
          buttonsOrder: 'btnAddUser,btnBulkDelete,refresh,columns,export,filterControlSwitch',
          columns: [{
            field: 'state',
            title: 'state',
            checkbox: true
          },{
            field: 'id',
            title: 'ID',
            filterControl: 'input',
            sortable: true
          },{
            field: 'username',
            title: 'Username',
            filterControl: 'input',
            sortable: true
          },{
            field: 'groups',
            title: 'Group(s)',
            filterControl: 'input',
            sortable: true,
            formatter: 'groupsFormatter'
          },{
            field: 'lastlogin',
            title: 'Last Login Date',
            filterControl: 'input',
            sortable: false,
            formatter: 'datetimeFormatter'
          },{
            field: 'created',
            title: 'Creation Date',
            filterControl: 'input',
            sortable: false,
            formatter: 'datetimeFormatter'            
          },{
            field: 'passwordexpires',
            title: 'Password Expiry Date',
            filterControl: 'input',
            sortable: false,
            formatter: 'datetimeFormatter'
          },{
            title: 'Actions',
            formatter: 'actionFormatter',
            events: 'actionEvents',
          }]
        });
        // Enable refresh button
        $('button[name="refresh"]').click(function() {
          populateUsers();
        });
      }
    }).fail(function( data, status ) {
        toast("API Error","","Unknown API Error","danger","30000");
    })
  }

  var groupinfo = '';

  $(document).ready(function() {
    $.getJSON('/api?function=GetRBAC&action=listconfigurablegroups', function(groupres) {
      groupinfo = groupres;
      populateUsers();
    });
    $('#newUserPassword2').on('change', function() {
      var password = $('#newUserPassword').val();
      var confirmPassword = $(this).val();
      
      if (password !== confirmPassword) {
        toast("Warning","","The entered passwords do not match","danger","30000");
        $('#newUserSubmit').attr('disabled',true);
        $(this).css('color','red').css('border-color','red');
      } else {
        $('#newUserSubmit').attr('disabled',false);
        $(this).css('color','green');
      }
    });
    $('#editUserPassword2').on('change', function() {
      var password = $('#editUserPassword').val();
      var confirmPassword = $(this).val();
      
      if (password !== confirmPassword) {
        toast("Warning","","The entered passwords do not match","danger","30000");
        $('#editUserSubmit').attr('disabled',true);
        $(this).css('color','red').css('border-color','red');
      } else {
        $('#editUserSubmit').attr('disabled',false);
        $(this).css('color','green');
      }
    });
  });

</script>
