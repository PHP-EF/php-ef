<?php
  require_once(__DIR__."/../../inc/inc.php");
  if ($ib->rbac->checkAccess("ADMIN-CONFIG") == false) {
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
      <form id="configurationForm">
        <div class="my-4">
          <h5 class="mb-0 mt-5">Configuration</h5>
          <p>Use the fields below to modify the configuration for the Infoblox SA Tools Portal.</p>
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
              <label for="System[CURL-TimeoutConnect]">CURL Timeout on Connect</label>
	            <input type="text" class="form-control info-field" id="System[CURL-TimeoutConnect]" aria-describedby="System[CURL-TimeoutConnect]Help" name="System[CURL-TimeoutConnect]">
              <small id="System[CURL-TimeoutConnect]Help" class="form-text text-muted">Specify the timeout used for CURL requests on connect. (Shouldn"t need to be increased)</small>
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
              <small id="Security[salt]Help" class="form-text text-muted">The salt used to encrypt credentials.</small>
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
              <div class="col-md-6">
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
              <div class="col-md-6">
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
              <div class="col-md-6">
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
	    </form>
      <br>
      <button class="btn btn-success float-end ms-1" id="submitConfig2">Save Configuration</button>&nbsp;
      <button class="btn btn-primary float-end" onclick="location.reload();">Discard Changes</button>
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

$("#submitConfig2").click(function(event) {
    event.preventDefault();
    var formData = $("#configurationForm .changed").serializeArray();
    
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

  $("#submitConfig").click(function(event) {
    event.preventDefault();
    var kvpairs = {};
    var inspectForm = document.querySelector("#configurationForm").getElementsByClassName("changed");
    for ( var i = 0; i < inspectForm.length; i++ ) {
      var e = inspectForm[i];
      if (inspectForm[i]["type"] == "checkbox") {
        kvpairs[e.name] = encodeURIComponent(e.checked);
      } else {
        kvpairs[e.name] = encodeURIComponent(e.value);
      }
    }
    queryAPI("PATCH","/api/config",kvpairs).done(function(data) {
      if (data["result"] == "Success") {
        toast("Success","","Successfully saved configuration","success");
      } else if (data["result"] == "Error") {
        toast("Error","","Failed to save configuration","danger");
      } else {
        toast("API Error","","Failed to save configuration","danger","30000");
      }
    });
  });
</script>
';