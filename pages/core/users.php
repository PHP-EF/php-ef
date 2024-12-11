<?php
  require_once(__DIR__.'/../../inc/inc.php');
  if ($ib->rbac->checkAccess("ADMIN-USERS") == false) {
    die();
  }
?>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-12 mx-auto">
      <div class="my-4">
        <h5 class="mb-0 mt-5">User/Group Configuration</h5>
        <p>Use the following to configure Users & Groups within the Infoblox SA Tools Portal.</p>
        <table id="userTable" class="table table-striped"></table>
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
          <input type="text" class="form-control" id="editUserName" aria-describedby="editUserNameHelp">
          <small id="editUserNameHelp" class="form-text text-muted">The Username for the account.</small>
        </div>
        <div class="form-group">
          <label for="editUserFirstname">First Name</label>
          <input type="text" class="form-control" id="editUserFirstname" aria-describedby="editUserFirstnameHelp">
          <small id="editUserFirstnameHelp" class="form-text text-muted">Enter the updated users first name.</small>
        </div>
        <div class="form-group">
          <label for="editUserSurname">Surname</label>
          <input type="text" class="form-control" id="editUserSurname" aria-describedby="editUserSurnameHelp">
          <small id="editUserSurnameHelp" class="form-text text-muted">Enter the updated users surname.</small>
        </div>
        <div class="form-group">
          <label for="editUserEmail">Email</label>
          <input type="text" class="form-control" id="editUserEmail" aria-describedby="editUserEmailHelp">
          <small id="editUserEmailHelp" class="form-text text-muted">Enter the updated users email address.</small>
        </div>
        <div class="form-group">
          <label for="editUserType">Account Type</label>
          <input type="text" class="form-control" id="editUserType" aria-describedby="editUserTypeHelp" disabled>
          <small id="editUserTypeHelp" class="form-text text-muted">The type of account (Local or SSO).</small>
        </div>
        <div class="form-group">
          <label for="editCreated">Created Date</label>
          <input type="text" class="form-control" id="editCreated" aria-describedby="editCreatedHelp" disabled>
          <small id="editCreatedHelp" class="form-text text-muted">The date when this account was created.</small>
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
        <hr>
        <div class="accordion" id="resetPasswordAccordion">
          <div class="accordion-item">
            <h2 class="accordion-header" id="resetPasswordHeading">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#resetPassword" aria-expanded="true" aria-controls="resetPassword">
              Reset Password
              </button>
            </h2>
            <div id="resetPassword" class="accordion-collapse collapse" aria-labelledby="resetPasswordHeading" data-bs-parent="#resetPasswordAccordion">
              <div class="accordion-body">
                <div class="form-group">
                  <label for="editUserPassword">Password</label>
                  <i class="fa fa-info-circle hover-target" aria-hidden="true"></i>
                  <input type="password" class="form-control" id="editUserPassword" aria-describedby="editUserPasswordHelp">
                  <small id="editUserPasswordHelp" class="form-text text-muted">The updated password for the user.</small>
                </div>
                <div class="form-group">
                  <label for="editUserPassword2">Verify Password</label>
                  <input type="password" class="form-control" id="editUserPassword2" aria-describedby="editUserPassword2Help">
                  <small id="editUserPassword2Help" class="form-text text-muted">Enter the updated password again.</small>
                </div>
              </div>
            </div>
          </div>
        </div>
        <hr>
        <div id="popover" class="popover" role="alert">
          <h4 class="alert-heading">Password Complexity</h4>
          <p>Minimum of 8 characters</p>
          <p>At least one uppercase letter</p>
          <p>At least one lowercase letter</p>
          <p>At least one number</p>
          <p>At least one special character</p>
        </div>
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
          <label for="newUserFirstname">First Name</label>
          <input type="text" class="form-control" id="newUserFirstname" aria-describedby="newUserFirstnameHelp">
          <small id="newUserFirstnameHelp" class="form-text text-muted">Enter the new users first name.</small>
        </div>
        <div class="form-group">
          <label for="newUserSurname">Surname</label>
          <input type="text" class="form-control" id="newUserSurname" aria-describedby="newUserSurnameHelp">
          <small id="newUserSurnameHelp" class="form-text text-muted">Enter the new users surname.</small>
        </div>
        <div class="form-group">
          <label for="newUserEmail">Email</label>
          <input type="text" class="form-control" id="newUserEmail" aria-describedby="newUserEmailHelp">
          <small id="newUserEmailHelp" class="form-text text-muted">Enter the new users email address.</small>
        </div>
        <div class="form-group">
          <label for="newUserPassword">Password</label>&nbsp;
          <i class="fa fa-info-circle hover-target" aria-hidden="true"></i>
          <input type="password" class="form-control" id="newUserPassword" aria-describedby="newUserPasswordHelp">
          <small id="newUserPasswordHelp" class="form-text text-muted">The password for the new user.</small>
        </div>
        <div class="form-group">
          <label for="newUserPassword2">Verify Password</label>
          <input type="password" class="form-control" id="newUserPassword2" aria-describedby="newUserPassword2Help">
          <small id="newUserPassword2Help" class="form-text text-muted">Enter the password again.</small>
        </div>
        <br>
        <div class="form-check form-switch">
          <input class="form-check-input info-field" type="checkbox" id="expire" name="expire">
          <label class="form-check-label" for="expire">Require Password Reset At First Login</label>
        </div>
        <hr>
        <div id="popover" class="popover" role="alert">
          <h4 class="alert-heading">Password Complexity</h4>
          <p>Minimum of 8 characters</p>
          <p>At least one uppercase letter</p>
          <p>At least one lowercase letter</p>
          <p>At least one number</p>
          <p>At least one special character</p>
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
    var html = ''
    $(row.groups).each(function (group) {
      html += '<span class="badge bg-info">'+row.groups[group]+'</span>&nbsp;';
    });
    return html;
  }

  function listUserConfig(row) {
    $('#editUserID').val(row['id']);
    $('#editUserName').val(row['username']);
    $('#editUserFirstname').val(row['firstname']);
    $('#editUserSurname').val(row['surname']);
    $('#editUserEmail').val(row['email']);
    if (row['type'] == 'SSO') {
      $('#editUserPassword').attr('disabled',true);
      $('#editUserPassword2').attr('disabled',true);
    } else {
      $('#editUserPassword').attr('disabled',false);
      $('#editUserPassword2').attr('disabled',false);
    }
    $('#editUserType').val(row['type']);
    $('#editLastLogin').val(row['lastlogin']);
    $('#editPasswordExpires').val(row['passwordexpires']);
    $('#editCreated').val(row['created']);
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
        $.post( "/api?f=removeUser", postArr).done(function( data, status ) {
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
    $.getJSON('/api?f=GetRBACGroups&type=configurable', function(groupinfo) {
      div.innerHTML = "";
      for (var key in groupinfo) {
        div.innerHTML += `
          <div class="list-group-item">
            <div class="row align-items-center">
              <div class="col">
                <strong class="mb-2">${groupinfo[key]['Name']}</strong>
                <p class="text-muted mb-0">${groupinfo[key]['Description']}</p>
              </div>
              <div class="col-auto">
                <div class="custom-control custom-switch">
                  <input type="checkbox" class="custom-control-input toggle" id="${groupinfo[key]['Name'].replaceAll(" ", "--")}">
                  <label class="custom-control-label" for="${groupinfo[key]['Name'].replaceAll(" ", "--")}"></label>
                </div>
	            </div>
            </div>
          </div>`
      };
      var groupsplit = row.groups;
      if (groupsplit[0] != "") {
        for (var group in groupsplit) {
          $("#"+groupsplit[group].replaceAll(" ", "--")).prop("checked", "true");
        }
      }
    });
  }

  $(document).on('click', '.toggle', function(event) {
    let toggle = $('#'+event.target.id).prop('checked');
    let groups = $('#editModal .toggle:checked').map(function() {
      return this.id.replaceAll("--"," ");
    }).get().join(',');
    var postArr = {}
    postArr.id = $('#editUserID').val();
    postArr.groups = groups;
    $.post( "/api?f=setUser", postArr).done(function( data, status ) {
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
    var firstname = $('#newUserFirstname').val().trim();
    var surname = $('#newUserSurname').val().trim();
    var email = $('#newUserEmail').val().trim();
    var expire = $('#expire')[0].checked;

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
      postArr.un = username;
      postArr.pw = password;
      postArr.fn = firstname;
      postArr.sn = surname;
      postArr.em = email;
      postArr.expire = expire;
      $.post( "/api?f=newUser", postArr).done(function( data, status ) {
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
    postArr.id = $('#editUserID').val().trim();
    postArr.un = $('#editUserName').val().trim();
    postArr.pw = $('#editUserPassword').val().trim();
    postArr.fn = $('#editUserFirstname').val().trim();
    postArr.sn = $('#editUserSurname').val().trim();
    postArr.em = $('#editUserEmail').val().trim();
    $.post( "/api?f=setUser", postArr).done(function( data, status ) {
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
	    }
    }
  }

  function populateUsers() {
    $.getJSON('/api?f=getUsers', function(data) {
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
            field: 'firstname',
            title: 'First Name',
            filterControl: 'input',
            sortable: true
          },{
            field: 'surname',
            title: 'Surname',
            filterControl: 'input',
            sortable: true
          },{
            field: 'email',
            title: 'Email',
            filterControl: 'input',
            sortable: true
          },{
            field: 'groups',
            title: 'Group(s)',
            filterControl: 'input',
            sortable: true,
            formatter: 'groupsFormatter'
          },{
            field: 'type',
            title: 'Type',
            filterControl: 'input',
            sortable: true
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
            visible: false,
            formatter: 'datetimeFormatter'
          },{
            field: 'passwordexpires',
            title: 'Password Expiry Date',
            filterControl: 'input',
            sortable: false,
            visible: false,
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
    $('.hover-target').hover(
        function() {
            $('.popover').css({
                display: 'block',
            });
        },
        function() {
            $('.popover').hide();
        }
    );
    $.getJSON('/api?f=GetRBACGroups&type=configurable', function(groupres) {
      groupinfo = groupres;
      populateUsers();
    });
    $('#newUserPassword, #newUserPassword2').on('change', function() {
      var password = $('#newUserPassword').val();
      var confirmPassword = $('#newUserPassword2').val();

      if (password !== confirmPassword) {
        if (password !== "" && confirmPassword !== "") {
          toast("Warning","","The entered passwords do not match","danger","3000");
          $('#newUserSubmit').attr('disabled',true);
          $('#newUserPassword').css('color','red').css('border-color','red');
          $('#newUserPassword2').css('color','red').css('border-color','red');
        }
      } else {
        $('#newUserSubmit').attr('disabled',false);
        $('#newUserPassword').css('color','green').css('border-color','green');
        $('#newUserPassword2').css('color','green').css('border-color','green');
      }
    });
    $('#editUserPassword, #editUserPassword2').on('change', function() {
      var password = $('#editUserPassword').val();
      var confirmPassword = $('#editUserPassword2').val();

      if (password !== confirmPassword) {
        if (password !== "" && confirmPassword !== "") {
          toast("Warning","","The entered passwords do not match","danger","3000");
          $('#editUserSubmit').attr('disabled',true);
          $('#editUserPassword').css('color','red').css('border-color','red');
          $('#editUserPassword2').css('color','red').css('border-color','red');
        }
      } else {
        $('#newUserSubmit').attr('disabled',false);
        $('#editUserPassword').css('color','green').css('border-color','green');
        $('#editUserPassword2').css('color','green').css('border-color','green');
      }
    });
  });

</script>
