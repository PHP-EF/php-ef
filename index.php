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

<!-- Created by CodingLab |www.youtube.com/CodingLabYT-->
<html lang="en" dir="ltr">
  <head>
    <meta charset="UTF-8">
    <title> Infoblox SA Tools </title>
    <link rel="stylesheet" href="/assets/css/theme-0.0.1.css">
    <!-- Boxiocns CDN Link -->
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  </head>
<body>
  <div class="sidebar">
    <div class="logo-details">
      <img class="logo-sm" src="/assets/images/Other/ib-diamonds.png"></img>
      <!-- <span class="logo_name">Infoblox SA Tools</span> -->
      <img class="logo-lg" src="/assets/images/Other/ib-logo-dark.svg"></img>
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
          <button class="dropbtn">Mat Cox
            <i class="bx bxs-chevron-down arrow" ></i>
          </button>
          <div class="dropdown-content">
            <ul>
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
		            <pre>
                  <code id="whoami"></code>
                </pre>
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

  $('.toggleFontSizeBtn, #fontDropdown-content').hover(function() {
    $('#fontDropdown').toggleClass('show');
  },function() {
    $('#fontDropdown').toggleClass('show');
  });

  function setFontSize(fontsize) {
    console.log(fontsize);
    $('html').css('font-size',fontsize);
    setCookie('fontSize',fontsize,365);
    location.reload();
  }

  $('.preventDefault').click(function(event){
    event.preventDefault();
  });

  //$(document).ready(function() {
  //  $('#sidebar .toggleFrame').click(function(element) {
  //    loadiFrame(element.currentTarget.href);
  //  });
  //  window.onhashchange = function(hash) {
  //    console.log(hash);
  //    loadiFrame(hash.newURL);
  //  }
  //});
  $('.icon-link').on('click',function(elem) {
    $(elem.currentTarget).parent().toggleClass('showMenu')
  });
  let sidebar = document.querySelector(".sidebar");
  let sidebarBtn = document.querySelector(".bx-menu");
  sidebarBtn.addEventListener("click", ()=>{
    sidebar.classList.toggle("close");
  });
  </script>
</body>
</html>