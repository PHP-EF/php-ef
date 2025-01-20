<?php
  if ($phpef->auth->checkAccess("ADMIN-CONFIG") == false) {
    $phpef->api->setAPIResponse('Error','Unauthorized',401);
    return false;
  }

return '

</style>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-12 col-xl-12 mx-auto">
      <h2 class="h3 mb-4 page-title">Configuration</h2>
      <ul class="nav nav-tabs" id="configTabs" role="tablist">
        <div class="d-lg-none w-100">
          <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle w-100" type="button" id="configTabsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              General
            </button>
            <ul class="dropdown-menu" aria-labelledby="configTabsDropdown" id="configTabsDropdownMenu">
              <li><a class="dropdown-item nav-link" href="#general" data-bs-toggle="tab">General</a></li>
              <li><a class="dropdown-item nav-link" href="#accesscontrol" data-bs-toggle="tab">Access Control</a></li>
              <li><a class="dropdown-item nav-link" href="#customisation" data-bs-toggle="tab">Customisation</a></li>
              <li><a class="dropdown-item nav-link" href="#plugins" data-bs-toggle="tab">Plugins</a></li>
              <li><a class="dropdown-item nav-link" href="#images" data-bs-toggle="tab">Images</a></li>
              <li><a class="dropdown-item nav-link" href="#dashboards" data-bs-toggle="tab">Dashboards</a></li>
            </ul>
          </div>
        </div>

        <li class="nav-item">
          <a class="nav-link d-none d-lg-flex active" id="general-tab" data-bs-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="true">General</a>
        </li>
        <li class="nav-item d-none d-lg-flex">
          <a class="nav-link" id="accesscontrol-tab" data-bs-toggle="tab" href="#accesscontrol" role="tab" aria-controls="accesscontrol" aria-selected="false">Access Control</a>
        </li>
        <li class="nav-item d-none d-lg-flex">
          <a class="nav-link" id="customisation-tab" data-bs-toggle="tab" href="#customisation" role="tab" aria-controls="customisation" aria-selected="false">Customisation</a>
        </li>
        <li class="nav-item d-none d-lg-flex">
          <a class="nav-link" id="plugins-tab" data-bs-toggle="tab" href="#plugins" role="tab" aria-controls="plugins" aria-selected="false">Plugins</a>
        </li>
        <li class="nav-item d-none d-lg-flex">
          <a class="nav-link" id="images-tab" data-bs-toggle="tab" href="#images" role="tab" aria-controls="images" aria-selected="false">Images</a>
        </li>
        <li class="nav-item d-none d-lg-flex">
          <a class="nav-link" id="dashboards-tab" data-bs-toggle="tab" href="#dashboards" role="tab" aria-controls="dashboards" aria-selected="false">Dashboards</a>
        </li>
      </ul>
      <div class="tab-content" id="configTabContent">
        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
          <div class="my-4">
            <div class="card card-rounded border-secondary p-3">
              <div class="general-content">
                <form id="generalForm"></form>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="accesscontrol" role="tabpanel" aria-labelledby="accesscontrol-tab">
          <div class="my-4">
            <div class="card card-rounded border-secondary p-3">
              <div class="accesscontrol-content">
                <form id="accesscontrolForm"></form>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="customisation" role="tabpanel" aria-labelledby="customisation-tab">
          <div class="my-4">
            <div class="card card-rounded border-secondary p-3">
              <div class="customisation-content">
                <form id="customisationForm"></form>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="plugins" role="tabpanel" aria-labelledby="plugins-tab">
          <div class="my-4">
            <div class="card card-rounded border-secondary p-3">
              <div class="plugins-content">
                <form id="pluginsForm"></form>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="images" role="tabpanel" aria-labelledby="images-tab">
          <div class="my-4">
            <h5 class="mb-0 mt-5">Images</h5>
            <p>Use the following to configure Custom Images on '.$phpef->config->get('Styling')['websiteTitle'].'.</p>
            <div class="image-gallery dropzone" id="imageGallery"></div>
          </div>
        </div>
        <div class="tab-pane fade" id="dashboards" role="tabpanel" aria-labelledby="dashboards-tab">
          <div class="my-4">
            <div class="card card-rounded border-secondary p-3">
              <div class="dashboards-content">
                <form id="dashboardsForm"></form>
              </div>
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

