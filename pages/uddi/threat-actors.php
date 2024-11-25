<?php
  require_once(__DIR__.'/../../inc/inc.php');
  if ($ib->auth->checkAccess(null,"B1-THREAT-ACTORS") == false) {
    die();
  }
?>

<section class="section">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <center>
            <h4>Threat Actor Query</h4>
            <p>You can use this tool to perform queries on Threat Actors found in a particular Infoblox Portal account.</p>
          </center>
        </div>
      </div>
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <div class="container">
            <div class="row justify-content-center">
              <div class="col-12 col-lg-12 col-xl-12 mx-auto">
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
                <div class="row">
                  <div class="col-md-6 options">
                    <div class="form-group">
                      <div class="form-check form-switch">
                        <input class="form-check-input info-field" type="checkbox" id="unnamed" name="unnamed">
                        <label class="form-check-label" for="unnamed">Enable Unnamed Actors</label>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="form-check form-switch">
                        <input class="form-check-input info-field" type="checkbox" id="substring" name="substring">
                        <label class="form-check-label" for="substring">Enable Substring_* Actors</label>
                      </div>
                    </div>
                  </div>
                </div>
                <br>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <br>
  <div class="row loading-div">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <div class="loading-icon">
            <div class="alert alert-info genInfo" role="alert">
              <center>It can take up to 2 minutes to generate the list of Threat Actors, please be patient.</center>
            </div>
            <hr>
            <div class="progress">
              <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>
            <br>
            <div id="spinner-container">
              <div class="spinner-bounce">
                <div class="spinner-child spinner-bounce1"></div>
                <div class="spinner-child spinner-bounce2"></div>
                <div class="spinner-child spinner-bounce3"></div>
              </div>
            </div>
            <small id="elapsed"></small>
          </div>
          <table id="threatActorTable" class="table table-striped rounded"></table>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Observed IOC Modal -->
<div class="modal fade" id="observedIOCModal" tabindex="-1" role="dialog" aria-labelledby="observedIOCModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
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

<script>
  function showLoading(timer) {
    $("#GetActors").prop('disabled', true)
    document.querySelector('.loading-icon').style.display = 'block';
    document.querySelector('.loading-div').style.display = 'block';
  }

  function hideLoading(timer) {
    $("#GetActors").prop('disabled', false)
    document.querySelector('.loading-icon').style.display = 'none';
    $('#progress-bar').css('width', '0%').attr('aria-valuenow', 0).text('0%');
    stopTimer(timer);
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

  function dateFormatter(value, row, index) {
    var d = new Date(value) // The 0 there is the key, which sets the date to the epoch
    return d.toGMTString();
  }

  // Workaround
  function populateObservedIOCs(row) {
    $('#threatActorObservedIOCTable').bootstrapTable('destroy');
    $('#threatActorObservedIOCTable').bootstrapTable({
      data: row['observed_iocs'],
      sortable: true,
      pagination: true,
      search: true,
      showExport: true,
      exportTypes: ['json', 'xml', 'csv', 'txt', 'excel', 'sql'],
      showColumns: true,
      columns: [{
        field: 'ThreatActors.domain',
        title: 'Indicator',
        sortable: true
      },{
        field: 'ThreatActors.ikbfirstsubmittedts',
        title: 'Submitted',
        sortable: true,
        formatter: 'dateFormatter'
      },{
        field: 'ThreatActors.lastdetectedts',
        title: 'Last Detected',
        sortable: true,
        formatter: 'dateFormatter'
      },{
        field: 'ThreatActors.vtfirstdetectedts',
        title: 'Virus Total Detected',
        sortable: true,
        formatter: 'dateFormatter'
      }]
    });
  }
  // function populateObservedIOCs(row) {
  //   $('#threatActorObservedIOCTable').bootstrapTable('destroy');
  //   $('#threatActorObservedIOCTable').bootstrapTable({
  //     data: row['related_indicators_with_dates'],
  //     sortable: true,
  //     pagination: true,
  //     search: true,
  //     showExport: true,
  //     exportTypes: ['json', 'xml', 'csv', 'txt', 'excel', 'sql'],
  //     showColumns: true,
  //     columns: [{
  //       field: 'indicator',
  //       title: 'Indicator',
  //       sortable: true
  //     },{
  //       field: 'te_ik_submitted',
  //       title: 'Submitted',
  //       sortable: true
  //     },{
  //       field: 'te_customer_last_dns_query',
  //       title: 'Last Queried',
  //       sortable: true
  //     },{
  //       field: 'vt_first_submission_date',
  //       title: 'Virus Total Submitted',
  //       sortable: true
  //     }]
  //   });
  // }

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

    let timer = startTimer();
    showLoading(timer);
    const startDateTime = new Date($('#startDate')[0].value)
    const endDateTime = new Date($('#endDate')[0].value)
    var postArr = {}
    postArr.StartDateTime = startDateTime.toISOString()
    postArr.EndDateTime = endDateTime.toISOString()
    postArr.Realm = $('#Realm').find(":selected").val()
    postArr.unnamed = $('#unnamed')[0].checked;
    postArr.substring = $('#substring')[0].checked;
    if ($('#APIKey')[0].value) {
      postArr.APIKey = $('#APIKey')[0].value
    }
    $.post( "/api?f=getThreatActors", postArr).done(function( data, status ) {
      if (data['Status'] == 'Error') {
        toast(data['Status'],"",data['Error'],"danger","30000");
        hideLoading(timer);
      } else if (data['error']) {
        toast('Error',"",data['error'][0]['message'],"danger","30000");
        hideLoading(timer);
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
          },
          //{
          //  field: 'related_indicators_with_dates',
          //  title: 'Observed IOCs',
          //  formatter: 'iocCountFormatter',
          //  sortable: true
          {
            field: 'observed_iocs',
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
        hideLoading(timer);
        return false;
      }
    }).fail(function( data, status ) {
      toast("API Error","","Unknown API Error","danger","30000");
    }).always(function() {
      hideLoading(timer);
    });
  });
</script>