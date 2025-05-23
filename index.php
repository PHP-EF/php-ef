<?php
  if ($_SERVER['REQUEST_URI'] == '/?') {
    header('Location: /');
  }
  if (isset($_REQUEST['page'])) {
    header('Location: /#'.$_SERVER['QUERY_STRING']);
  }

  require_once(__DIR__.'/inc/inc.php');
  require_once(__DIR__.'/inc/me.php');

  if ($phpef->auth->getAuth()['Authenticated'] == true) {
    $isAuth = true;
  } else {
    $isAuth = false;
  }

  $navLinks = $phpef->pages->get();
  $defaultLink = array_filter($navLinks, function($link) {
    return array_key_exists('isDefault', $link) && $link['isDefault'] == 1;
  });
  $defaultLink = reset($defaultLink);

  function filterNavLinksByMenu($navLinks, $menuName) {
    return array_filter($navLinks, function($link) use ($menuName) {
        return $link['Menu'] === $menuName && $link['Type'] == 'MenuLink';
    });
  }
  function filterSubmenuLinksByMenu($navLinks, $menuName) {
    return array_filter($navLinks, function($link) use ($menuName) {
        return $link['Menu'] === $menuName && $link['Type'] == 'SubMenu';
    });
  }
  function filterNavLinksBySubMenu($navLinks, $linkName) {
    return array_filter($navLinks, function($link) use ($linkName) {
        return $link['Submenu'] === $linkName && $link['Type'] == 'SubMenuLink';
    });
  }

  $sidebarExpandOnHover = $phpef->config->get('Styling','sidebar')['expandOnHover'] ? ' expandOnHover' : '';
  $sidebarCollapseByDefault = $phpef->config->get('Styling','sidebar')['collapseByDefault'] ? ' close' : '';
  $sidebarClasses = $sidebarExpandOnHover.$sidebarCollapseByDefault;
?>

<!DOCTYPE html>

<html lang="en" dir="ltr">
  <head>
    <meta charset="UTF-8">
    <title> <?php echo $phpef->config->get('Styling')['websiteTitle']; ?> </title>
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
  </head>
<body>
  <div class="sidebar<?php echo $sidebarClasses ?>">
    <div class="logo-details">
      <?php
      $smLogoPath = $phpef->config->get('Styling', 'logo-sm')['Image'] ?? '';
      $smLogoPath = $smLogoPath !== '' ? $smLogoPath : '/assets/images/php-ef-icon.png';
      $smLogoCSS = $phpef->config->get('Styling', 'logo-sm')['CSS'] ?? '';
      echo '<img class="logo-sm" src="' . $smLogoPath . '" style="'.$smLogoCSS.'"></img>';

      if ($phpef->config->get('Styling', 'websiteTitleNotLogo') ?? false) {
          $fontSize = $phpef->config->get('Styling', 'websiteTitleFontSize') ?? "42";
          echo '<span class="logo-text" style="font-size: '.$fontSize.';">'.$phpef->config->get('Styling', 'websiteTitle') ?? ''.'</span>';
      } else {
          $lgLogoPath = $phpef->config->get('Styling', 'logo-lg')['Image'] ?? '';
          $lgLogoPath = $lgLogoPath !== '' ? $lgLogoPath : '/assets/images/php-ef-icon-text.png';
          $lgLogoCSS = $phpef->config->get('Styling', 'logo-lg')['CSS'] ?? '';
          echo '<img class="logo-lg" src="' . $lgLogoPath . '" style="'.$lgLogoCSS.'"></img>';
      }
      ?>
    </div>
    <ul class="nav-links">
      <?php
