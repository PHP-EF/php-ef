<?php
  require_once(__DIR__.'/../inc/inc.php');
?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

	<title>Security Assessment Report Generator</title>

	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
  <meta name="viewport" content="width=device-width" />
</head>
<body>

<div class="wrapper">
    <div class="main-child-panel main-panel-theme" data-background-color="white">
		<nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href="#">Security Assessment Report Generator</a>
                </div>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav navbar-right">
                    </ul>
                </div>
            </div>
        </nav>


        <div class="mainContainer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <!-- <div class="header">
                                <h3 class="title">Security Assessment Report Generator</h3>
                                <p class="category"></p>
                            </div> -->
                            <div class="content">
                                  <div class="row justify-content-md-center">
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
                                  <!-- <div class="col-md-2 ml-md-auto realm">
                                    <input class="dateTimePicker" type="text" id="startDate" placeholder="Start Date/Time">&nbsp;
                                  </div>
                                  <div class="col-md-2 ml-md-auto realm">
                                    <input class="dateTimePicker" type="text" id="endDate" placeholder="End Date/Time">
                                  </div> -->
                                  </div>
                                  <div class="row justify-content-md-center">
                                  <div class="calendar">
                                    <div class="col-sm ml-sm-auto">
                                    <h5>Start:&nbsp;</h5><input class="dateTimePicker" type="text" id="startDate" placeholder="Start Date/Time">&nbsp;
                                    </div>
                                    <div class="col-sm">
                                    <h5>End:&nbsp;</h5><input class="dateTimePicker" type="text" id="endDate" placeholder="End Date/Time">
                                    </div>
                                  </div>
                                  </div>
                                  <div class="calendar">
                                  </div>
                                  <br>
                                  <div class="alert alert-info genInfo" role="alert">
                                  It can take up to 2 minutes to generate the report, please be patient.
                                  </div>
                                  <button class="btn btn-success" id="Generate">Generate Report</button>
                                  <div class="loading-icon">
                                  <hr>
                                  <div class="progress">
                                    <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                  </div>
                                  <br>
                                  <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Loading...</span>
                                  </div>
                                  </div>
                                </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


</body>

</html>

<script>
let haltProgress = false;

function download(url) {
  const a = document.createElement('a')
  a.href = url
  a.download = url.split('/').pop()
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
}

function showLoading(id) {
  document.querySelector('.loading-icon').style.display = 'block';
  haltProgress = false;
  updateProgress(id);
}
function hideLoading() {
  document.querySelector('.loading-icon').style.display = 'none';
  $('#progress-bar').css('width', '0%').attr('aria-valuenow', 0).text('0%');
  haltProgress = true;
}

function updateProgress(id) {
  $.get('../api?function=getSecurityReportProgress&id='+id, function(data) {
      var progress = parseFloat(data).toFixed(1); // Assuming the server returns a JSON object with a 'progress' field
      $('#progress-bar').css('width', progress + '%').attr('aria-valuenow', progress).text(progress + '%');
      if (progress < 100 && haltProgress == false) {
        setTimeout(function() {
          updateProgress(id);
        }, 1000);
      }
  });
}

$("#changelog-modal-button").click(function(){
  $("#changelog-modal").modal('show')
})

$("#Generate").click(function(){
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

  $("#Generate").prop('disabled', true)
  $.get( "../api?function=getUUID", function( id ) {
    showLoading(id);
    const startDateTime = new Date($('#startDate')[0].value)
    const endDateTime = new Date($('#endDate')[0].value)
    var postArr = {}
    postArr.StartDateTime = startDateTime.toISOString()
    postArr.EndDateTime = endDateTime.toISOString()
    postArr.Realm = $('#Realm').find(":selected").val()
    postArr.id = id
    if ($('#APIKey')[0].value) {
      postArr.APIKey = $('#APIKey')[0].value
    }
    $.post( "../api?function=createSecurityReport", postArr).done(function( data, status ) {
      if (data['Status'] == 'Success') {
        toast("Success","","The report has been successfully generated.","success","30000");
        download('../api?function=downloadSecurityReport&id='+data['id'])
      } else {
        toast(data['Status'],"",data['Error'],"danger","30000");
      }
    }).fail(function( data, status ) {
        toast("API Error","","Unknown API Error","danger","30000");
    }).always(function() {
        hideLoading()
        $("#Generate").prop('disabled', false)
    });
  });
});
</script>