<?php
  require_once(__DIR__.'/../../inc/inc.php');
  if (CheckAccess(null,"ADMIN-CONFIG") == false) {
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
              <label for="systemRBACFile">Role Based Access Control File Location</label>
              <input type="text" class="form-control info-field" id="systemRBACFile" aria-describedby="systemRBACFileHelp" name="systemRBACFile">
              <small id="systemRBACFileHelp" class="form-text text-muted">The full path of the RBAC .json file to be used for storing permissions.</small>
	          </div>
            <div class="form-group">
              <label for="systemRBACInfoFile">Role Based Access Control Information File Location</label>
              <input type="text" class="form-control info-field" id="systemRBACInfoFile" aria-describedby="systemRBACInfoFileHelp" name="systemRBACInfoFile">
              <small id="systemRBACInfoFileHelp" class="form-text text-muted">The full path of the RBAC .json file containing the list of available roles and matching descriptions.</small>
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
            <div class="form-group">
              <label for="securityAdminPW">Admin Password</label>
              <input type="password" class="form-control info-field" id="securityAdminPW" aria-describedby="securityAdminPWHelp" name="securityAdminPW">
              <small id="securityAdminPWHelp" class="form-text text-muted">The password for the <b>admin</b> account.</small>
	          </div>
          </div>
        </div>
	    </form>
      <br>
      <button class="btn btn-primary float-right" id="submitConfig">Submit</button>
	  </div>
  </div>
</div>

<script>

  function getConfig() { 
    $.getJSON('/api/?function=GetConfig', function(config) {
      $("#systemLogFileName").val(config.System.logfilename);
      $("#systemLogDirectory").val(config.System.logdirectory);
      $("#systemLogLevel").val(config.System.loglevel);
      $("#systemLogRetention").val(config.System.logretention);
      $("#systemCURLTimeout").val(config.System["CURL-Timeout"]);
      $("#systemCURLTimeoutConnect").val(config.System["CURL-ConnectTimeout"]);
      $("#systemRBACFile").val(config.System.rbacjson);
      $("#systemRBACInfoFile").val(config.System.rbacinfo);
      $("#securitySalt").val(config.Security.salt);
      $("#securityAdminPW").val(config.Security.AdminPassword);
    });
  }

  getConfig();
  
  $(".info-field").change(function() {
    $(this).addClass("changed");
  });

  $("#submitConfig").click(function(event) {
    event.preventDefault();
    var kvpairs = {};
    var inspectForm = document.querySelector('#configurationForm').getElementsByClassName("changed");
    for ( var i = 0; i < inspectForm.length; i++ ) {
      var e = inspectForm[i];
      kvpairs[e.name] = encodeURIComponent(e.value);
    }
    $.post( "/api?function=SetConfig", kvpairs).done(function( data, status ) {
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
