<?php
  require_once(__DIR__.'/../../inc/inc.php');
  if (CheckAccess(null,"B1-THREAT-ACTORS") == false) {
    die();
  }
?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

	<title>Threat Actors</title>

	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
  <meta name="viewport" content="width=device-width" />
</head>
<body>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-12 col-xl-12 mx-auto">
      <h2 class="h3 mb-4 page-title">Threat Actors</h2>

      <div class="row justify-content-md-center toolsMenu">
          <div class="col-md-4 ml-md-auto apiKey">
              <input onkeyup="checkInput(this.value)" id="APIKey" type="password" placeholder="Enter API Key" required>
              <i class="fas fa-save saveBtn" id="saveBtn"></i>
          </div>
          <div class="col-md-2 ml-md-auto realm">
              <select id="Realm" class="form-select" aria-label="Realm Selection">
                  <option value="US" selected>US Realm</option>
                  <option value="EU">EU Realm</option>
              </select>
          </div>
          <div class="col-md-2 ml-md-auto startDate">
              <input type="text" id="startDate" placeholder="Start Date/Time">
          </div>
          <div class="col-md-2 ml-md-auto endDate">
              <input type="text" id="endDate" placeholder="End Date/Time">
          </div>
          <div class="col-md-2 ml-md-auto actions">
            <button class="btn btn-success" id="Actors">Get Actors</button>
          </div>
      </div>
      <br>
      <div class="alert alert-info genInfo" role="alert">
        <center>It can take up to 2 minutes to generate the list of Threat Actors, please be patient.</center>
      </div>
      <div class="calendar"></div>
          <div class="loading-icon">
          <hr>
          <div class="spinner-border text-primary" role="status">
              <span class="sr-only">Loading...</span>
          </div>
      </div>
      <table id="threatActorTable" class="table table-striped rounded"></table>
	  </div>
  </div>
</div>


    <div class="modal fade" id="observedIOCModal" tabindex="-1" role="dialog" aria-labelledby="observedIOCModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="observedIOCModalLabel">Observed Indicators</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body" id="observedIOCModalBody">
                    <table id="threatActorObservedIOCTable" class="table table-striped rounded"></table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

</body>

</html>

<script>
  function showLoading() {
    document.querySelector('.loading-icon').style.display = 'block';
    $("#GetActors").prop('disabled', true)
  }
  function hideLoading() {
    document.querySelector('.loading-icon').style.display = 'none';
    $("#GetActors").prop('disabled', false)
  }

  function iocCountFormatter(value, row, index) {
    if (value) {
        return value.length;
    }
  }

  function actionFormatter(value, row, index) {
    return [
      '<a class="inspect" title="inspect" style="padding:5px">',
      '<i class="fa fa-magnifying-glass"></i>',
      '</a>'
    ].join('')
  }

  function populateObservedIOCs(row) {
    $('#threatActorObservedIOCTable').bootstrapTable('destroy');
    $('#threatActorObservedIOCTable').bootstrapTable({
      data: row['related_indicators_with_dates'],
      sortable: true,
      pagination: true,
      search: true,
      showExport: true,
      exportTypes: ['json', 'xml', 'csv', 'txt', 'excel', 'sql'],
      showColumns: true,
      columns: [{
        field: 'indicator',
        title: 'Indicator',
        sortable: true
      },{
        field: 'te_ik_submitted',
        title: 'Submitted',
        sortable: true
      },{
        field: 'te_customer_last_dns_query',
        title: 'Last Queried',
        sortable: true
      },{
        field: 'vt_first_submission_date',
        title: 'Virus Total Submitted',
        sortable: true
      }]
    });
  }

  window.actionEvents = {
    'click .inspect': function (e, value, row, index) {
      console.log(row);
      populateObservedIOCs(row);
      $('#observedIOCModal').modal('show');
    }
  }

  $('#Actors').on("click",function(e) {
    if (!$('#APIKey').is(':disabled')) {
        if(!$('#APIKey')[0].value) {
            toast("Error","Missing Required Fields","The API Key is a required field.","danger","30000");
            return null;
        }
    }
    if(!$('#startDate')[0].value){
        toast("Error","Missing Required Fields","The Start Date is a required field.","danger","30000");
        return null;
    }
    if(!$('#endDate')[0].value){
        toast("Error","Missing Required Fields","The End Date is a required field.","danger","30000");
        return null;
    }

    showLoading();
    const startDateTime = new Date($('#startDate')[0].value)
    const endDateTime = new Date($('#endDate')[0].value)
    var postArr = {}
    postArr.StartDateTime = startDateTime.toISOString()
    postArr.EndDateTime = endDateTime.toISOString()
    postArr.Realm = $('#Realm').find(":selected").val()
    if ($('#APIKey')[0].value) {
      postArr.APIKey = $('#APIKey')[0].value
    }
    $.post( "/api?function=getThreatActors", postArr).done(function( data, status ) {
      if (data['Status'] == 'Error') {
        toast(data['Status'],"",data['Error'],"danger","30000");
      } else if (data['error']) {
        toast('Error',"",data['error'][0]['message'],"danger","30000");
      } else {
        $('#threatActorTable').bootstrapTable('destroy');
        $('#threatActorTable').bootstrapTable({
          data: data,
          sortable: true,
          pagination: true,
          search: true,
          showExport: true,
          exportTypes: ['json', 'xml', 'csv', 'txt', 'excel', 'sql'],
          showColumns: true,
          columns: [{
            field: 'actor_name',
            title: 'Name',
            sortable: true
          },{
            field: 'actor_description',
            title: 'Description',
            sortable: true
          },{
            field: 'related_indicators_with_dates',
            title: 'Observed IOCs',
            formatter: 'iocCountFormatter',
            sortable: true
          },{
            field: 'related_count',
            title: 'Related IOCs',
            sortable: true
          },{
            field: 'actor_id',
            title: 'ID',
            sortable: false
          },{
            title: 'Actions',
            formatter: 'actionFormatter',
            events: 'actionEvents',
          }]
        });
        hideLoading();
        return false;
      }
    }).fail(function( data, status ) {
        toast("API Error","","Unknown API Error","danger","30000");
    }).always(function() {
        hideLoading()
    });;
  });
</script>