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
              <li><a class="dropdown-item nav-link" href="#pages" data-bs-toggle="tab">Pages</a></li>
              <li><a class="dropdown-item nav-link" href="#plugins" data-bs-toggle="tab">Plugins</a></li>
              <li><a class="dropdown-item nav-link" href="#images" data-bs-toggle="tab">Images</a></li>
              <li><a class="dropdown-item nav-link" href="#dashboards" data-bs-toggle="tab">Dashboards</a></li>
              <li><a class="dropdown-item nav-link" href="#customisation" data-bs-toggle="tab">Customisation</a></li>
              <li><a class="dropdown-item nav-link" href="#notifications" data-bs-toggle="tab">Notifications</a></li>
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
          <a class="nav-link" id="pages-tab" data-bs-toggle="tab" href="#pages" role="tab" aria-controls="pages" aria-selected="false">Pages</a>
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
        <li class="nav-item d-none d-lg-flex">
          <a class="nav-link" id="customisation-tab" data-bs-toggle="tab" href="#customisation" role="tab" aria-controls="customisation" aria-selected="false">Customisation</a>
        </li>
        <li class="nav-item d-none d-lg-flex">
          <a class="nav-link" id="notifications-tab" data-bs-toggle="tab" href="#notifications" role="tab" aria-controls="notifications" aria-selected="false">Notifications</a>
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
        <div class="tab-pane fade" id="pages" role="tabpanel" aria-labelledby="pages-tab">
          <div class="my-4">
            <div class="card card-rounded border-secondary p-3">
              <div class="pages-content">
                <form id="pagesForm"></form>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
          <div class="my-4">
            <div class="card card-rounded border-secondary p-3">
              <div class="notifications-content">
                <form id="notificationsForm"></form>
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

<script>
  var changedSettingsElements = new Set();
  var changedModalSettingsElements = new Set();
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

    // Include dynamic image selects in the formData
    $("input.dynamic-select-input.changed").each(function() {
        formData.push({ name: this.name, value: this.value });
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
            var inputElement = $(`[name="${item.name}"]`);
            if (inputElement.is("select[multiple]")) {
                // Handle multi-select inputs
                temp[key] = inputElement.val() !== "" ? convertArrayToCSV(inputElement.val()) : "";
            } else if (inputElement.hasClass("encrypted") && item.value !== "") {
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

  $("body").on("click", "#widgetSelect", function () {
    const widgetsSelect = $("#widgetSelect");
    const table = $("#widgetSelectTable");
    const selectedValues = Array.from(widgetsSelect[0].selectedOptions).map(option => option.value);
    
    // Get current table data
    const currentData = table.bootstrapTable("getData");
    const sizeMap = {};
    // Store current sizes
    currentData.forEach(row => {
        const selectElement = table.find(`select[data-label="size"]`).filter((index, element) => $(element).closest("tr").find("td").eq(1).text() === row.name);
        if (selectElement.length) {
            sizeMap[row.name] = selectElement.val();
        }
    });
    const newData = [];
    // Add or update rows based on selected options
    Array.from(widgetsSelect[0].selectedOptions).forEach(option => {
        if (option.value) {
            const existingRow = currentData.find(row => row.name === option.text);
            if (existingRow) {
                newData.push(existingRow);
            } else {
                newData.push({
                    dragHandle: `<span class="dragHandle" style="font-size:22px;">☰</span>`,
                    name: option.text,
                    size: `<select class="form-select" data-label="size">
                            <option value="col-md-1">1</option>
                            <option value="col-md-2">2</option>
                            <option value="col-md-3">3</option>
                            <option value="col-md-4">4</option>
                            <option value="col-md-5">5</option>
                            <option value="col-md-6">6</option>
                            <option value="col-md-7">7</option>
                            <option value="col-md-8">8</option>
                            <option value="col-md-9">9</option>
                            <option value="col-md-10">10</option>
                            <option value="col-md-11">11</option>
                            <option value="col-md-12">12</option>
                        </select>`
                });
            }
        }
    });
    // Remove rows that are no longer selected
    currentData.forEach(row => {
        if (!selectedValues.includes(row.name)) {
            table.bootstrapTable("remove", { field: "name", values: [row.name] });
        }
    });
    // Update table with new data
    table.bootstrapTable("load", newData);
    // Restore selected values in the dropdowns
    newData.forEach(row => {
        const selectElement = table.find(`select[data-label="size"]`).filter((index, element) => $(element).closest("tr").find("td").eq(1).text() === row.name);
        if (selectElement.length && sizeMap[row.name]) {
            selectElement.val(sizeMap[row.name]);
        }
    });
  });

  getConfig();
  switchTab("#general");
</script>
';