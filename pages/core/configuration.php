<?php
  require_once(__DIR__."/../../inc/inc.php");
  if ($ib->auth->checkAccess("ADMIN-CONFIG") == false) {
    die();
  }

return '

<style>
.card {
  padding: 10px;
}
</style>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-12 col-xl-12 mx-auto">
      <h2 class="h3 mb-4 page-title">Configuration</h2>
      <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="true">General</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="customisation-tab" data-toggle="tab" href="#customisation" role="tab" aria-controls="customisation" aria-selected="false">Customisation</a>
        </li>
                <li class="nav-item">
          <a class="nav-link" id="plugins-tab" data-toggle="tab" href="#plugins" role="tab" aria-controls="plugins" aria-selected="false">Plugins</a>
        </li>
      </ul>
      <div class="tab-content" id="tabContent">
        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
          <form id="configurationForm">
            <div class="my-4">
              <h5 class="mb-0 mt-5">Configuration</h5>
              <p>Use the fields below to modify the configuration for '.$ib->config->get('Styling')['websiteTitle'].'.</p>
              <div class="card border-secondary">
                <div class="card-title">
                  <h5>System</h5>
                </div>
                <div class="form-group">
                  <label for="System[logfilename]">Log File Name</label>
                  <input type="text" class="form-control info-field" id="System[logfilename]" aria-describedby="System[logfilename]Help" name="System[logfilename]">
                  <small id="System[logfilename]Help" class="form-text text-muted">The name of the log file <b>without</b> the file extension.</small>
                </div>
                <div class="form-group">
                  <label for="System[logdirectory]">Log Directory</label>
                  <input type="text" class="form-control info-field" id="System[logdirectory]" aria-describedby="System[logdirectory]Help" name="System[logdirectory]">
                  <small id="System[logdirectory]Help" class="form-text text-muted">The full path of the log directory including the trailing slash.</small>
                </div>
                <div class="form-group">
                  <label for="System[loglevel]">Log Level</label>
                  <select type="select" class="form-select info-field" id="System[loglevel]" aria-describedby="System[loglevel]Help" name="System[loglevel]">
                    <option>Debug</option>
                    <option>Info</option>
                    <option>Warning</option>
                  </select>
                  <small id="System[loglevel]Help" class="form-text text-muted">Specify which log level you would like to use. Enabling <b>Debug</b> logs will generate lots of data.</small>
                </div>
                <div class="form-group">
                  <label for="System[logretention]">Log Retention</label>
                  <input type="text" class="form-control info-field" id="System[logretention]" aria-describedby="System[logretention]Help" name="System[logretention]">
                  <small id="System[logretention]Help" class="form-text text-muted">How many days to keep system logs before they are purged.</small>
                </div>
                <div class="form-group">
                  <label for="System[CURL-Timeout]">CURL Timeout</label>
                  <input type="text" class="form-control info-field" id="System[CURL-Timeout]" aria-describedby="System[CURL-Timeout]Help" name="System[CURL-Timeout]">
                  <small id="System[CURL-Timeout]Help" class="form-text text-muted">Specify the timeout used for CURL requests. (Can be increased if long running outbound API calls time out)</small>
                </div>
                <div class="form-group">
                  <label for="System[CURL-ConnectTimeout]">CURL Timeout on Connect</label>
                  <input type="text" class="form-control info-field" id="System[CURL-ConnectTimeout]" aria-describedby="System[CURL-ConnectTimeout]Help" name="System[CURL-ConnectTimeout]">
                  <small id="System[CURL-ConnectTimeout]Help" class="form-text text-muted">Specify the timeout used for CURL requests on connect. (Shouldn"t need to be increased)</small>
                </div>
              </div>
              <br>
              <div class="card border-secondary">
                <div class="card-title">
                  <h5>Security</h5>
                </div>
                <div class="form-group">
                  <label for="Security[salt]">Salt</label>
                  <input type="password" class="form-control info-field" id="Security[salt]" aria-describedby="Security[salt]Help" name="Security[salt]">
                  <small id="Security[salt]Help" class="form-text text-muted">The salt used to encrypt credentials. <b>WARNING! Changing the Salt will invalidate all client-side stored API Keys</b></small>
                </div>
              </div>
              <br>
              <div class="card border-secondary">
                <div class="card-title">
                  <h5>Authentication</h5>
                </div>
                <div class="accordion" id="ldapConfigAccordian">
                  <div class="accordion-item">
                    <h2 class="accordion-header" id="ldapConfigAccordianHeading">
                      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#ldapConfig" aria-expanded="true" aria-controls="ldapConfig">
                      LDAP Configuration
                      </button>
                    </h2>
                    <div id="ldapConfig" class="accordion-collapse collapse" aria-labelledby="ldapConfigAccordianHeading" data-bs-parent="#ldapConfigAccordian">
                      <div class="accordion-body">
                        <div class="card border-secondary">
                          <div class="card-title">
                            <h5>LDAP Configuration</h5>
                          </div>
                          <div class="row">
                            <div class="col-lg-6 col-12">
                              <div class="form-group">
                                  <label for="LDAP[ldap_server]">LDAP Server</label>
                                  <input type="text" class="form-control info-field" id="LDAP[ldap_server]" name="LDAP[ldap_server]" placeholder="ldap://fqdn:389">
                              </div>
                              <div class="form-group">
                                  <label for="LDAP[service_dn]">LDAP Bind Username</label>
                                  <input type="text" class="form-control info-field" id="LDAP[service_dn]" name="LDAP[service_dn]" placeholder="cn=read-only-admin,dc=example,dc=com">
                              </div>
                              <div class="form-group">
                                  <label for="LDAP[service_dn]">LDAP Bind Password</label>
                                  <input type="password" class="form-control info-field" id="LDAP[service_password]" name="LDAP[service_password]" placeholder="*********">
                              </div>
                            </div>
                            <div class="col-lg-6 col-12">
                              <div class="form-group">
                                <label for="LDAP[user_dn]">User DN</label>
                                <input type="text" class="form-control info-field" id="LDAP[user_dn]" name="LDAP[user_dn]" placeholder="dc=example,dc=com">
                              </div>
                              <div class="form-group">
                                <label for="LDAP[base_dn]">Base DN</label>
                                <input type="text" class="form-control info-field" id="LDAP[base_dn]" name="LDAP[base_dn]" placeholder="dc=example,dc=com">
                              </div>
                              <br>
                              <div class="form-group">
                                <div class="form-check form-switch">
                                  <label class="form-check-label" for="LDAP[enabled]">Enable LDAP</label>
                                  <input class="form-check-input info-field" type="checkbox" id="LDAP[enabled]" name="LDAP[enabled]">
                                </div>
                              </div>
                              <div class="form-group">
                                <div class="form-check form-switch">
                                  <label class="form-check-label" for="LDAP[AutoCreateUsers]">Auto-Create Users</label>
                                  <input class="form-check-input info-field" type="checkbox" id="LDAP[AutoCreateUsers]" name="LDAP[AutoCreateUsers]">
                                </div>
                              </div>
                            </div>
                          </div>
                          <hr>
                          <div class="row">
                            <h4>User Attribute Mapping</h4>
                            <p>Used for mapping LDAP Attributes to the account information</p>
                            <div class="col-md-6 col-12">
                              <div class="form-group">
                                  <label for="LDAP[attributes][Username]">Username Attribute</label>
                                  <input type="text" class="form-control info-field" id="LDAP[attributes][Username]" name="LDAP[attributes][Username]" placeholder="sAMAccountName">
                              </div>
                              <div class="form-group">
                                  <label for="LDAP[attributes][FirstName]">First Name Attribute</label>
                                  <input type="text" class="form-control info-field" id="LDAP[attributes][FirstName]" name="LDAP[attributes][FirstName]" placeholder="givenName">
                              </div>
                              <div class="form-group">
                                  <label for="LDAP[attributes][LastName]">Last Name Attribute</label>
                                  <input type="text" class="form-control info-field" id="LDAP[attributes][LastName]" name="LDAP[attributes][LastName]" placeholder="sn">
                              </div>
                            </div>
                            <div class="col-md-6 col-12">
                              <div class="form-group">
                                  <label for="LDAP[attributes][Email]">Email Attribute</label>
                                  <input class="form-control info-field" id="LDAP[attributes][Email]" name="LDAP[attributes][Email]" placeholder="mail">
                              </div>
                              <div class="form-group">
                                  <label for="LDAP[attributes][Groups]">Groups Attribute</label>
                                  <input class="form-control info-field" id="LDAP[attributes][Groups]" name="LDAP[attributes][Groups]" placeholder="memberOf">
                              </div>
                              <div class="form-group">
                                  <label for="LDAP[attributes][DN]">Distinguished Name Attribute</label>
                                  <input class="form-control info-field" id="LDAP[attributes][DN]" name="LDAP[attributes][DN]" placeholder="distinguishedName">
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <br>
                <div class="accordion" id="samlConfigAccordian">
                  <div class="accordion-item">
                    <h2 class="accordion-header" id="samlConfigAccordianHeading">
                      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#samlConfig" aria-expanded="true" aria-controls="samlConfig">
                      SAML Configuration
                      </button>
                    </h2>
                    <div id="samlConfig" class="accordion-collapse collapse" aria-labelledby="samlConfigAccordianHeading" data-bs-parent="#samlConfigAccordian">
                      <div class="accordion-body">
                        <div class="card border-secondary">
                          <div class="card-title">
                            <h5>SAML Configuration</h5>
                          </div>
                          <div class="row">
                            <div class="col-lg-6 col-12">
                              <h4>Service Provider (SP)</h4>
                              <div class="form-group">
                                  <label for="SAML[sp][entityId]">Entity ID</label>
                                  <input type="text" class="form-control info-field" id="SAML[sp][entityId]" name="SAML[sp][entityId]">
                              </div>
                              <div class="form-group">
                                  <label for="SAML[sp][assertionConsumerService][url]">Assertion Consumer Service URL</label>
                                  <input type="text" class="form-control info-field" id="SAML[sp][assertionConsumerService][url]" name="SAML[sp][assertionConsumerService][url]">
                              </div>
                              <div class="form-group">
                                  <label for="SAML[sp][singleLogoutService][url]">Single Logout Service URL</label>
                                  <input type="text" class="form-control info-field" id="SAML[sp][singleLogoutService][url]" name="SAML[sp][singleLogoutService][url]">
                              </div>
                              <div class="form-group">
                                  <label for="SAML[sp][x509cert]">X.509 Certificate</label>
                                  <textarea class="form-control info-field" id="SAML[sp][x509cert]" name="SAML[sp][x509cert]"></textarea>
                              </div>
                              <div class="form-group">
                                  <label for="SAML[sp][privateKey]">Private Key</label>
                                  <textarea class="form-control info-field" id="SAML[sp][privateKey]" name="SAML[sp][privateKey]"></textarea>
                              </div>
                            </div>
                            <div class="col-lg-6 col-12">
                              <h4>Identity Provider (IdP)</h4>
                              <div class="form-group">
                                  <label for="SAML[idp][entityId]">Entity ID</label>
                                  <input type="text" class="form-control info-field" id="SAML[idp][entityId]" name="SAML[idp][entityId]">
                              </div>
                              <div class="form-group">
                                  <label for="SAML[idp][singleSignOnService][url]">Single Sign-On Service URL</label>
                                  <input type="text" class="form-control info-field" id="SAML[idp][singleSignOnService][url]" name="SAML[idp][singleSignOnService][url]">
                              </div>
                              <div class="form-group">
                                  <label for="SAML[idp][singleLogoutService][url]">Single Logout Service URL</label>
                                  <input type="text" class="form-control info-field" id="SAML[idp][singleLogoutService][url]" name="SAML[idp][singleLogoutService][url]">
                              </div>
                              <div class="form-group">
                                  <label for="SAML[idp][x509cert]">X.509 Certificate</label>
                                  <textarea class="form-control info-field" id="SAML[idp][x509cert]" name="SAML[idp][x509cert]"></textarea>
                              </div>
                              <br>
                              <div class="form-group">
                                <div class="form-check form-switch">
                                  <label class="form-check-label" for="SAML[enabled]">Enable SAML</label>
                                  <input class="form-check-input info-field" type="checkbox" id="SAML[enabled]" name="SAML[enabled]">
                                </div>
                              </div>
                              <div class="form-group">
                                <div class="form-check form-switch">
                                  <label class="form-check-label" for="SAML[AutoCreateUsers]">Auto-Create Users</label>
                                  <input class="form-check-input info-field" type="checkbox" id="SAML[AutoCreateUsers]" name="SAML[AutoCreateUsers]">
                                </div>
                              </div>
                              <div class="form-group">
                                <div class="form-check form-switch">
                                  <label class="form-check-label" for="SAML[strict]">Use Strict Mode</label>
                                  <input class="form-check-input info-field" type="checkbox" id="SAML[strict]" name="SAML[strict]">
                                </div>
                              </div>
                              <div class="form-group">
                                <div class="form-check form-switch">
                                  <label class="form-check-label" for="SAML[debug]">Use Debug Mode</label>
                                  <input class="form-check-input info-field" type="checkbox" id="SAML[debug]" name="SAML[debug]">
                                </div>
                              </div>
                            </div>
                          </div>
                          <hr>
                          <div class="row">
                            <h4>User Attribute Mapping</h4>
                            <p>Used for mapping SAML Attributes to the account information</p>
                            <div class="col-md-6 col-12">
                              <div class="form-group">
                                  <label for="SAML[attributes][Username]">Username Attribute</label>
                                  <input type="text" class="form-control info-field" id="SAML[attributes][Username]" name="SAML[attributes][Username]">
                              </div>
                              <div class="form-group">
                                  <label for="SAML[attributes][FirstName]">First Name Attribute</label>
                                  <input type="text" class="form-control info-field" id="SAML[attributes][FirstName]" name="SAML[attributes][FirstName]">
                              </div>
                              <div class="form-group">
                                  <label for="SAML[attributes][LastName]">Last Name Attribute</label>
                                  <input type="text" class="form-control info-field" id="SAML[attributes][LastName]" name="SAML[attributes][LastName]">
                              </div>
                            </div>
                            <div class="col-md-6 col-12">
                              <div class="form-group">
                                  <label for="SAML[attributes][Email]">Email Attribute</label>
                                  <input class="form-control info-field" id="SAML[attributes][Email]" name="SAML[attributes][Email]">
                              </div>
                              <div class="form-group">
                                  <label for="SAML[attributes][Groups]">Groups Attribute</label>
                                  <input class="form-control info-field" id="SAML[attributes][Groups]" name="SAML[attributes][Groups]">
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="tab-pane fade" id="customisation" role="tabpanel" aria-labelledby="customisation-tab">
          <form id="customisationForm">
            <div class="my-4">
              <h5 class="mb-0 mt-5">Customisation</h5>
              <p>Use the fields below to customize the style and logos for '.$ib->config->get('Styling')['websiteTitle'].'.</p>
              <div class="card border-secondary">
                <div class="card-title">
                  <h5>General</h5>
                </div>
                <div class="form-group row">
                  <div class="col-lg-6 col-12">
                    <label for="Styling[logo-sm][Image]">Logo Image (Small)</label>
                    <input type="text" class="form-control info-field" id="Styling[logo-sm][Image]" aria-describedby="Styling[logo-sm][Image]Help" name="Styling[logo-sm][Image]">
                    <small id="Styling[logo-sm][Image]Help" class="form-text text-muted">The path of the small logo to be used in the top-left navbar.</small>
                  </div>
                  <div class="col-lg-6 col-12">
                    <label for="Styling[logo-sm][CSS]">Logo CSS (Small)</label>
                    <input type="text" class="form-control info-field" id="Styling[logo-sm][CSS]" aria-describedby="Styling[logo-sm][CSS]Help" name="Styling[logo-sm][CSS]">
                    <small id="Styling[logo-sm][CSS]Help" class="form-text text-muted">Custom CSS for the small logo.</small>
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-lg-6 col-12">
                    <label for="Styling[logo-lg][Image]">Logo Image (Large)</label>
                    <input type="text" class="form-control info-field" id="Styling[logo-lg][Image]" aria-describedby="Styling[logo-lg][Image]Help" name="Styling[logo-lg][Image]">
                    <small id="Styling[logo-lg][Image]Help" class="form-text text-muted">The path of the large logo to be used in the top-left navbar.</small>
                  </div>
                  <div class="col-lg-6 col-12">
                    <label for="Styling[logo-lg][CSS]">Logo CSS (Large)</label>
                    <input type="text" class="form-control info-field" id="Styling[logo-lg][CSS]" aria-describedby="Styling[logo-lg][CSS]Help" name="Styling[logo-lg][CSS]">
                    <small id="Styling[logo-lg][CSS]Help" class="form-text text-muted">Custom CSS for the large logo.</small>
                  </div>
                </div>
                <div class="form-group row">
                  <div class="col-lg-6 col-12">
                    <label for="Styling[favicon][Image]">Favicon</label>
                    <input type="text" class="form-control info-field" id="Styling[favicon][Image]" aria-describedby="Styling[favicon][Image]Help" name="Styling[favicon][Image]">
                    <small id="Styling[favicon][Image]Help" class="form-text text-muted">The path of the favicon image.</small>
                  </div>
                  <div class="col-lg-6 col-12">
                    <label for="Styling[websiteTitle]">Website Title</label>
                    <input type="text" class="form-control info-field" id="Styling[websiteTitle]" aria-describedby="Styling[websiteTitle]Help" name="Styling[websiteTitle]">
                    <small id="Styling[websiteTitle]Help" class="form-text text-muted">The website title.</small>
                  </div>
                </div>
              </div>
              <br>
              <div class="card border-secondary">
                <div class="card-title">
                  <h5>Content</h5>
                </div>
                <div class="form-group row">
                  <div class="col-lg-6 col-12">
                    <label for="Styling[html][homepage]">Homepage HTML</label>
                    <textarea class="form-control info-field" id="Styling[html][homepage]" name="Styling[html][homepage]"></textarea>
                    <small id="Styling[html][homepage]Help" class="form-text text-muted">Custom HTML for the homepage.</small>
                  </div>
                  <div class="col-lg-6 col-12">
                    <label for="Styling[html][about]">About HTML</label>
                    <textarea class="form-control info-field" id="Styling[html][about]" name="Styling[html][about]"></textarea>
                    <small id="Styling[html][about]Help" class="form-text text-muted">Custom HTML for the about page in the information modal.</small>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="tab-pane fade" id="plugins" role="tabpanel" aria-labelledby="plugins-tab">
          <div class="my-4">
            <h5 class="mb-0 mt-5">Plugins</h5>
            <p>Use the following to configure Plugins installed on '.$ib->config->get('Styling')['websiteTitle'].'.</p>
          </div>
          <div class="card">
            <div class="container">
              <table data-url="/api/plugins/available"
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
                data-buttons="pluginsButtons"
                class="table table-striped" id="pluginsTable">

                <thead>
                  <tr>
                    <th data-field="state" data-checkbox="true"></th>
                    <th data-field="name" data-sortable="true">Plugin Name</th>
                    <th data-field="author" data-sortable="true">Author</th>
                    <th data-field="category" data-sortable="true">Category</th>
                    <th data-field="version" data-sortable="true">Version</th>
                    <th data-field="link" data-sortable="true">URL</th>
                    <th data-field="status" data-sortable="true">Status</th>
                    <th data-field="source" data-sortable="true">Source</th>
                    <th data-formatter="pluginActionFormatter" data-events="pluginActionEvents">Actions</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
      <br>
      <button class="btn btn-success float-end ms-1" id="submitConfig">Save Configuration</button>&nbsp;
      <button class="btn btn-primary float-end" onclick="location.reload();">Discard Changes</button>
	  </div>
  </div>
</div>

<!-- Plugin Settings Modal -->
<div class="modal fade" id="pluginSettingsModal" tabindex="-1" role="dialog" aria-labelledby="pluginSettingsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pluginSettingsModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"></span>
        </button>
      </div>
      <div class="modal-body" id="pluginSettingsModalBody">
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" id="editPluginSaveButton">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Online Plugins Modal -->
<div class="modal fade" id="onlinePluginsModal" tabindex="-1" role="dialog" aria-labelledby="onlinePluginsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="onlinePluginsModalLabel">Github Repository URLs</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"></span>
        </button>
      </div>
      <div class="modal-body" id="onlinePluginsModalBody">
        <div class="container mt-2">
          <div class="input-group mb-3">
            <input id="urlInput" type="text" class="form-control" placeholder="https://github.com/TehMuffinMoo/example" aria-label="Github Repository URL">
            <div class="input-group-append">
              <button class="btn btn-outline-success" type="button" id="addUrl">Add URL</button>
            </div>
          </div>
          <ul id="urlList" class="list-group mt-3 urlList"></ul>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" id="editOnlinePluginsSaveButton">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
  function getConfig() {
    $.getJSON("/api/config", function(data) {
      let config = data.data;

      const updateConfigValues = (config, parentKey = "") => {
        for (const section in config) {
          for (const key in config[section]) {
            const value = config[section][key];
            const fullKey = parentKey ? `${parentKey}[${section}][${key}]` : `${section}[${key}]`;
            const selector = `#${$.escapeSelector(fullKey)}`;
            if (typeof value === "object" && !Array.isArray(value) && value !== null) {
              updateConfigValues({ [fullKey]: value });
            } else if (typeof value === "boolean") {
              $(selector).prop("checked", value);
            } else {
              $(selector).val(value);
            }
          }
        }
      };
      updateConfigValues(config);
    });
  }

  getConfig();

  $(".info-field").change(function(elem) {
    toast("Configuration","",$(elem.target.previousElementSibling).text()+" has changed.<br><small>Save configuration to apply changes.</small>","warning");
    $(this).addClass("changed");
  });

  $("#submitConfig").click(function(event) {
    event.preventDefault();
    var formData = $("#configurationForm .changed,#customisationForm .changed").serializeArray();
    
    // Include unchecked checkboxes in the formData
    $("#configurationForm input.changed[type=checkbox]").each(function() {
        formData.push({ name: this.name, value: this.checked ? true : false });
    });

    var configData = {};
    formData.forEach(function(item) { 
        var keys = item.name.split("[").map(function(key) {
            return key.replace("]","");
        });
        var temp = configData;
        keys.forEach(function(key, index) {
            if (index === keys.length - 1) {
                temp[key] = item.value;
            } else {
                temp[key] = temp[key] || {};
                temp = temp[key];
            }
        });
    });

    queryAPI("PATCH","/api/config",configData).done(function(data) {
        if (data["result"] == "Success") {
            toast("Success","","Successfully saved configuration","success");
        } else if (data["result"] == "Error") {
            toast("Error","","Failed to save configuration","danger");
        } else {
            toast("API Error","","Failed to save configuration","danger","30000");
        }
    });
  });

  // Function to switch tabs
  function switchTab(tabId) {
    $(`.nav-tabs a[href="` + tabId + `"]`).tab("show");
  }
  // Listener for tab changes
  $("#myTab .nav-link").on("click", function(elem) {
    elem.preventDefault();
    switchTab($(elem.target).attr("href"));
  });


  // PLUGINS //
  function buildPluginSettingsModal(row){
    try {
      queryAPI("GET",row.api).done(function(settingsResponse) {
        $("#pluginSettingsModalBody").html(buildFormGroup(settingsResponse.data));
        initPasswordToggle();
        $("#pluginSettingsModalLabel").text("Plugin Settings: "+row.name);
        $(".info-field").change(function(elem) {
          toast("Configuration","",$(elem.target).data("label")+" has changed.<br><small>Save configuration to apply changes.</small>","warning");
          $(this).addClass("changed");
        });
        $("#editPluginSaveButton").on("click",function(elem) {
          editPluginSubmit(row);
        });
        try {
          queryAPI("GET","/api/config/plugins/"+row.name).done(function(configResponse) {
            let data = configResponse.data;
            for (const key in data) {
              if (data.hasOwnProperty(key)) {
                  const value = data[key];
                  $(`#pluginSettingsModal [name="${key}"]`).val(value);
              }
            }
          }).fail(function(xhr) {
            logConsole("Error",xhr,"error");
          });
        } catch(e) {
          logConsole("Error",e,"error");
        }
      }).fail(function(xhr) {
        logConsole("Error",xhr,"error");
      });;
    } catch(e) {
      logConsole("Error",e,"error");
    }
  }

  function editPluginSubmit(row) {
    // Serialize the form data into an array
    var serializedArray = $("#pluginSettingsModal .info-field.changed").serializeArray();

    // Convert the array into an object
    var formData = {};
    serializedArray.forEach(function(item) {
        formData[item.name] = item.value;
    });

    queryAPI("PATCH","/api/config/plugins/"+row.name,formData).done(function(data) {
      if (data["result"] == "Success") {
          toast(data["result"],"",data["message"],"success");
          $("#pluginSettingsModal").modal("hide");
      } else if (data["result"] == "Error") {
          toast(data["result"],"",data["message"],"danger");
      } else {
          toast("API Error","","Failed to save configuration","danger","30000");
      }
    });
  }

  function pluginActionFormatter(value, row, index) {
    var buttons = [];
    if (row.settings) {
      buttons.push(`<a class="edit" title="Edit"><i class="fa fa-pencil"></i></a>&nbsp;`);
    }
    if (row.status == "Available") {
      buttons.push(`<a class="install" title="Install"><i class="fa-solid fa-download"></i></a>&nbsp;`);
    } else if (row.status == "Installed") {
      buttons.push(`<a class="uninstall" title="Uninstall"><i class="fa-solid fa-trash-can"></i></a>&nbsp;`);
    }
    return buttons.join("");
  }

  window.pluginActionEvents = {
    "click .edit": function (e, value, row, index) {
      buildPluginSettingsModal(row);
      $("#pluginSettingsModal").modal("show");
    },
    "click .install": function (e, value, row, index) {
      installPlugin(row);
    },
    "click .uninstall": function (e, value, row, index) {
      uninstallPlugin(row);
    }
  }

  function pluginsButtons() {
    return {
      btnEditPluginURLs: {
        text: "Edit Plugin URL(s)",
        icon: "bi bi-pencil-square",
        event: function() {
          $("#urlList").html("");
          populatePluginRepositories();
          $("#onlinePluginsModal").modal("show");
        },
        attributes: {
          title: "Edit Plugin URL(s)",
          style: "background-color:#4bbe40;border-color:#4bbe40;"
        }
	    }
    }
  }

  function populatePluginRepositories() {
    queryAPI("GET","/api/plugins/repositories").done(function(data) {
      if (data["result"] == "Success") {
        // Loop through the URLs and create list items
        data["data"].forEach(url => {
            // Create a new li element
            const listItem = document.createElement("li");
            listItem.className = "list-group-item"; // Add Bootstrap class for styling

            // Create an a element
            const link = document.createElement("a");
            link.href = url;
            link.textContent = url;

            // Create an i element
            const trash = document.createElement("i");
            trash.classList = "fa fa-trash removeUrl";

            // Append the link to the list item
            listItem.appendChild(link);
            // Append the icon to the list item
            listItem.appendChild(trash);

            // Append the list item to the ul
            urlList.appendChild(listItem);
        });

        $(".removeUrl").click(function(elem) {
          $(elem.target).parent().remove();
        });
      } else if (data["result"] == "Error") {
        toast(data["result"],"",data["message"],"danger","30000");
      } else {
        toast("Error", "", "Failed to query list of repositories", "danger");
      }
    }).fail(function() {
        toast("Error", "", "Failed to query list of repositories", "danger");
    });
  }

  function getAllRepositoryUrls() {
    const urlList = document.getElementById("urlList");
    const listItems = urlList.getElementsByTagName("li");
    const urls = [];
    for (let i = 0; i < listItems.length; i++) {
      const listItem = listItems[i];
      const link = listItem.getElementsByTagName("a")[0];
      if (link) {
        urls.push(link.href);
      } else {
        urls.push(listItem.textContent.trim());
      }
    }
    return urls;
  }

  $("#addUrl").click(function() {
    var url = $("#urlInput").val();
    if (url) {
        $("#urlList").append(`<li class="list-group-item">` + url + `</li>`);
        $("#urlInput").val("");
    }
  });

  $("#editOnlinePluginsSaveButton").on("click",function(elem) {
    var list = getAllRepositoryUrls();
    queryAPI("POST","/api/plugins/repositories",{list: list}).done(function(data) {
      if (data["result"] == "Success") {
        toast(data["result"],"",data["message"],"success");
        $("#onlinePluginsModal").modal("hide");
      } else if (data["result"] == "Error") {
        toast(data["result"],"",data["message"],"danger");
      } else {
        toast("API Error","","Failed to save repository configuration","danger","30000");
      }
    }).fail(function(xhr) {
      toast("API Error","","Failed to save repository configuration","danger","30000");
    });
  })

  function installPlugin(row){
    toast("Installing","","Installing "+row["name"]+"...","info");
    try {
      queryAPI("POST","/api/plugins/install",row).done(function(data) {
        if (data["result"] == "Success") {
          toast(data["result"],"",data["message"],"success");
          $("#pluginsTable").bootstrapTable("refresh");
        } else if (data["result"] == "Error") {
          toast(data["result"],"",data["message"],"danger");
        } else {
          toast("API Error","","Failed to install plugin","danger","30000");
        }
      }).fail(function(xhr) {
        toast("API Error","","Failed to install plugin","danger","30000");
        logConsole("Error",xhr,"error");
      });;
    } catch(e) {
      toast("API Error","","Failed to install plugin","danger","30000");
      logConsole("Error",e,"error");
    }
  }

  function uninstallPlugin(row){
    console.log(row);
  }

  $("#pluginsTable").bootstrapTable();
</script>
';