<?php
  require_once(__DIR__."/../../inc/inc.php");
  if ($ib->auth->checkAccess("ADMIN-PLUGINS") == false) {
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
            <h4>Plugins</h4>
            <p>Use the following to configure Plugins installed on '.$ib->config->get('Styling')['websiteTitle'].'.</p>
          </center>
        </div>
      </div>
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-title ms-3 mt-2">
          <h5>Installed Plugins</h5>
        </div>
        <div class="container">
          <table data-url="/api/config/plugins"
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
            class="table table-striped" id="pluginsTable">

            <thead>
              <tr>
                <th data-field="state" data-checkbox="true"></th>
                <th data-field="name" data-sortable="true">Plugin Name</th>
                <th data-field="author" data-sortable="true">Author</th>
                <th data-field="category" data-sortable="true">Category</th>
                <th data-field="version" data-sortable="true">Version</th>
                <th data-field="link" data-sortable="true">URL</th>
                <th data-formatter="pluginActionFormatter" data-events="pluginActionEvents">Actions</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
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
        <button class="btn btn-primary" id="editRoleSaveButton">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
  function pluginActionFormatter(value, row, index) {
    if (row.settings) {
      var actions = `<a class="edit" title="Edit"><i class="fa fa-pencil"></i></a>&nbsp;`
      return actions
    }
  }

  window.pluginActionEvents = {
    "click .edit": function (e, value, row, index) {
      buildPluginSettingsModal(row.name);
      $("#pluginSettingsModal").modal("show");
    }
  }

  $("#pluginsTable").bootstrapTable();
</script>
';