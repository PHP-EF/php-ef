<?php
  require_once(__DIR__.'/../../inc/inc.php');
  if ($ib->rbac->checkAccess("ADMIN-CONFIG") == false) {
    die();
  }

?>

<style>
.card {
  padding: 10px;
}
</style>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-12 col-xl-12 mx-auto">
      <h2 class="h3 mb-4 page-title">Configuration</h2>
      <form id="configurationForm">
        <div class="my-4">
          <h5 class="mb-0 mt-5">Configuration</h5>
          <p>Use the fields below to modify the configuration for the Infoblox SA Tools Portal.</p>
          <div class="card border-secondary">
            <div class="card-title">
              <h5>System</h5>
            </div>
            <div class="form-group">
              <label for="systemLogFileName">Log File Name</label>
              <input type="text" class="form-control info-field" id="systemLogFileName" aria-describedby="systemLogFileNameHelp" name="systemLogFileName">
              <small id="systemLogFileNameHelp" class="form-text text-muted">The name of the log file <b>without</b> the file extension.</small>
	          </div>
            <div class="form-group">
              <label for="systemLogDirectory">Log Directory</label>
              <input type="text" class="form-control info-field" id="systemLogDirectory" aria-describedby="systemLogDirectoryHelp" name="systemLogDirectory">
              <small id="systemLogDirectoryHelp" class="form-text text-muted">The full path of the log directory including the trailing slash.</small>
            </div>
            <div class="form-group">
              <label for="systemLogLevel">Log Level</label>
	            <select type="select" class="form-select info-field" id="systemLogLevel" aria-describedby="systemLogLevelHelp" name="systemLogLevel">
                <option>Debug</option>
                <option>Info</option>
                <option>Warning</option>
              </select>
              <small id="systemLogLevelHelp" class="form-text text-muted">Specify which log level you would like to use. Enabling <b>Debug</b> logs will generate lots of data.</small>
	          </div>
            <div class="form-group">
              <label for="systemLogRetention">Log Retention</label>
              <input type="text" class="form-control info-field" id="systemLogRetention" aria-describedby="systemLogRetentionHelp" name="systemLogRetention">
              <small id="systemLogRetentionHelp" class="form-text text-muted">How many days to keep system logs before they are purged.</small>
            </div>
            <div class="form-group">
              <label for="systemCURLTimeout">CURL Timeout</label>
              <input type="text" class="form-control info-field" id="systemCURLTimeout" aria-describedby="systemCURLTimeoutHelp" name="systemCURLTimeout">
              <small id="systemCURLTimeoutHelp" class="form-text text-muted">Specify the timeout used for CURL requests. (Can be increased if long running outbound API calls time out)</small>
	          </div>
            <div class="form-group">
              <label for="systemCURLTimeoutConnect">CURL Timeout on Connect</label>
	            <input type="text" class="form-control info-field" id="systemCURLTimeoutConnect" aria-describedby="systemCURLTimeoutConnectHelp" name="systemCURLTimeoutConnect">
              <small id="systemCURLTimeoutConnectHelp" class="form-text text-muted">Specify the timeout used for CURL requests on connect. (Shouldn't need to be increased)</small>
	          </div>
	        </div>
          <br>
          <div class="card border-secondary">
            <div class="card-title">
              <h5>Security</h5>
            </div>
            <div class="form-group">
              <label for="securitySalt">Salt</label>
              <input type="password" class="form-control info-field" id="securitySalt" aria-describedby="securitySaltHelp" name="securitySalt">
              <small id="securitySaltHelp" class="form-text text-muted">The salt used to encrypt credentials.</small>
	          </div>
          </div>
          <br>
          <div class="card border-secondary">
            <div class="card-title">
              <h5>SAML Configuration</h5>
            </div>
            <div class="row">
              <div class="col-md-6">
                <h4>Service Provider (SP)</h4>
                <div class="form-group">
                    <label for="spEntityId">Entity ID</label>
                    <input type="text" class="form-control info-field" id="spEntityId" name="spEntityId">
                </div>
                <div class="form-group">
                    <label for="spAcsUrl">Assertion Consumer Service URL</label>
                    <input type="text" class="form-control info-field" id="spAcsUrl" name="spAcsUrl">
                </div>
                <div class="form-group">
                    <label for="spSloUrl">Single Logout Service URL</label>
                    <input type="text" class="form-control info-field" id="spSloUrl" name="spSloUrl">
                </div>
                <div class="form-group">
                    <label for="spX509Cert">X.509 Certificate</label>
                    <textarea class="form-control info-field" id="spX509Cert" name="spX509Cert"></textarea>
                </div>
                <div class="form-group">
                    <label for="spPrivateKey">Private Key</label>
                    <textarea class="form-control info-field" id="spPrivateKey" name="spPrivateKey"></textarea>
                </div>
              </div>
              <div class="col-md-6">
                <h4>Identity Provider (IdP)</h4>
                <div class="form-group">
                    <label for="idpEntityId">Entity ID</label>
                    <input type="text" class="form-control info-field" id="idpEntityId" name="idpEntityId">
                </div>
                <div class="form-group">
                    <label for="idpSsoUrl">Single Sign-On Service URL</label>
                    <input type="text" class="form-control info-field" id="idpSsoUrl" name="idpSsoUrl">
                </div>
                <div class="form-group">
                    <label for="idpSloUrl">Single Logout Service URL</label>
                    <input type="text" class="form-control info-field" id="idpSloUrl" name="idpSloUrl">
                </div>
                <div class="form-group">
                    <label for="idpX509Cert">X.509 Certificate</label>
                    <textarea class="form-control info-field" id="idpX509Cert" name="idpX509Cert"></textarea>
                </div>
                <br>
                <div class="form-group">
                  <div class="form-check form-switch">
                    <input class="form-check-input info-field" type="checkbox" id="samlEnabled" name="samlEnabled">
                    <label class="form-check-label" for="samlEnabled">Enable SAML</label>
                  </div>
                </div>
                <div class="form-group">
                  <div class="form-check form-switch">
                    <input class="form-check-input info-field" type="checkbox" id="samlAutoCreateUsers" name="samlAutoCreateUsers">
                    <label class="form-check-label" for="samlAutoCreateUsers">Auto-Create Users</label>
                  </div>
                </div>
                <div class="form-group">
                  <div class="form-check form-switch">
                    <input class="form-check-input info-field" type="checkbox" id="samlStrict" name="samlStrict">
                    <label class="form-check-label" for="samlStrict">Use Strict Mode</label>
                  </div>
                </div>
                <div class="form-group">
                  <div class="form-check form-switch">
                    <input class="form-check-input info-field" type="checkbox" id="samlDebug" name="samlDebug">
                    <label class="form-check-label" for="samlDebug">Use Debug Mode</label>
                  </div>
                </div>
              </div>
            </div>
            <hr>
            <div class="row">
              <h4>User Attribute Mapping</h4>
              <p>Used for mapping SAML Attributes to the account information</p>
              <div class="col-md-6">
                <div class="form-group">
                    <label for="attributeUsername">Username Attribute</label>
                    <input type="text" class="form-control info-field" id="attributeUsername" name="attributeUsername">
                </div>
                <div class="form-group">
                    <label for="attributeFirstName">First Name Attribute</label>
                    <input type="text" class="form-control info-field" id="attributeFirstName" name="attributeFirstName">
                </div>
                <div class="form-group">
                    <label for="attributeLastName">Last Name Attribute</label>
                    <input type="text" class="form-control info-field" id="attributeLastName" name="attributeLastName">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                    <label for="attributeEmail">Email Attribute</label>
                    <input class="form-control info-field" id="attributeEmail" name="attributeEmail">
                </div>
                <div class="form-group">
                    <label for="attributeGroups">Groups Attribute</label>
                    <input class="form-control info-field" id="attributeGroups" name="attributeGroups">
                </div>
              </div>
            </div>
          </div>
        </div>
	    </form>
      <br>
      <button class="btn btn-success float-end ms-1" id="submitConfig">Save Configuration</button>&nbsp;
      <button class="btn btn-primary float-end" onclick="location.reload();">Discard Changes</button>
	  </div>
  </div>
</div>

<script>

  function getConfig() {
    $.getJSON('/api?f=GetConfig', function(config) {
      $("#systemLogFileName").val(config.System.logfilename);
      $("#systemLogDirectory").val(config.System.logdirectory);
      $("#systemLogLevel").val(config.System.loglevel);
      $("#systemLogRetention").val(config.System.logretention);
      $("#systemCURLTimeout").val(config.System["CURL-Timeout"]);
      $("#systemCURLTimeoutConnect").val(config.System["CURL-ConnectTimeout"]);
      $("#securitySalt").val(config.Security.salt);
      $("#spEntityId").val(config.SAML.sp.entityId);
      $("#spAcsUrl").val(config.SAML.sp.assertionConsumerService.url);
      $("#spSloUrl").val(config.SAML.sp.singleLogoutService.url);
      $("#spX509Cert").val(config.SAML.sp.x509cert);
      $("#spPrivateKey").val(config.SAML.sp.privateKey);
      $("#idpEntityId").val(config.SAML.idp.entityId);
      $("#idpSsoUrl").val(config.SAML.idp.singleSignOnService.url);
      $("#idpSloUrl").val(config.SAML.idp.singleLogoutService.url);
      $("#idpX509Cert").text(config.SAML.idp.x509cert);
      $("#samlEnabled").prop('checked',config.SAML.enabled);
      $("#samlAutoCreateUsers").prop('checked',config.SAML.AutoCreateUsers);
      $("#samlStrict").prop('checked',config.SAML.strict);
      $("#samlDebug").prop('checked',config.SAML.debug);
      $("#attributeUsername").val(config.SAML.attributes.Username);
      $("#attributeFirstName").val(config.SAML.attributes.FirstName);
      $("#attributeLastName").val(config.SAML.attributes.LastName);
      $("#attributeEmail").val(config.SAML.attributes.Email);
      $("#attributeGroups").val(config.SAML.attributes.Groups);
    });
  }

  getConfig();

  $(".info-field").change(function(elem) {
    console.log(elem);
    console.log($(elem.target.previousElementSibling).text());
    toast("Configuration","",$(elem.target.previousElementSibling).text()+' has changed.<br><small>Save configuration to apply changes.</small>',"warning");
    $(this).addClass("changed");
  });

  $("#submitConfig").click(function(event) {
    event.preventDefault();
    var kvpairs = {};
    var inspectForm = document.querySelector('#configurationForm').getElementsByClassName("changed");
    for ( var i = 0; i < inspectForm.length; i++ ) {
      var e = inspectForm[i];
      if (inspectForm[i]['type'] == 'checkbox') {
        console.log('checkbox!');
        kvpairs[e.name] = encodeURIComponent(e.checked);
      } else {
        kvpairs[e.name] = encodeURIComponent(e.value);
      }
    }
    $.post( "/api?f=SetConfig", kvpairs).done(function( data, status ) {
      if (data != null) {
        toast("Success","","Successfully saved configuration","success");
      } else {
        toast("Error","","Failed to save configuration","danger");
      }
    }).fail(function( data, status ) {
        toast("API Error","","Failed to save configuration","danger","30000");
    })
  });
</script>
