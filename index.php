<!DOCTYPE html>
<html lang="en">
<?php
  if (isset($_REQUEST['page'])) {
    header('Location: /#'.$_SERVER['QUERY_STRING']);
  }

  require_once(__DIR__.'/inc/inc.php');
  require_once(__DIR__.'/inc/me.php');

  if (GetAuth()['Authenticated'] == true) {
    $isAuth = true;
  } else {
    $isAuth = false;
  }
?>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Infoblox SA Tools</title>
</head>

<style>
#tabsJustifiedContent * p{
  text-align:center;
}
pre {
  background-color: #000;
  overflow: auto;
  font-family: 'Monaco',monospace;
  padding: 0 1em;
}
pre code {
  display: block;
  border: none;
  background: none;
  color: #FFF;
  white-space: pre-wrap;
  letter-spacing: normal;
  line-height: 1em;
}

.fontDropBtn {
  border: none;
  cursor: pointer;
}

.fontDropdown {
  position: relative;
  display: inline-block;
}

.fontDropdown-content {
  bottom: 100%;
  display: none;
  position: absolute;
  background-color: #31353d;
  min-width: 20px;
  box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
  z-index: 1;
}

.fontDropdown-content i {
  color: #6c7b88;
  padding: 12px 16px;
  text-decoration: none;
  display: block;
}