<!-- Settings Modal -->
<div class="modal fade" id="SettingsModal" tabindex="-1" role="dialog" aria-labelledby="SettingsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xxl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="SettingsModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"></span>
        </button>
        <input id="modalItemID" hidden></input>
      </div>
      <div class="modal-body" id="SettingsModalBody">
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" id="SettingsModalSaveBtn">Save</button>
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
            <input id="urlInput" type="text" class="form-control" placeholder="https://github.com/php-ef/plugin-example" aria-label="Github Repository URL">
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

<!-- All of these modals should be refactored to Settings Modal -->

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editUserModalLabel">User Information</h5>
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
        <p>Enable or Disable the following groups to provide granular control to specific areas of '.$phpef->config->get('Styling')['websiteTitle'].'.</p>
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

<!-- Edit Group Modal -->
<div class="modal fade" id="groupEditModal" tabindex="-1" role="dialog" aria-labelledby="groupEditModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="groupEditModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"></span>
        </button>
      </div>
      <div class="modal-body" id="editModelBody">
        <h4>Group Information</h4>
	      <div class="form-group">
          <input type="text" class="form-control" id="editGroupID" aria-describedby="editGroupIDHelp" hidden>
          <div class="input-group mb-1">
            <input type="text" class="form-control" id="editGroupDescription" aria-describedby="editGroupDescriptionHelp">
            <button class="btn btn-primary" id="editGroupDescriptionSaveButton">Save</button>
	        </div>
          <small id="editGroupDescriptionHelp" class="form-text text-muted">The group description.</small>
	      </div>
	      <hr>
        <h4>Group Roles</h4>
        <p>Enable or Disable the following roles to provide granular control to specific areas of '.$phpef->config->get('Styling')['websiteTitle'].'.</p>
	      <div class="list-group mb-5 shadow" id="modalListRoles"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- New Item Modal -->
