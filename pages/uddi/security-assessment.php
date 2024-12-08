<?php
  require_once(__DIR__.'/../../inc/inc.php');
  if ($ib->auth->checkAccess(null,"B1-SECURITY-ASSESSMENT") == false) {
    die();
  }
?>

<section class="section">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <center>
            <h4>Security Assessment Report Generator</h4>
            <p>You can use this tool to generate Security Assessment Reports for Infoblox Portal accounts.</p>
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
            <div class="row justify-content-md-center toolsMenu">
              <div class="col-md-4 apiKey">
                  <input onkeyup="checkInput(this.value)" id="APIKey" type="password" placeholder="Enter API Key" required>
                  <i class="fas fa-save saveBtn" id="saveBtn"></i>
              </div>
              <div class="col-md-2 realm">
                  <select id="Realm" class="form-select" aria-label="Realm Selection">
                      <option value="US" selected>US Realm</option>
                      <option value="EU">EU Realm</option>
                  </select>
              </div>
              <div class="col-md-3">
                  <input type="text" id="startAndEndDate" placeholder="Start & End Date/Time">
              </div>
              <div class="col-md-2 actions">
                <button class="btn btn-success" id="Generate">Generate</button>
              </div>
            </div>
            <div class="row mt-3">
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
  <br>
  <div class="row loading-div">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <div class="loading-icon">
            <br>
            <div class="alert alert-info genInfo" role="alert">
              <center>It can take up to 3 minutes to generate the report, please be patient.</center>
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
            <p class="progressAction" id="progressAction"></p>
            <small id="elapsed"></small>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


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

function showLoading(id,timer) {
  document.querySelector('.loading-icon').style.display = 'block';
  document.querySelector('.loading-div').style.display = 'block';
  haltProgress = false;
  updateProgress(id,timer);
}

function hideLoading(timer) {
  document.querySelector('.loading-icon').style.display = 'none';
  document.querySelector('.loading-div').style.display = 'none';
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
  // if(!$('#startDate')[0].value){
  //   toast("Error","Missing Required Fields","The Start Date is a required field.","danger","30000");
  //   return null;
  // }
  // if(!$('#endDate')[0].value){
  //   toast("Error","Missing Required Fields","The End Date is a required field.","danger","30000");
  //   return null;
  // }
  if(!$('#startAndEndDate')[0].value){
    toast("Error","Missing Required Fields","The Start & End Date is a required field.","danger","30000");
    return null;
  }

  $("#Generate").prop('disabled', true)
  $.get( "/api?f=getUUID", function( id ) {
    let timer = startTimer();
    showLoading(id,timer);
    const startAndEndDate = $('#startAndEndDate')[0].value.split(" to ");
    const startDateTime = new Date(startAndEndDate[0]);
    const endDateTime = new Date(startAndEndDate[1]);
    var postArr = {};
    postArr.StartDateTime = startDateTime.toISOString();
    postArr.EndDateTime = endDateTime.toISOString();
    postArr.Realm = $('#Realm').find(":selected").val();
    postArr.id = id;
    postArr.unnamed = $('#unnamed')[0].checked;
    postArr.substring = $('#substring')[0].checked;
    if ($('#APIKey')[0].value) {
      postArr.APIKey = $('#APIKey')[0].value;
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