.fontDropdown-content i:hover {background-color: #393f4c;}

.show {display:block;}
</style>

<div class="page-wrapper chiller-theme toggled">
  <a id="show-sidebar" class="btn btn-sm btn-dark" href="#">
    <i class="fas fa-bars"></i>
  </a>
  <nav id="sidebar" class="sidebar-wrapper">
    <div class="sidebar-content">
      <div class="sidebar-brand">
        <a href="#page=default">Infoblox SA Tools</a>
        <div id="close-sidebar">
          <i class="fas fa-times"></i>
        </div>
      </div>
      <div class="sidebar-header">
	      <div class="user-pic">
          <svg id="UserSvg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 55 55" xml:space="preserve" role="img" class="jss11"><path d="M55,27.5C55,12.337,42.663,0,27.5,0S0,12.337,0,27.5c0,8.009,3.444,15.228,8.926,20.258l-0.026,0.023l0.892,0.752 c0.058,0.049,0.121,0.089,0.179,0.137c0.474,0.393,0.965,0.766,1.465,1.127c0.162,0.117,0.324,0.234,0.489,0.348 c0.534,0.368,1.082,0.717,1.642,1.048c0.122,0.072,0.245,0.142,0.368,0.212c0.613,0.349,1.239,0.678,1.88,0.98 c0.047,0.022,0.095,0.042,0.142,0.064c2.089,0.971,4.319,1.684,6.651,2.105c0.061,0.011,0.122,0.022,0.184,0.033 c0.724,0.125,1.456,0.225,2.197,0.292c0.09,0.008,0.18,0.013,0.271,0.021C25.998,54.961,26.744,55,27.5,55 c0.749,0,1.488-0.039,2.222-0.098c0.093-0.008,0.186-0.013,0.279-0.021c0.735-0.067,1.461-0.164,2.178-0.287 c0.062-0.011,0.125-0.022,0.187-0.034c2.297-0.412,4.495-1.109,6.557-2.055c0.076-0.035,0.153-0.068,0.229-0.104 c0.617-0.29,1.22-0.603,1.811-0.936c0.147-0.083,0.293-0.167,0.439-0.253c0.538-0.317,1.067-0.648,1.581-1 c0.185-0.126,0.366-0.259,0.549-0.391c0.439-0.316,0.87-0.642,1.289-0.983c0.093-0.075,0.193-0.14,0.284-0.217l0.915-0.764 l-0.027-0.023C51.523,42.802,55,35.55,55,27.5z M2,27.5C2,13.439,13.439,2,27.5,2S53,13.439,53,27.5 c0,7.577-3.325,14.389-8.589,19.063c-0.294-0.203-0.59-0.385-0.893-0.537l-8.467-4.233c-0.76-0.38-1.232-1.144-1.232-1.993v-2.957 c0.196-0.242,0.403-0.516,0.617-0.817c1.096-1.548,1.975-3.27,2.616-5.123c1.267-0.602,2.085-1.864,2.085-3.289v-3.545 c0-0.867-0.318-1.708-0.887-2.369v-4.667c0.052-0.52,0.236-3.448-1.883-5.864C34.524,9.065,31.541,8,27.5,8 s-7.024,1.065-8.867,3.168c-2.119,2.416-1.935,5.346-1.883,5.864v4.667c-0.568,0.661-0.887,1.502-0.887,2.369v3.545 c0,1.101,0.494,2.128,1.34,2.821c0.81,3.173,2.477,5.575,3.093,6.389v2.894c0,0.816-0.445,1.566-1.162,1.958l-7.907,4.313 c-0.252,0.137-0.502,0.297-0.752,0.476C5.276,41.792,2,35.022,2,27.5z"></path></svg>
        </div>
	      <div class="user-info">
          <?php if ($isAuth) { echo '
	        <span class="display-name">'.GetAuth()['DisplayName'].'</span>
	        <span class="user-name">'.GetAuth()['Username'].'</span>
          <span class="user-status">
            <i class="fa fa-circle"></i>
            <span>Online</span>
	        </span>';} else { echo '
          <span class="user-status">
            <span>Not Signed In</span>
      	  </span>';}?>
        </div>
      </div>
      <!-- sidebar-header  -->
      <!--<div class="sidebar-search">
        <div>
          <div class="input-group">
            <input type="text" class="form-control search-menu" placeholder="Search...">
            <div class="input-group-append">
              <span class="input-group-text">
                <i class="fa fa-search" aria-hidden="true"></i>
              </span>
            </div>
          </div>
        </div>
      </div>-->
      <!-- sidebar-search -->
      <div class="sidebar-menu dropdown">
	      <ul>
	        <li class="header-menu">
            <a href="#page=default" class="toggleFrame">
              <i class="fa fa-house"></i>
	            <span>Home</span>
            </a>
	        </li>
	        <!-- <li class="header-menu">
            <span>Reports</span>
	        </li> -->
          <li class="header-menu">
            <a href="#page=tools/dnstoolbox" class="toggleFrame">
              <i class="fa fa-toolbox"></i>
              <span>DNS Toolbox</span>
            </a>
          </li>
          <li class="header-menu">
            <a href="#page=uddi/security-assessment" class="toggleFrame">
              <i class="fa fa-magnifying-glass-chart"></i>
              <span>Security Assessment</span>
            </a>
          </li>
          <li class="header-menu">
            <a href="#page=uddi/threat-actors" class="toggleFrame">
              <i class="fa fa-skull"></i>
              <span>Threat Actors</span>
            </a>
          </li>
          <?php if ($isAuth) { echo '
	        <li class="header-menu">
            <span>DDI/IPAM</span>
	        </li>
          <li class="sidebar-dropdown">
            <a href="#" class="preventDefault">
              <i class="fa fa-tachometer-alt"></i>
              <span>Dashboards</span>
            </a>
            <div class="sidebar-submenu">
              <ul>
                <li>
		              <a href="#page=bloxoneddi/dhcp" class="toggleFrame">DHCP Utilization</a>
		            </li>

                <li>
                  <a href="#page=bloxoneddi/subnets" class="toggleFrame">Subnet Utilization</a>
                </li>
              </ul>
            </div>
	        </li>
          <li class="sidebar-dropdown">
            <a href="#" class="preventDefault">
              <i class="fa fa-chart-simple"></i>
              <span>Monitoring</span>
              <!--<span class="badge rounded-pill bg-danger">3</span>-->
	          </a>
            <div class="sidebar-submenu">
	            <ul>
                <li>
                  <a href="#page=pages/license-usage" class="toggleFrame">License Utilization</a>
		            </li>
	            </ul>
	          </div>
          </li>
          <li class="sidebar-dropdown">
            <a href="#" class="preventDefault">
              <i class="fa fa-toolbox"></i>
              <span>Tools</span>
              <!--<span class="badge rounded-pill bg-danger">3</span>-->
	          </a>
	          <div class="sidebar-submenu">
                <ul>
                  <li>
                    <a href="#page=bloxoneddi/iplookup" class="toggleFrame">IP Lookup</a>
                  </li>
                </ul>
              
                <ul>
                  <li>
                    <a href="#page=bloxoneddi/subnetlookup" class="toggleFrame">Subnet Lookup</a>
                  </li>
                </ul>
            </div>
          </li>
          <li class="sidebar-dropdown">
            <a href="#" class="preventDefault">
              <i class="fa fa-magnifying-glass"></i>
              <span>Query</span>
            </a>
	          <div class="sidebar-submenu">
              <ul>
                <li>
		              <a href="#page=bloxoneddi/dnslogs" class="toggleFrame">DNS Logs</a>
		            </li>
	            </ul>
            </div>
          </li>

          <li class="header-menu">
            <span>Help & Guidance</span>
          </li>
          <li>
            <a href="#page=prx/docs/" class="toggleFrame">
              <i class="fa fa-book"></i>
              <span>Documentation</span>
            </a>
          </li>';}
          if (CheckAccess(null,null,"ADMIN-Menu")) { echo '
          <li class="header-menu">
            <span>Admin</span>
          </li>
	        <li class="sidebar-dropdown">
            <a href="#" class="preventDefault">
              <i class="fas fa-user-shield"></i>
              <span>Admin</span>
	          </a>
            <div class="sidebar-submenu">
              <ul>
                <li class="sidebar-subdropdown">
		              <a href="#">Settings</a>
		              <ul class="sidebar-subsubmenu">';
                    if (CheckAccess(null,"ADMIN-RBAC")) { echo '
		                <li><a href="#page=core/rbac" class="toggleFrame">Role Based Access</a></li>
                    ';}
                    if (CheckAccess(null,"ADMIN-CONFIG")) { echo '
                    <li><a href="#page=core/configuration" class="toggleFrame">Configuration</a></li>
                    ';}
                  echo '
		              </ul>
		            </li>
                <li class="sidebar-subdropdown">
		              <a href="#">Logs</a>
                  <ul class="sidebar-subsubmenu">';
                    if (CheckAccess(null,"ADMIN-LOGS")) { echo '
                    <li><a href="#page=core/logs" class="toggleFrame">Portal Logs</a></li>
                    ';}
                  echo '
		              </ul>
		            </li>
	            </ul>
	          </div>
	        </li>
          ';}?>

          <li class="header-menu">
            <span>Account</span>
          </li>
          <?php if ($isAuth) { echo '
	        <li>
            <a href="#" onclick="logout();" id="logoutBtn">
              <i class="fa fa-sign-out"></i>
              <span>Logout</span>
            </a>
	        </li>';} else { echo '
	        <li>
            <a href="#" onclick="login();" id="loginBtn">
              <i class="fa fa-sign-in"></i>
              <span>Login</span>
            </a>
	        </li>';}?>
	      </ul>
      </div>

    <!-- sidebar-menu  -->
    </div>
    <!-- sidebar-content  -->
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
            <i onclick='setFontSize("16px")'>16px (default)</i>
            <i onclick='setFontSize("16px")'>17px</i>
            <i onclick='setFontSize("18px")'>18px</i>
          </div>
        </div>
      </a>
      <a href="#" class="toggleThemeBtn preventDefault">
        <i class="fas fa-circle toggler" id="themeToggle"></i>
      </a>
      <!--<a href="#">
        <i class="fa fa-envelope"></i>
        <span class="badge badge-pill badge-success notification">7</span>
      </a>
      <a href="#">
        <i class="fa fa-cog"></i>
        <span class="badge-sonar"></span>
      </a>
      <a href="#">
        <i class="fa fa-power-off"></i>
      </a>-->
    </div>
  </nav>

  <!-- sidebar-wrapper  -->
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
  <!-- page-content" -->
</div>
</body>
</html>


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
                <iframe class="changeLogFrame" src="api?function=getChangelog"></iframe>
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



<script>
loadiFrame();

function login() {
  location = "/login.php?redirect_uri="+window.location.href.replace("#","?");
}

function logout() {
  $.get('/api?function=logout', function(data) {
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

//$(document).ready(function() {
//  $('#sidebar .toggleFrame').click(function(element) {
//    loadiFrame(element.currentTarget.href);
//  });
//  window.onhashchange = function(hash) {
//    console.log(hash);
//    loadiFrame(hash.newURL);
//  }
//});

var cookie = getCookie('theme');
let toggle = document.getElementById('themeToggle');
if (cookie == "dark") {
  toggle.className = 'far fa-circle toggleon toggler';
} else {
  toggle.className = 'fas fa-circle toggleoff toggler';
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
  $.getJSON('/api/?function=whoami', function(whoami) {
    if (whoami.headers['X-Authentik-Uid'] != null) {
      if (whoami.Groups != null) {whoami.Groups = whoami.Groups.split('|')};
    } else {
      if (whoami.Groups != null) {whoami.Groups = whoami.Groups.split(',')};
    }
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

heartBeat();

$('.preventDefault').click(function(event){
  event.preventDefault();
});
</script>