<div class="modal fade" id="newItemModal" tabindex="-1" role="dialog" aria-labelledby="newItemModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newItemModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"></span>
        </button>
      </div>
      <div class="modal-body" id="newItemModalBody">
        <div id="modal-body-heading"></div>
        <form>
          <div class="form-group">
            <label id="newItemNameLabel" for="newItemName"></label>
            <input type="text" class="form-control" id="newItemName" aria-describedby="newItemNameHelp">
            <small id="newItemNameHelp" class="form-text text-muted"></small>
      	  </div>
          <div class="form-group">
            <label id="newItemDescriptionLabel" for="newItemDescription"></label>
            <input type="text" class="form-control" id="newItemDescription" aria-describedby="newItemDescriptionHelp">
            <small id="newItemDescriptionHelp" class="form-text text-muted"></small>
          </div>
          <button id="newItemSubmit" class="btn btn-primary preventDefault" onclick="">Submit</button>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
  const changedSettingsElements = new Set();
  const changedModalSettingsElements = new Set();
  var imagesLoaded = false;
  var tabsLoaded = [];
  var selectWithTableArr = {};
  let config = {};

  // ** Image Functions ** //
  
  function extractImageName(url) {
    let imageName = "";

    if (url.includes("/assets/images/custom/")) {
        imageName = url.split("/assets/images/custom/")[1];
        imageType = "native";
    } else if (url.includes("/api/image/plugin/")) {
        const parts = url.split("/");
        imageName = parts[parts.length - 2] + "." + parts[parts.length - 1];
        imageType = parts[parts.length - 3];
    }

    return {
      "name": imageName,
      "type": imageType
    }
  }

  function loadImageGallery() {
    if (imagesLoaded == false) {
      queryAPI("GET", "/api/images").done(function(images) {
        const imageGallery = $("#imageGallery");
        images.data.forEach(image => {
          var imageExtract = extractImageName(image);
          var imageName = imageExtract["name"];
          var imageType = imageExtract["type"];
          if (imageType == "native") {
            imageGallery.append(`<div class="image-container" data-bs-toggle="tooltip" data-bs-title="`+imageName+`"><img src="`+image+`" class="custom-image" data-image-name="`+imageName+`"></img><span class="fa fa-trash" onclick="deleteImage(this)"></span></div>`);
          } else {
           imageGallery.append(`<div class="image-container" data-bs-toggle="tooltip" data-bs-html="true" data-bs-title="Plugin: `+imageType+`<br>`+imageName+`"><img src="`+image+`" class="custom-image" data-image-name="`+imageName+`"></img></div>`);
          }
        });
        imagesLoaded = true;
        var tooltipTriggerList = document.querySelectorAll(`[data-bs-toggle="tooltip"]`)
        var tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
      }).fail(function(xhr) {
        toast("Error", "", xhr, "danger", 30000);
      });
    }
  }

  var imageGalleryDropzone = new Dropzone("#imageGallery", {
    url: "/api/images",
    paramName: "file", // The name that will be used to transfer the file
    maxFilesize: 5, // MB
    acceptedFiles: "image/*",
    disablePreviews: true,
    init: function() {
      this.on("sending", function(file, xhr, formData) {
        // Append additional data if needed
        formData.append("fileName", file.name);
      });
      this.on("success", function(file, response) {
        // Add the uploaded image to the gallery
        var imageGallery = $("#imageGallery");
        imageGallery.append(`<div class="image-container" data-bs-toggle="tooltip" data-bs-title="`+file.name+`"><img src="/assets/images/custom/`+file.name+`" data-image-name="`+file.name+`" class="custom-image"></img><span class="fa fa-trash" onclick="deleteImage(this)"></span></div>`);
        var newImage = imageGallery.find("img.custom-image").last();
        newImage.tooltip();
        toast ("Success","","Successfully uploaded image","success");
      });
      this.on("error", function(file, response) {
        toast ("Error","","Failed to upload image","danger", 30000);
      });
    }
  });

  function deleteImage(elem) {
    // Get the parent element of the trash icon, which is the image container
    var imageContainer = elem.parentElement;

    // Find the image element within the image container
    var imageElement = imageContainer.querySelector("img");

    var imgName = $(imageElement).attr("data-image-name");

    if(confirm("Are you sure you want to delete "+imgName+" from the list of Images? This is irriversible.") == true) {
      queryAPI("DELETE","/api/images?fileName="+imgName).done(function(data) {
        if (data["result"] == "Success") {
          imageElement.remove();
          toast(data["result"],"",data["message"],"success");
        } else if (data["result"] == "Error") {
          toast(data["result"],"",data["message"],"danger","30000");
        } else {
          toast("Error", "", "Failed to delete image", "danger");
        }
      }).fail(function() {
          toast("Error", "", "Failed to delete image", "danger");
      });
    };
  }

  function getConfig() {
    $.getJSON("/api/config", function(data) {
      config = data.data;

      const updateConfigValues = (config, parentKey = "") => {
        for (const section in config) {
          const value = config[section];
          const fullKey = parentKey ? `${parentKey}[${section}]` : section;
          const selector = `[name=${$.escapeSelector(fullKey)}]`;

          if (typeof value === "object" && !Array.isArray(value) && value !== null) {
            updateConfigValues(value, fullKey);
          } else if (typeof value === "boolean") {
            $(selector).prop("checked", value);
          } else {
            $(selector).val(value);
          }
        }
      };

      updateConfigValues(config);
    });
  }

  $("#submitConfig").click(function(event) {
    event.preventDefault();
    var formData = $("#page-content .info-field.changed").serializeArray();
    
    // Include unchecked checkboxes in the formData
    $("input.info-field.changed[type=checkbox]").each(function() {
        formData.push({ name: this.name, value: this.checked });
    });

    var configData = {};
    var encryptionPromises = [];

    formData.forEach(function(item) { 
        var keys = item.name.split("[").map(function(key) {
            return key.replace("]", "");
        });
        var temp = configData;
        keys.forEach(function(key, index) {
            if (index === keys.length - 1) {
                var inputElement = $("[name=\'" + item.name + "\']");
                if (inputElement.hasClass("encrypted") && item.value !== "") {
                    // Encrypt sensitive data
                    var promise = encryptData(item.name, item.value).done(function(encryptedValue) {
                        temp[key] = encryptedValue.data;
                    });
                    encryptionPromises.push(promise);
                } else {
                    temp[key] = item.value;
                }
            } else {
                temp[key] = temp[key] || {};
                temp = temp[key];
            }
        });
    });

    // Wait for all encryption promises to resolve
    $.when.apply($, encryptionPromises).done(function() {
        queryAPI("PATCH", "/api/config", configData).done(function(data) {
            if (data.result === "Success") {
                toast("Success", "", "Successfully saved configuration", "success");
                $(".info-field.changed").removeClass("changed");
                changedSettingsElements.clear();
            } else if (data.result === "Error") {
                toast("Error", "", "Failed to save configuration", "danger");
            } else {
                toast("API Error", "", "Failed to save configuration", "danger", "30000");
            }
        });
    });
  });

  // Function to switch tabs
  function switchTab(tabId) {
    $(`.nav-tabs a[href="` + tabId + `"]`).tab("show");
    var tabIdNoHash = tabId.split("#")[1];
    buildSettings($(tabId+`Form`), tabIdNoHash, {
      dataLocation: "data"
    });
  }
  // Listener for main tab changes
  $("#configTabs .nav-link, #configTabsDropdownMenu .dropdown-item").on("click", function(elem) {
    elem.preventDefault();
    $("#configTabsDropdown").text($(elem.target).text());
    var href = $(elem.target).attr("href");
    if (href == "#images") {
      loadImageGallery();
    }
    switchTab($(elem.target).attr("href"));
  });

  function populatePluginRepositories() {
    queryAPI("GET","/api/plugins/repositories").done(function(data) {
      if (data["result"] == "Success") {
        // Loop through the URLs and create list items
        if (data["data"]) {
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
        }
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
    const githubRepoPattern = /^https:\/\/github\.com\/[^\/]+\/[^\/]+$/;

    for (let i = 0; i < listItems.length; i++) {
      const listItem = listItems[i];
      const link = listItem.getElementsByTagName("a")[0];
      let url = link ? link.href : listItem.textContent.trim();

      if (githubRepoPattern.test(url)) {
        urls.push(url);
      } else {
        toast("Warning", "", "Invalid Github URL: "+url, "warning");
        return false;
      }
    }
    return urls;
  }

  $("#addUrl").click(function() {
    var url = $("#urlInput").val();
    if (url) {
        $("#urlList").append(`<li class="list-group-item newUrl"><a href="` + url + `">` + url + `</a><i class="fa fa-trash removeUrl"></i></li>`);
        $("#urlInput").val("");
    }
  });

  // Remove URL from the list using event delegation
  $("#urlList").on("click", ".removeUrl", function(elem) {
      $(elem.target).parent().remove();
  });

  $("#editOnlinePluginsSaveButton").on("click",function(elem) {
    var list = getAllRepositoryUrls();
    if (list) {
      queryAPI("POST","/api/plugins/repositories",{list: list}).done(function(data) {
        if (data["result"] == "Success") {
          toast(data["result"],"",data["message"],"success");
          $("#pluginsTable").bootstrapTable("refresh");
          $("#onlinePluginsModal").modal("hide");
        } else if (data["result"] == "Error") {
          toast(data["result"],"",data["message"],"danger");
        } else {
          toast("API Error","","Failed to save repository configuration","danger","30000");
        }
      }).fail(function(xhr) {
        toast("API Error","","Failed to save repository configuration","danger","30000");
      });
    }
  })

  getConfig();
  switchTab("#general");
</script>
';