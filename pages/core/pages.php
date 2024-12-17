<?php
  require_once(__DIR__."/../../inc/inc.php");
  if ($ib->rbac->checkAccess("ADMIN-PAGES") == false) {
    die();
  }
return '
<style>
.card {
  padding: 10px;
}
</style>

<div class="section">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <center>
            <h4>Page Configuration</h4>
            <p>Use the following to configure Navigation Links, Menus and Sub-Menus.</p>
          </center>
        </div>
      </div>
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="container">
          <table id="pagesTable" class="table table-striped" data-toggle="table" data-pagination="true" data-search="true" data-detail-view="true" data-detail-formatter="detailFormatter">
              <thead>
                  <tr>
                      <th data-field="Name">Name</th>
                      <th data-field="Title">Title</th>
                      <th data-field="Type">Type</th>
                      <th data-field="Url">URL</th>
                      <th data-field="ACL">ACL</th>
                      <th data-field="Icon" data-formatter="pageIconFormatter">Icon</th>
                  </tr>
              </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
  <br>
</div>

<!-- Edit Page Modal -->
<div class="modal fade" id="pageEditModal" tabindex="-1" role="dialog" aria-labelledby="pageEditModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pageEditModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"></span>
        </button>
      </div>
      <div class="modal-body" id="editModelBody">
        <h4>Group Information</h4>
	      <div class="form-group">
          <input type="text" class="form-control" id="editPageID" aria-describedby="editPageIDHelp" hidden>
          <div class="input-group mb-1">
            <input type="text" class="form-control" id="editPageTitle" aria-describedby="editPageTitleHelp">
            <div class="input-group-append">
	            <span class="input-group-text">
                <button class="btn btn-primary" id="editPageTitleSaveButton">Save</button>
	            </span>
            </div>
	        </div>
          <small id="editPageTitleHelp" class="form-text text-muted">The group description.</small>
	      </div>
	      <hr>
        <h4>Group Roles</h4>
        <p>Enable or Disable the following roles to provide granular control to specific areas of the Infoblox SA Tools Portal.</p>
	      <div class="list-group mb-5 shadow" id="modalListGroup"></div>
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
  function pageIconFormatter(value, row, index) {
      return `<i class="`+value+`"></i>`
  }

  function createTableHtml(index, items) {
      let html = [];
      let theme = getCookie("theme") == "dark" ? "table-dark" : "";
      
      html.push(`<table class="table table-striped `+theme+`" id="child-table-` + index +`"></table>`);
      return html.join("");
  }

  function detailFormatter(index, row) {
      let html = [];
      if (row.Items) {
          html.push(createTableHtml(index, Array.isArray(row.Items) ? row.Items : Object.values(row.Items)));
      }
      return html.join("");
  }

  function initializeChildTable(index, row, detail) {
      console.log(index,row,detail)
      if (!row.Items) return;
      const childTableId = `#child-table-${index}`;
      const detailView = !Array.isArray(row.Items);
      $(childTableId).bootstrapTable({
          data: Array.isArray(row.Items) ? row.Items : Object.values(row.Items),
          detailView: detailView,
          detailFormatter: detailFormatter,
          onExpandRow: initializeChildTable,
          columns: [{
            field: "Name",
            title: "Name",
            sortable: true
          },{
            field: "Title",
            title: "Title",
            sortable: true
          },
          {
            field: "Type",
            title: "Type",
            sortable: true
          },{
            field: "Url",
            title: "URL",
            sortable: true
          },{
            field: "ACL",
            title: "ACL",
            sortable: true
          },{
            field: "Icon",
            title: "Icon",
            formatter: "pageIconFormatter",
            sortable: true
          }]
      });
  }

  queryAPI("GET","/api/pages/hierarchy",false).done(function(data) {
    if (data["result"] == "Success") {
      const flattenedData = Object.values(data["data"]); // Flatten your JSON data here
      console.log(flattenedData);
      $("#pagesTable").bootstrapTable("destroy");
      $("#pagesTable").bootstrapTable({
          data: flattenedData,
          detailView: true,
          detailFormatter: detailFormatter,
          onExpandRow: initializeChildTable
      });
    } else if (data["result"] == "Error") {
      toast(data["result"],"",data["message"],"danger","30000");
    }
  }).fail(function() {
      toast("Error", "", "Failed to query page information", "danger");
  });
</script>
';