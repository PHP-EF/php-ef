<?php
  require_once(__DIR__.'/../../inc/inc.php');
  if (CheckAccess(null,"ADMIN-LOGS") == false) {
    die();
  }

?>

<style>
pre {
  background-color: #000;
  overflow: auto;
  font-family: 'Monaco', monospace;
  padding: 0 1em;
}

code {
  font-family: Monaco, monospace;
  font-size: $base-font-size;
  line-height: 100%;
 /background-color: #000;/
  padding: 0.2em;
  letter-spacing: -0.05em;
  word-break: normal;
  /border-radius: 5px;/
}

pre code {
  border: none;
  background: none;
  font-size: $base-font-size * 0.875;
  line-height: 1em;
  letter-spacing: normal;
  word-break: break-all;
  color:#FFF;
  white-space: pre-wrap;
}
</style>


<div class="container">
<div class="row justify-content-center">
    <div class="col-14 col-lg-14 col-xl-14 mx-auto">
        <h2 class="h3 mb-4 page-title">Logs</h2>
        <div class="my-4">
            <h5 class="mb-0 mt-5">Logs</h5>
            <p>The following table displays logs from the Infoblox SA Tools Portal.</p>
            <table  data-url="/api/?function=GetLog"
              data-toggle="table"
              data-search="true"
	      data-filter-control="true"
              data-show-filter-control-switch="true"
	      data-filter-control-visible="false"
              data-filter-control-multiple-search="true"
	      data-show-export="true"
	      data-show-refresh="true"
              data-show-columns="true"
              data-pagination="true"
	      data-toolbar="#toolbar"
              data-query-params="queryParams"
	      class="table table-striped"
	      id="logTable">

              <div id="toolbar" class="select">
		<select class="form-select" id="logDate">
                  <?php
                    $LogFiles = array_reverse(getLogFiles());
                    $LogFileArr = array();
		    foreach ($LogFiles as $LogFile) {
                      $LogFileShort = explode(".log",explode(getConfig("System","logfilename")."-",$LogFile)[1]);
                      if ($LogFileShort[0] != null) {
                        array_push($LogFileArr,$LogFileShort[0]);
                      }
		    }
		    usort($LogFileArr, "compareByTimestamp");
		    foreach ($LogFileArr as $LogFileItem) {
                      echo '<option value="'.$LogFileItem.'">'.date('d/m/Y',strtotime($LogFileItem)).'</option>';
		    }
                  ?>
                </select>
              </div>
              <thead>
                <tr>
                  <th data-field="date" data-sortable="true" data-formatter="dateFormatter" data-filter-control="input">Date</th>
		  <th data-field="logger" data-sortable="true" data-filter-control="select">Logger</th>
                  <th data-field="level" data-sortable="true" data-filter-control="select">Level</th>
		  <th data-field="message" data-sortable="true" data-filter-control="input">Message</th>
		  <th data-field="displayname" data-sortable="true" data-filter-control="select">Display Name</th>
		  <th data-field="username" data-sortable="true" data-filter-control="select" data-visible="false">Username</th>
                  <th data-field="ipaddress" data-sortable="true" data-filter-control="input">IP Address</th>
                  <th data-formatter="actionFormatter" data-events="actionEvents">Actions</th>
                </tr>
              </thead>
              <tbody id="rbacgroups">
	      </tbody>
	    </table>
        </div>
    </div>
</div>
</div>

<!-- Log Modal -->
<div class="modal fade" id="logModal" tabindex="-1" role="dialog" aria-labelledby="logModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"></span>
        </button>
      </div>
      <div class="modal-body" id="logModelBody">
        <pre><code id="logData" style="color:#FFF;">
        </code></pre>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<script>
  function actionFormatter(value, row, index) {
    return [
      '<a class="edit" title="Edit">',
      '<i class="fa fa-search"></i>',
      '</a>'
    ].join('')
  }

  $('#logDate').on('change', function(event) {
    $('#logTable').bootstrapTable('refresh');  
  });

  function queryParams(params) {
    var logDate = $('#logDate').find(":selected").val();    
    params.date = logDate;
    return params;
  }

  function dateFormatter(value, row, index) {
    var d = new Date(0) // The 0 there is the key, which sets the date to the epoch
    d.setUTCSeconds(value);
    return d.toGMTString();
  }

  function logData(row) {
    document.getElementById('logData').innerHTML = row;
  }

  window.actionEvents = {
    'click .edit': function (e, value, row, index) { 
      var jsonPretty = JSON.stringify(JSON.parse(row.context),null,2);
      logData(jsonPretty);
      $('#logModal').modal('show');
    }
  }
</script>