foreach ($navLinks as $navLink) {
  $MenuItem = '';
  switch($navLink['Type']) {
      case 'Link':
          // Create Nav Link
          if (!$navLink['ACL'] || $phpef->auth->checkAccess($navLink['ACL'])) {
              $LinkElem = $phpef->getImageOrIcon($navLink['Icon']);
              if ($navLink['LinkType'] == 'NewWindow') {
                  $MenuItem .= <<<EOD
                  <li class="menu-item">
                      <div class="icon-link">
                          <a href="{$navLink['Url']}" target="_blank">
                              {$LinkElem}
                              <span class="link_name">{$navLink['Name']}</span>
                          </a>
                          <ul class="sub-menu blank">
                              <li><a class="link_name" href="{$navLink['Url']}" target="_blank">{$navLink['Name']}</a></li>
                          </ul>
                      </div>
                  </li>
                  EOD;
              } else {
                  $MenuItem .= <<<EOD
                  <li class="menu-item">
                      <div class="icon-link">
                          <a href="#page={$navLink['Name']}" class="toggleFrame" data-page-url="{$navLink['Url']}" data-page-type="{$navLink['LinkType']}">
                              {$LinkElem}
                              <span class="link_name">{$navLink['Name']}</span>
                          </a>
                          <ul class="sub-menu blank">
                              <li><a class="link_name toggleFrame" href="#page={$navLink['Name']}" data-page-url="{$navLink['Url']}" data-page-type="{$navLink['LinkType']}">{$navLink['Name']}</a></li>
                          </ul>
                      </div>
                  </li>
                  EOD;
              }
          }
          break;
      case 'Menu':
          // Filter links and submenus
          $filteredMenuLinks = filterNavLinksByMenu($navLinks, $navLink['Name']);
          $filteredSubMenuLinks = filterSubmenuLinksByMenu($navLinks, $navLink['Name']);
          
          // Check if there are any valid links or submenus
          $hasValidLinks = false;
          foreach ($filteredMenuLinks as $filteredMenuLink) {
              if ($phpef->auth->checkAccess($filteredMenuLink['ACL'])) {
                  $hasValidLinks = true;
                  break;
              }
          }
          if (!$hasValidLinks) {
              foreach ($filteredSubMenuLinks as $filteredSubMenuLink) {
                  $filteredSubMenuNavLinks = filterNavLinksBySubMenu($navLinks, $filteredSubMenuLink['Name']);
                  foreach ($filteredSubMenuNavLinks as $filteredSubMenuNavLink) {
                      if ($phpef->auth->checkAccess($filteredSubMenuNavLink['ACL'])) {
                          $hasValidLinks = true;
                          break 2;
                      }
                  }
              }
          }

          // Create Nav Menu Dropdown only if there are valid links
          if ($hasValidLinks) {
            $MenuIconElem = $phpef->getImageOrIcon($navLink['Icon']);
              $MenuItem .= <<<EOD
              <li class="menu-item">
                  <div class="icon-link menu-item-dropdown">
                      <a href="#" class="preventDefault">
                          {$MenuIconElem}
                          <span class="link_name">{$navLink['Name']}</span>
                      </a>
                      <i class="bx bxs-chevron-down arrow"></i>
                  </div>
                  <ul class="sub-menu">
              EOD;

              $MenuItem .= "<a class='link_name preventDefault' href='#'>{$navLink['Name']}</a>";

              // Create Nav Menu Links
              foreach ($filteredMenuLinks as $filteredMenuLink) {
                  if ($phpef->auth->checkAccess($filteredMenuLink['ACL'])) {
                    $filteredMenuLinkIcon = $phpef->getImageOrIcon($filteredMenuLink['Icon']);
                      $MenuItem .= <<<EOD
                      <li>
                          <a href="#page={$filteredMenuLink['Name']}" class="toggleFrame" data-page-url="{$filteredMenuLink['Url']}" data-page-type="{$filteredMenuLink['LinkType']}">
                              {$filteredMenuLinkIcon}
                              <span>{$filteredMenuLink['Name']}</span>
                          </a>
                      </li>
                      EOD;
                  }
              }

              // Create Nav Menu Submenus
              foreach ($filteredSubMenuLinks as $filteredSubMenuLink) {
                  $filteredSubMenuLinkIcon = $phpef->getImageOrIcon($filteredSubMenuLink['Icon']);
                  $Submenu = <<<EOD
                  <li class="sub-menu-item">
                      <div class="icon-link menu-item-dropdown">
                          <a href="#" class="preventDefault">
                              {$filteredSubMenuLinkIcon}
                              <span>{$filteredSubMenuLink['Name']}</span>
                          </a>
                          <i class="bx bxs-chevron-down arrow"></i>
                      </div>
                      <ul class="sub-sub-menu">
                          <li>
                  EOD;

                  // Create Nav Submenu Links
                  $SubmenuLinks = '';
                  $filteredSubMenuNavLinks = filterNavLinksBySubMenu($navLinks, $filteredSubMenuLink['Name']);
                  foreach ($filteredSubMenuNavLinks as $filteredSubMenuNavLink) {
                      if ($phpef->auth->checkAccess($filteredSubMenuNavLink['ACL'])) {
                          $SubmenuLinks .= <<<EOD
                          <a href="#page={$filteredSubMenuNavLink['Name']}" class="toggleFrame" data-page-url="{$filteredSubMenuNavLink['Url']}" data-page-type="{$filteredSubMenuNavLink['LinkType']}">{$filteredSubMenuNavLink['Name']}</a>
                          EOD;
                      }
                  }

                  // Only display Sub Menu if the user has access to links underneath it
                  if ($SubmenuLinks != '') {
                      $MenuItem .= $Submenu . $SubmenuLinks . <<<EOD
                          </li>
                      </ul>
                  </li>
                  EOD;
                  }
              }

              $MenuItem .= <<<EOD
                  </ul>
              </li>
              EOD;
          }
          break;
        }
        echo $MenuItem;
      }
    ?>
    </ul>
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
  </div>

  <section class="home-section">
    <div class="nav-bar">
      <i class='bx bx-menu' ></i>
      <span class="title-text"></span>
      <div class="profile-name-user ms-auto me-3">
        <?php if ($phpef->auth->getAuth()['Authenticated']) { echo '
        <div class="dropdown">
          <button class="dropbtn">'; echo $phpef->auth->getAuth()['DisplayName']. '
            <i class="bx bxs-chevron-down arrow" ></i>
          </button>
          <div class="dropdown-content">
            <ul>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li>
                <a href="#" class="profile preventDefault">
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
      <div class="container-fluid p-0 main-container">
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
              <div class="d-flex">
                <!--tabs-->
                <ul id="tabsJustified" class="nav nav-tabs flex-column info-nav">
                  <li class="nav-item">
                    <a href="" data-bs-target="#about" data-bs-toggle="tab" class="nav-link text-uppercase active">About</a>
                  </li>
                  <li class="nav-item">
                    <a href="" data-bs-target="#support" data-bs-toggle="tab" class="nav-link text-uppercase">Support</a>
                  </li>
                  <li class="nav-item">
                    <a href="" data-bs-target="#license" data-bs-toggle="tab" class="nav-link text-uppercase">License</a>
                  </li>
                  <li class="nav-item">
                    <a href="" data-bs-target="#debugger" data-bs-toggle="tab" class="nav-link text-uppercase">Debugger</a>
                  </li>
                  <li class="nav-item">
                    <a href="" data-bs-target="#changelog" data-bs-toggle="tab" class="nav-link text-uppercase">Change Log</a>
                  </li>
                </ul>
                <!--/tabs-->
                <div class="tab-content">
                  <div class="tab-pane fade text-center active show p-1" id="about">
                    <?php echo $phpef->config->get('Styling')['html']['about']; ?>
                    <p>Designed by <i class="fa fa-code" style="color:red"></i> by - <a target="_blank" rel="noopener noreferrer" href="https://github.com/TehMuffinMoo">Mat Cox</a></p>
                    <hr>
                    <small>
                      <?php echo '<span class="fa-solid fa-code-compare"></span> PHP-EF: v'.$phpef->getVersion()[0].' | <span class="fa-solid fa-database"></span> Database: v'.$phpef->dbHelper->getDatabaseVersion(); ?>
                      </a>
                    </small>
                    <br>
                  </div>
                  <div class="tab-pane fade text-center" id="support">
                    <br>
                    <p>Issues and Feature Requests can be raised via Github issues page by clicking <a href="https://github.com/php-ef/php-ef/issues" target="_blank">here</a>.</p>
                  </div>
                  <div class="tab-pane fade text-center" id="license">
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
                  <div class="tab-pane fade text-center" id="changelog">
                    <div>
                      <iframe class="changeLogFrame" src="/api/changelog"></iframe>
                    </div>
                  </div>
                  <!--/tabs content-->
                </div>
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
            <div class="accordion" id="mfaAccordion">
              <div class="accordion-item">
                <h2 class="accordion-header" id="mfaHeading">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mfa" aria-expanded="true" aria-controls="mfa">
                  Multi-Factor Authentication
                  </button>
                </h2>
                <div id="mfa" class="accordion-collapse collapse" aria-labelledby="mfaHeading" data-bs-parent="#mfaAccordion">
                  <div class="accordion-body">
                    <div class="card-body" id="mfaCard">
                      <?php
                        $mfaContent = '';
                        $mfaSetup = 'flex';
                        if ($phpef->auth->mfaSettings()['multifactor_enabled']) {
                          switch($phpef->auth->mfaSettings()['multifactor_type']) {
                            case 'totp':
                              $mfaContent .= '
                              <div class="alert alert-info text-center" role="alert" id="mfaAlert">
                                <h3>'.strtoupper($phpef->auth->mfaSettings()['multifactor_type']).'</h3>
                                <span>Multifactor Authentication is configured.</span>
                                <button class="btn btn-danger mt-2" onclick="reconfigureMFA();">Reconfigure</button>
                              </div>';
                              break;
                            default:
                              $mfaContent .= '
                              <div class="alert alert-warning text-center" role="alert" id="mfaAlert">
                                <h3>'.strtoupper($phpef->auth->mfaSettings()['multifactor_type']).'</h3>
                                <span>Multifactor Authentication is invalid.</span>
                                <button class="btn btn-danger mt-2" onclick="reconfigureMFA();">Reconfigure</button>
                              </div>';
                          }
                          $mfaSetup = 'none';
                        }
                        $mfaContent .= '
                          <div class="row" style="display:'.$mfaSetup.'" id="mfaRegister">
                            <div class="form-group col-md-8">
                              <label for="mfaType">MFA Type</label>
                              <select class="form-control" id="mfaType" aria-describedby="mfaTypeHelp">
                                <option value="totp">TOTP</option>
                              </select>
                            </div>
                            <div class="col-md-4 mt-4">
                              <button class="btn btn-success w-100" onclick="registerMFA()">Configure</button>
                            </div>
                          </div>
                          <div class="row mt-3" style="display:none" id="mfaSetup">
                            <img id="qrCodeImage"/>
                            <div class="mt-2" id="mfaVerification"></div>
                          </div>
                        ';
                        echo $mfaContent;
                      ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="accordion" id="resetPasswordAccordion">
              <div class="accordion-item">
                <h2 class="accordion-header" id="resetPasswordHeading">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#resetPassword" aria-expanded="true" aria-controls="resetPassword">
                  Reset Password
                  </button>
                </h2>
                <div id="resetPassword" class="accordion-collapse collapse" aria-labelledby="resetPasswordHeading" data-bs-parent="#resetPasswordAccordion">
                  <div class="accordion-body">
                    <div class="card-body">
                      <?php if ($phpef->auth->getAuth()['Authenticated'] && $phpef->auth->getAuth()['Type'] != 'Local') { echo '
                        <div class="alert alert-warning" role="alert">
                        <center>You must reset your password via the external provider ('.$phpef->auth->getAuth()['Type'].').</center>
                        </div>';}
                      ?>
                      <div class="form-group">
                        <label for="userPassword">Password</label>
                        <i class="fa fa-info-circle hover-target" aria-hidden="true"></i>
                        <input type="password" class="form-control" id="userPassword" aria-describedby="userPasswordHelp" <?php if ($phpef->auth->getAuth()['Authenticated'] && $phpef->auth->getAuth()['Type'] != 'Local') { echo 'disabled'; } ?> >
                        <small id="userPasswordHelp" class="form-text text-muted">Enter the updated password.</small>
                      </div>
                      <div class="form-group">
                        <label for="userPassword2">Verify Password</label>
                        <input type="password" class="form-control" id="userPassword2" aria-describedby="userPassword2Help" <?php if ($phpef->auth->getAuth()['Authenticated'] && $phpef->auth->getAuth()['Type'] != 'Local') { echo 'disabled'; } ?> >
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
          <hr>
          <div class="row">
            <div class="accordion" id="sessionTokenAccordion">
              <div class="accordion-item">
                <h2 class="accordion-header" id="sessionTokenHeading">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sessionToken" aria-expanded="true" aria-controls="sessionToken">
                  Session Tokens
                  </button>
                </h2>
                <div id="sessionToken" class="accordion-collapse collapse" aria-labelledby="sessionTokenHeading" data-bs-parent="#sessionTokenAccordion">
                  <div class="accordion-body p-0">
                    <div class="card-body">
                      <table id="sessionTokenTable"></table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="accordion" id="apiKeyAccordion">
              <div class="accordion-item">
                <h2 class="accordion-header" id="apiKeyHeading">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#apiKey" aria-expanded="true" aria-controls="apiKey">
                  API Keys
                  </button>
                </h2>
                <div id="apiKey" class="accordion-collapse collapse" aria-labelledby="apiKeyHeading" data-bs-parent="#apiKeyAccordion">
                  <div class="accordion-body p-0">
                    <div class="card-body">
                      <table id="apiTokenTable"></table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php
            $phpef->hooks->executeHook('user_profile_body');
          ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
  loadContent(null,"<?php echo $defaultLink['Name'] ?? null ?>");
  heartBeat();

  function login() {
    location = "/login.php?redirect_uri="+window.location.href.replace("#","?");
  }

  function logout() {
    queryAPI('GET', '/api/auth/logout').done(function(data) {
      if (data['result'] == "Success" && !data['data']['Authenticated']) {
          toast("Logged Out","","Successfully Logged Out.","success");
        } else {
          toast("Error","","Failed to Log Out. Your session may still be active.","danger");
        }
        location.reload();
    }).fail(function( data, status ) {
      toast("Error","","Unknown API Error","danger");
    });
  }

  function registerMFA() {
    var mfaType = $("#mfaType").find(":selected").val();
    queryAPI("POST","/api/auth/mfa/"+mfaType+"/register").done(function(data) {
      if (data['result'] == "Success") {

        switch(mfaType) {
          case 'totp':
            // Get & Build QR Code
            var qrCodeBase64String = "data:image/png;base64,"+data['data'];
            var qrCodeImage = $("#qrCodeImage");
            qrCodeImage.attr('src',qrCodeBase64String);

            // Build the verification step
            var mfaVerification = $("#mfaVerification");
            mfaVerification.append(`<div class="row"><div class="form-group col-md-8"><label for="totpVerify">Enter Verification Code</label><input class="form-control" id="totpVerify" placeholder="123456"></input></div><div class="col-md-4 mt-4"><button class="btn btn-success w-100" onclick='verifyMFA("totp");'>Verify</button></div></div>`);
            break;
          default:
            toast('Error',"",'Invalid MFA Method','danger','30000');
            break;
        }
        $("#mfaSetup").css('display','flex')
      } else {
        toast(data['result'],"",data['message'],'danger','30000');
      }
    }).fail(function(jqXHR, textStatus, errorThrown) {
      toast(textStatus,"","Error: "+jqXHR.status+": "+errorThrown,"danger");
    });;
  }

  function verifyMFA(mfaType) {
    switch(mfaType) {
      case 'totp':
        var verificationCode = $("#totpVerify").val();
        queryAPI("POST","/api/auth/mfa/"+mfaType+"/register/verify",{"totp_code": verificationCode}).done(function(data) {
          if (data['result'] == "Success") {
            queryAPI("GET","/api/auth/mfa/settings").done(function(data) {
              if (data['result'] == "Success" && data['data'][`${mfaType}_verified`]) {
                $("#mfaCard").append(`
                <div class="alert alert-info text-center" role="alert" id="mfaAlert">
                  <h3>${mfaType.toUpperCase()}</h3>
                  <span>Multifactor Authentication is configured.</span>
                  <button class="btn btn-danger mt-2" onclick="reconfigureMFA();">Reconfigure</button>
                </div>
                `);
                toast('Successful','','TOTP Successfully Verified','success');
              } else {
                toast('Warning','','TOTP was successfully verified, but the verification status failed to update','warning');
              }
            }).fail(function(jqXHR, textStatus, errorThrown) {
              toast(textStatus,"","Error: "+jqXHR.status+": "+errorThrown,"danger");
            });
            $("#mfaSetup,#mfaRegister").css('display','none');
            $("#mfaVerification").css('display','none').html('');
            $("#qrCodeImage").css('display','none').attr('src','');
          } else {
            toast(data['result'],"",data['message'],'danger','30000');
          }
        }).fail(function(jqXHR, textStatus, errorThrown) {
          toast(textStatus,"","Error: "+jqXHR.status+": "+errorThrown,"danger");
        });
        break;
      default:
        toast('Error',"",'Invalid MFA Method','danger','30000');
        break;
    }
  }

  function reconfigureMFA() {
    $("#mfaAlert").remove();
    $("#mfaRegister").css('display','flex');
  }

  function setFontSize(fontsize) {
    $('html').css('font-size',fontsize);
    setCookie('fontSize',fontsize,365);
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
    if (cookie) {
      if (cookie == "dark") {
        $('html').attr('data-bs-theme',"dark");
        toggle.className = 'fa-regular fa-lightbulb toggleon toggler';
      } else {
        $('html').attr('data-bs-theme',"light");
        toggle.className = 'fa-solid fa-lightbulb toggleoff toggler';
      }
    } else {
      var defaultTheme = "<?php echo $phpef->config->get('Styling','theme')['default'] ?? 'dark' ?>";
      $('html').attr('data-bs-theme',defaultTheme);
      if (defaultTheme == "dark") {
        toggle.className = 'fa-regular fa-lightbulb toggleon toggler';
      } else {
        toggle.className = 'fa-solid fa-lightbulb toggleoff toggler';
      }
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
      queryAPI('GET','/api/auth/whoami').done(function(whoami) {
        if (whoami.data.headers.Cookie != null) {whoami.data.headers.Cookie = whoami.data.headers.Cookie.split('; ')};
        $('#whoami').text(JSON.stringify(whoami.data, null, 2));
      });
    });

    $('.profile').on('click', function() {
      $('#profileModal').modal('show');
      queryAPI('GET','/api/auth/whoami').done(function(whoami) {
        $('#userUsername').val(whoami.data.Username);
        $('#userFirstname').val(whoami.data.Firstname);
        $('#userSurname').val(whoami.data.Surname);
        $('#userEmail').val(whoami.data.Email);
      });

      $("#sessionTokenTable").bootstrapTable("destroy");
      $("#sessionTokenTable").bootstrapTable({
        url: '/api/auth/tokens/session',
        dataField: 'data',
        sortable: true,
        sortName: "exp",
        sortOrder: "desc",
        columns: [{
          field: "last_10_chars",
          title: "Token",
          sortable: true,
          formatter: appendDotsFormatter
        },{
          field: "exp",
          title: "Expiry",
          sortable: true
        },{
          events: tokenActionEvents,
          formatter: deleteActionFormatter
        }],
        rowStyle: function(row, index) {
          if (row.active) {
            return {
              classes: 'table-success'
            };
          }
          return {};
        }
      });

      $("#apiTokenTable").bootstrapTable("destroy");
      $("#apiTokenTable").bootstrapTable({
        url: '/api/auth/tokens/api',
        dataField: 'data',
        sortable: true,
        sortName: "expiry",
        sortOrder: "desc",
        buttons: apiTokenTableButtons,
        columns: [{
          field: "last_10_chars",
          title: "Token",
          sortable: true,
          formatter: appendDotsFormatter
        },{
          field: "exp",
          title: "Expiry",
          sortable: true
        },{
          events: tokenActionEvents,
          formatter: deleteActionFormatter
        }]
      });
    });

    $('.toggleFontSizeBtn, #fontDropdown-content').hover(function() {
      $('#fontDropdown').toggleClass('show');
    },function() {
      $('#fontDropdown').toggleClass('show');
    });

    $('.menu-item .menu-item-dropdown').on('click',function(elem) {
      $(elem.currentTarget).parent().toggleClass('showMenu')
    });
    $('.sub-menu .menu-item-dropdown').on('click',function(elem) {
      $(elem.currentTarget).next().toggleClass('showMenu')
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
        queryAPI("POST", "/api/auth/password/reset", postArr).done(function( data, status ) {
          if (data['result'] == 'Success') {
            toast(data['result'],"",data['message'],"success");
            $('#profileModal').modal('hide');
          } else if (data['result'] == 'Error') {
            toast(data['result'],"",data['message'],"danger","30000");
          } else {
            toast("Error","","Failed to reset password","danger","30000");
          }
        }).fail(function( data, status ) {
            toast("API Error","","Failed to reset password","danger","30000");
        })
      }
    });

    $('.toggleFrame').click(function(element) {
      loadContent(element);
    });

    <?php
      $customJS = $Styling['js']['custom'] ?? '';
      echo $customJS;
    ?>
  });
</script>