<?php
  if ($_SERVER['REQUEST_URI'] == '/?') {
    header('Location: /');
  }
  if (isset($_REQUEST['page'])) {
    header('Location: /#'.$_SERVER['QUERY_STRING']);
  }

  require_once(__DIR__.'/inc/inc.php');
  require_once(__DIR__.'/inc/me.php');

  if ($ib->auth->getAuth()['Authenticated'] == true) {
    $isAuth = true;
  } else {
    $isAuth = false;
  }
?>

<!DOCTYPE html>

<html lang="en" dir="ltr">
  <head>
    <meta charset="UTF-8">
    <title> Infoblox SA Tools </title>
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
  </head>
<body>
  <div class="sidebar">
    <div class="logo-details">
      <img class="logo-sm" src="/assets/images/Other/ib-diamonds.png"></img>
      <!-- <span class="logo_name">Infoblox SA Tools</span> -->
      <img class="logo-lg" src="/assets/images/Other/ib-logo-white.png"></img>
    </div>
    <ul class="nav-links">
      <li class="menu-item">
        <a href="#page=default" class="toggleFrame" data-page-name="Home">
          <i class='fa fa-home' ></i>
          <span class="link_name">Home</span>
        </a>
        <ul class="sub-menu blank">
          <li><a class="link_name preventDefault" href="#">Home</a></li>
        </ul>
      </li>
      <?php if ($ib->auth->checkAccess(null,'DNS-TOOLBOX')) { echo '
      <li class="menu-item">
        <div class="icon-link">
          <a href="#page=tools/dnstoolbox" class="toggleFrame" data-page-name="DNS Toolbox">
            <i class="fa fa-toolbox" ></i>
            <span class="link_name">DNS Toolbox</span>
          </a>
          <ul class="sub-menu blank">
            <li><a class="link_name preventDefault" href="#">DNS Toolbox</a></li>
          </ul>
        </div>
      </li>';}
      if ($ib->auth->checkAccess(null,'B1-SECURITY-ASSESSMENT')) { echo '
      <li class="menu-item">
        <div class="icon-link">
          <a href="#page=uddi/security-assessment" class="toggleFrame" data-page-name="Security Assessment Report Generator">
            <i class="fa fa-magnifying-glass-chart" ></i>
            <span class="link_name">Security Assessment</span>
          </a>
          <ul class="sub-menu blank">
            <li><a class="link_name preventDefault" href="#">Security Assessment</a></li>
          </ul>
        </div>
      </li>';}
      if ($ib->auth->checkAccess(null,'B1-THREAT-ACTORS')) { echo '
      <li class="menu-item">
        <div class="icon-link">
          <a href="#page=uddi/threat-actors" class="toggleFrame" data-page-name="Threat Actors">
            <i class="fa fa-skull" ></i>
            <span class="link_name">Threat Actors</span>
          </a>
          <ul class="sub-menu blank">
            <li><a class="link_name preventDefault" href="#">Threat Actors</a></li>
          </ul>
        </div>
      </li>';}
      if ($ib->auth->checkAccess(null,null,"DEV-Menu")) { echo '
      <li class="menu-item">
        <div class="icon-link">
          <a href="#" class="preventDefault">
            <i class="fa fa-toolbox" ></i>
            <span class="link_name">Dev</span>
          </a>
          <i class="bx bxs-chevron-down arrow" ></i>
        </div>
        <ul class="sub-menu">
          <li>
            <a class="link_name preventDefault" href="#">Dev</a>
          </li>';
          if ($ib->auth->checkAccess(null,'B1-LICENSE-USAGE')) { echo '
            <li>
              <a href="#page=uddi/license-usage" class="toggleFrame" data-page-name="License Usage">
                <i class="fas fa-certificate" ></i>
                <span>License Utilization</span>
              </a>
            </li>';}
            echo '
        </ul>
      </li>';}
      if ($ib->auth->checkAccess(null,null,"ADMIN-Menu")) { echo '
      <li class="menu-item">
        <div class="icon-link">
          <a href="#" class="preventDefault">
            <i class="fas fa-user-shield" ></i>
            <span class="link_name">Admin</span>
          </a>
          <i class="bx bxs-chevron-down arrow" ></i>
        </div>
        <ul class="sub-menu">
          <div class="icon-link">
            <a class="link_name preventDefault" href="#">Admin</a>
            <a href="#" class="preventDefault">
              <i class="fas fa-cog" ></i>
              <span>Settings</span>
            </a>
            <i class="bx bxs-chevron-down arrow" ></i>
          </div>
          <li class="sub-menu-item">
            <ul class="sub-sub-menu">
              <li>';
                if ($ib->auth->checkAccess(null,"ADMIN-USERS")) { echo '
                <a href="#page=core/users" class="toggleFrame" data-page-name="Users">Users</a>
                ';}
                if ($ib->auth->checkAccess(null,"ADMIN-CONFIG")) { echo '
                <a href="#page=core/configuration" class="toggleFrame" data-page-name="Configuration">Configuration</a>
                ';}
                if ($ib->auth->checkAccess(null,"ADMIN-RBAC")) { echo '
                <a href="#page=core/rbac" class="toggleFrame" data-page-name="Role Based Access">Role Based Access</a>
                ';}
                if ($ib->auth->checkAccess(null,"ADMIN-SECASS")) { echo '
                <a href="#page=core/security-assessment-configuration" class="toggleFrame" data-page-name="Security Assessment Configuration">Security Assessment</a>
                ';}
                echo '
              </li>
            </ul>
          </li>
        </ul>';
        if ($ib->auth->checkAccess(null,"ADMIN-LOGS")) { echo '
        <ul class="sub-menu">
          <div class="icon-link">
            <a href="#" class="preventDefault">
              <i class="fa-regular fa-file" ></i>
              <span>Logs</span>
            </a>
            <i class="bx bxs-chevron-down arrow" ></i>
          </div>
          <li class="sub-menu-item">
            <ul class="sub-sub-menu">
              <li>';
                if ($ib->auth->checkAccess(null,"ADMIN-USERS")) { echo '
                <a href="#page=core/logs" class="toggleFrame" data-page-name="Logs">Portal Logs</a>
                ';}
                echo '
              </li>
            </ul>
          </li>
        </ul>
      </li>';}}?>
      <li class="menu-item">
        <div class="sidebar-footer">
          <a href="#" class="infoBtn preventDefault">
            <i class="fa fa-info infoBtn"></i>
          </a>
          <a href="#" class="toggleFontSizeBtn preventDefault">
            <i class="fas fa-font fontDropBtn" id="fontSizeBtn"></i>
            <div class="fontDropdown">
              <div id="fontDropdown" class="fontDropdown-content">
                <i onclick='setFontSize("12px")'>12px</i>
                <i onclick='setFontSize("14px")'>13px</i>
                <i onclick='setFontSize("14px")'>14px</i>
                <i onclick='setFontSize("14px")'>15px</i>
                <i onclick='setFontSize("16px")'>16px</i>
                <i onclick='setFontSize("16px")'>17px</i>
                <i onclick='setFontSize("18px")'>18px</i>
              </div>
            </div>
          </a>
          <a href="#" class="toggleThemeBtn preventDefault">
            <i class="fa-solid fa-lightbulb toggler" id="themeToggle"></i>
          </a>
        </div>
      </li>
    </ul>
  </div>

  <section class="home-section">
    <div class="nav-bar">
      <i class='bx bx-menu' ></i>
      <span class="title-text"></span>
      <div class="profile-name-user ms-auto me-3">
        <?php if ($ib->auth->getAuth()['Authenticated']) { echo '
        <div class="dropdown">
          <button class="dropbtn">'; echo $ib->auth->getAuth()['Username']. '
            <i class="bx bxs-chevron-down arrow" ></i>
          </button>
          <div class="dropdown-content">
            <ul>
              <li class="dropdown-header">
                <h6>'; echo $ib->auth->getAuth()['DisplayName'].'</h6>
                <small>'; echo $ib->auth->getAuth()['Email'].'</small>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li>
                <a href="#" class="profile">
                  <span>Profile</span>
                  <i class="fa fa-user"></i>
                </a>
              </li>
              <li>
                <a href="#" class="log-out" onclick="logout();">
                  <span>Log Out</span>
                  <i class="fa fa-sign-out" onclick="logout();"></i>
                </a>
              </li>
            </ul>
          </div>
        </div>';} else {
        echo '
        <div class="dropdown">
          <a href="#" class="login-btn preventDefault" onclick="login();">
            <span>Login</span>
            <i class="fa fa-sign-in" onclick="login();"></i>
          </a>
        </div>
        ';} ?>
      </div>
    </div>
    <main class="page-content" id="page-content">
      <div class="container-fluid">
        <?php
        if (isset($iframe)) {
        echo '<iframe id="mainFrame" name="mainFrame" height="100%" width="100%" frameborder="0" src="'.$iframe.'"></iframe>';
        } else {
        echo '<iframe id="mainFrame" name="mainFrame" height="100%" width="100%" frameborder="0" src="pages/default.php"></iframe>';
        }
        ?>
      </div>
    </main>
  </section>
</body>


<!-- Info Modal -->
<div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="infoModalLabel">General Information</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"></span>
        </button>
      </div>
      <div class="modal-body" id="infoModelBody">
        <div class="lg-12 col-sm-12 pb-6">
          <div class="card h-100">
            <div class="card-block">
              <!--tabs-->
              <ul id="tabsJustified" class="nav nav-tabs info-nav">
                <li class="nav-item">
                  <a href="" data-bs-target="#about" data-bs-toggle="tab" class="nav-link small text-uppercase active">About</a>
                </li>
                <li class="nav-item">
                  <a href="" data-bs-target="#support" data-bs-toggle="tab" class="nav-link small text-uppercase">Support</a>
                </li>
                <li class="nav-item">
                  <a href="" data-bs-target="#license" data-bs-toggle="tab" class="nav-link small text-uppercase">License</a>
		            </li>
                <li class="nav-item">
                  <a href="" data-bs-target="#debugger" data-bs-toggle="tab" class="nav-link small text-uppercase">Debugger</a>
                </li>
                <li class="nav-item">
                  <a href="" data-bs-target="#changelog" data-bs-toggle="tab" class="nav-link small text-uppercase">Change Log</a>
                </li>
              </ul>
              <!--/tabs-->
              <div id="tabsJustifiedContent" class="tab-content">
                <div class="tab-pane fade active show p-1" id="about">
                  <p>The Infoblox SA Tools Portal offers a place for the Infoblox SA Team to leverage some web based tools.</p>
                  <p>Designed by <i class="fa fa-code" style="color:red"></i> by - <a target="_blank" rel="noopener noreferrer" href="https://github.com/TehMuffinMoo">Mat Cox</a></p>
                  <hr>
                  <small>
                    Running Version: <?php echo $ib->getVersion()[0]; ?>
                    </a>
                  </small>
                  <br>
                </div>
                <div class="tab-pane fade" id="support">
                  <br>
                  <p>Issues and Feature Requests can be raised via Github issues page by clicking <a href="https://github.com/TehMuffinMoo/ib-sa-report/issues" target="_blank">here</a>.</p>
                </div>
                <div class="tab-pane fade" id="license">
                  <p>MIT License</p>
                  <p>Copyright &copy; 2021-2024 <a target="_blank" rel="noopener noreferrer" href="https://github.com/TehMuffinMoo">Mat Cox</a></p>
                  <p>
                    Permission is hereby granted, free of charge, to any person obtaining a copy
                    of this software and associated documentation files (the "Software"), to deal
                    in the Software without restriction, including without limitation the rights
                    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
                    copies of the Software, and to permit persons to whom the Software is
                    furnished to do so, subject to the following conditions:
                  </p><p>
                    The above copyright notice and this permission notice shall be included in all
                    copies or substantial portions of the Software.
                  </p><p>
                    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
                    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
                    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
                    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
                    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
                    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
                    SOFTWARE.
                  </p>
                </div>
                <div class="tab-pane fade" id="debugger">
                  <br>
		              <pre><code id="whoami"></code></pre>
                </div>
                <div class="tab-pane fade" id="changelog">
                  <div>
                    <iframe class="changeLogFrame" src="api?f=getChangelog"></iframe>
                  </div>
                </div>
                <!--/tabs content-->
              </div>
            </div>
          </div>
          <hr>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- User Profile Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-labelledby="profileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="profileModalLabel">User Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"></span>
        </button>
      </div>
      <div class="modal-body" id="infoModelBody">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-4">
              <p class="rowLabel">Username</p>
            </div>
            <div class="col-md-8">
              <input type="text" class="form-control" id="userUsername" placeholder="Username" aria-describedby="userUsernameHelp" disabled>
              <small id="userUsernameHelp" class="form-text text-muted">Username</small>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-md-4">
              <p class="rowLabel">Name</p>
            </div>
            <div class="col-md-4">
              <input type="text" class="form-control" id="userFirstname" placeholder="First Name" aria-describedby="userFirstnameHelp" disabled>
              <small id="userFirstnameHelp" class="form-text text-muted">First Name</small>
            </div>
            <div class="col-md-4">
              <input type="text" class="form-control" id="userSurname" placeholder="Last Name" aria-describedby="userSurnameHelp" disabled>
              <small id="userSurnameHelp" class="form-text text-muted">Last Name</small>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-md-4">
              <p class="rowLabel">Email Address</p>
            </div>
            <div class="col-md-8">
              <input type="text" class="form-control" id="userEmail" placeholder="example@domain.com" aria-describedby="userEmailHelp" disabled>
              <small id="userEmailHelp" class="form-text text-muted">Email Address</small>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="accordion" id="resetPasswordAccordion">
              <div class="accordion-item">
                <h2 class="accordion-header" id="resetPasswordHeading">
                  <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#resetPassword" aria-expanded="true" aria-controls="resetPassword">
                  Reset Password
                  </button>
                </h2>
                <div id="resetPassword" class="accordion-collapse collapse" aria-labelledby="resetPasswordHeading" data-bs-parent="#resetPasswordAccordion">
                  <div class="accordion-body">
                    <div class="card-body">
                      <?php if ($ib->auth->getAuth()['Authenticated'] && $ib->auth->getAuth()['Type'] == 'SSO') { echo '
                        <div class="alert alert-warning" role="alert">
                        <center>You must reset your password via the Single Sign On provider.</center>
                        </div>';}
                      ?>
                      <div class="form-group">
                        <label for="userPassword">Password</label>
                        <i class="fa fa-info-circle hover-target" aria-hidden="true"></i>
                        <input type="password" class="form-control" id="userPassword" aria-describedby="userPasswordHelp" <?php if ($ib->auth->getAuth()['Authenticated'] && $ib->auth->getAuth()['Type'] == 'SSO') { echo 'disabled'; } ?> >
                        <small id="userPasswordHelp" class="form-text text-muted">Enter the updated password.</small>
                      </div>
                      <div class="form-group">
                        <label for="userPassword2">Verify Password</label>
                        <input type="password" class="form-control" id="userPassword2" aria-describedby="userPassword2Help" <?php if ($ib->auth->getAuth()['Authenticated'] && $ib->auth->getAuth()['Type'] == 'SSO') { echo 'disabled'; } ?> >
                        <small id="userPassword2Help" class="form-text text-muted">Enter the updated password again.</small>
                      </div>
                      <div id="popover" class="popover" role="alert">
                        <h4 class="alert-heading">Password Complexity</h4>
                        <p>Minimum of 8 characters</p>
                        <p>At least one uppercase letter</p>
                        <p>At least one lowercase letter</p>
                        <p>At least one number</p>
                        <p>At least one special character</p>
                      </div>
                      <hr>
                      <button type="button" class="btn btn-success" id="resetPasswordBtn">Save</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
  loadiFrame();
  heartBeat();

  function login() {
    location = "/login.php?redirect_uri="+window.location.href.replace("#","?");
  }

  function logout() {
    $.get('/api?f=logout', function(data) {
    }).done(function (data, status) {
      if (!data['Authenticated']) {
          toast("Logged Out","","Successfully Logged Out.","success");
        } else {
          toast("Error","","Failed to Log Out. Your session may still be active.","danger");
        }
        location.reload();
    }).fail(function( data, status ) {
      toast("Error","","Unknown API Error","danger");
    });
  }

  function setFontSize(fontsize) {
    console.log(fontsize);
    $('html').css('font-size',fontsize);
    setCookie('fontSize',fontsize,365);
    location.reload();
  }

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

    var cookie = getCookie('theme');
    let toggle = document.getElementById('themeToggle');
    if (cookie == "dark") {
      toggle.className = 'fa-regular fa-lightbulb toggleon toggler';
    } else {
      toggle.className = 'fa-solid fa-lightbulb toggleoff toggler';
    }


    $('.toggleThemeBtn').on('click', function () {
      $('.toggler').toggleClass('fas far toggleoff toggleon');
      if ($('.toggler').hasClass("toggleon")) {
        setCookie('theme','dark',365);
        location.reload();
      } else {
        setCookie('theme','light',365);
        location.reload();
      };
    });

    $('.infoBtn').on('click', function() {
      $('#infoModal').modal('show');
      $.getJSON('/api?f=whoami', function(whoami) {
        if (whoami.Groups != null) {whoami.Groups = whoami.Groups};
        if (whoami.headers.Cookie != null) {whoami.headers.Cookie = whoami.headers.Cookie.split('; ')};
        $('#whoami').text(JSON.stringify(whoami, null, 2));
      });
    });

    $('.profile').on('click', function() {
      $('#profileModal').modal('show');
      $.getJSON('/api?f=whoami', function(whoami) {
        $('#userUsername').val(whoami.Username);
        $('#userFirstname').val(whoami.Firstname);
        $('#userSurname').val(whoami.Surname);
        $('#userEmail').val(whoami.Email);
      });
    });

    $('.toggleFontSizeBtn, #fontDropdown-content').hover(function() {
      $('#fontDropdown').toggleClass('show');
    },function() {
      $('#fontDropdown').toggleClass('show');
    });

    $('.preventDefault').click(function(event){
      event.preventDefault();
    });

    $('.icon-link').on('click',function(elem) {
      $(elem.currentTarget).parent().toggleClass('showMenu')
    });
    let sidebar = document.querySelector(".sidebar");
    let sidebarBtn = document.querySelector(".bx-menu");
    sidebarBtn.addEventListener("click", ()=>{
      sidebar.classList.toggle("close");
    });

    $('#userPassword, #userPassword2').on('change', function() {
      var password = $('#userPassword').val();
      var confirmPassword = $('#userPassword2').val();
      
      if (password !== confirmPassword) {
        if (password !== "" && confirmPassword !== "") {
          toast("Warning","","The entered passwords do not match","danger","3000");
          $('#resetPasswordBtn').attr('disabled',true);
          $('#userPassword').css('color','red').css('border-color','red');
          $('#userPassword2').css('color','red').css('border-color','red');
        }
      } else {
        $('#resetPasswordBtn').attr('disabled',false);
        $('#userPassword').css('color','green').css('border-color','green');
        $('#userPassword2').css('color','green').css('border-color','green');
      }
    });

    $('#resetPasswordBtn').on('click', function(event) {
      // Prevent the default form submission
      event.preventDefault();
      isValid = true;

      // Get values from the input fields
      var password = $('#userPassword').val().trim();
      var confirmPassword = $('#userPassword2').val().trim();

      // Check if all fields are populated
      if (!password || !confirmPassword) {
        toast("Error","","Both the password and confirmation password are required","danger","30000");
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
        postArr.pw = password;
        $.post( "/api?f=passwordReset", postArr).done(function( data, status ) {
          if (data['Status'] == 'Success') {
            toast(data['Status'],"",data['Message'],"success");
            populateUsers();
            $('#profileModal').modal('hide');
          } else if (data['Status'] == 'Error') {
            toast(data['Status'],"",data['Message'],"danger","30000");
          } else {
            toast("Error","","Failed to reset password","danger","30000");
          }
        }).fail(function( data, status ) {
            toast("API Error","","Failed to reset password","danger","30000");
        })
      }
    });
  });
</script>