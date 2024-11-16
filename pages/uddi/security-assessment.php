<?php
  require_once(__DIR__.'/../../inc/inc.php');
  if ($ib->auth->checkAccess(null,"B1-SECURITY-ASSESSMENT") == false) {
    die();
  }
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

<div class="container">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-12 col-xl-12 mx-auto">
      <h2 class="h3 mb-4 page-title">Security Assessment Report Generator</h2>

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
            <button class="btn btn-success" id="Generate">Generate</button>
          </div>
      </div>
      <br>
      <div class="alert alert-info genInfo" role="alert">
        <center>It can take up to 5 minutes to generate the report, please be patient.</center>
      </div>
      <div class="calendar"></div>
        <div class="loading-icon">
          <hr>
          <div class="progress">
            <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
          </div>
          <br>
          <div id="spinner-container">
            <div class="spinner-grow text-warning" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <div class="spinner-grow text-success" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <div class="spinner-grow text-info" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
          <p class="progressAction" id="progressAction"></p>
          <small id="elapsed"></small>
        </div>
      </div>
	  </div>
  </div>
</div>
</body>

</html>

<script>
let haltProgress = false;

const spinners = document.querySelectorAll('.spinner-grow');

function showSpinners() {
  // Show spinners one by one
  spinners.forEach((spinner, i) => {
    setTimeout(() => {
      spinner.style.display = 'inline-block'; // Show spinner
    }, i * 1000); // Delay of 1 second for each spinner
  });

  // Hide spinners in the same order
  setTimeout(() => {
    spinners.forEach((spinner, i) => {
      setTimeout(() => {
        spinner.style.display = 'none'; // Hide spinner
      }, i * 1000); // Delay of 1 second for each spinner
    });
  }, (spinners.length * 1000) + 1000); // Start hiding after all are shown

  setTimeout(function() {
    showSpinners();
  }, (spinners.length * 2) * 1000);
}

function hideSpinners() {
  spinners.forEach((spinner) => {
    spinner.style.display = 'none'; // Hide all spinners
  });
}

function download(url) {
  const a = document.createElement('a')
  a.href = url
  a.download = url.split('/').pop()
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
}

function showLoading(id,timer) {
  document.querySelector('.loading-icon').style.display = 'block';
  $('.spinner-grow').css('display','none');
  showSpinners();
  haltProgress = false;
  updateProgress(id,timer);
}
function hideLoading(timer) {
  document.querySelector('.loading-icon').style.display = 'none';
  $('#progress-bar').css('width', '0%').attr('aria-valuenow', 0).text('0%');
  haltProgress = true;
  stopTimer(timer);
}

function updateProgress(id,timer) {
  $.get('/api?f=getSecurityReportProgress&id='+id, function(data) {
      var progress = parseFloat(data['Progress']).toFixed(1); // Assuming the server returns a JSON object with a 'progress' field
      $('#progress-bar').css('width', progress + '%').attr('aria-valuenow', progress).text(progress + '%');
      $('#progressAction').text(data['Action'])
      if (progress < 100 && haltProgress == false) {
        setTimeout(function() {
          updateProgress(id,timer);
        }, 1000);
      } else if (progress >= 100 && data['Action'] == 'Done..' ) {
        toast("Success","","Security Assessment Successfully Generated","success","30000");
        download('/api?f=downloadSecurityReport&id='+id);
        hideLoading(timer);
        $("#Generate").prop('disabled', false);
      }
  }).fail(function( data, status ) {
    setTimeout(function() {
      updateProgress(id,timer);
    }, 1000);
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
  $.get( "/api?f=getUUID", function( id ) {
    let timer = startTimer();
    showLoading(id,timer);
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
    $.post( "/api?f=createSecurityReport", postArr).done(function( data, status ) {
      if (data['Status'] == 'Success') {
        toast("Success","Do not refresh the page","Security Assessment Report Job Started Successfully","success","30000");
      } else {
        toast(data['Status'],"",data['Error'],"danger","30000");
        hideLoading(timer);
        $("#Generate").prop('disabled', false);
      }
    }).fail(function( data, status ) {
        toast("API Error","","Unknown API Error","danger","30000");
        hideLoading(timer);
        $("#Generate").prop('disabled', false);
    }).always(function() {
    });
  });
});
</script>