<?php
  require_once(__DIR__.'/scripts/inc/inc.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Infoblox Security Assessment Report Generator</title>
</head>
<body>
    <div class="mainContainer">
      <div class="container">
        <h2>Infoblox Security Assessment Report Generator</h2>
        <div class="row justify-content-md-center">
          <div class="col-md-4 ml-md-auto apiKey">
            <input id="APIKey" type="password" placeholder="Enter API Key" required>
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

      <div id="changelog-modal" class="modal fade changelog-modal" tabindex="-1" role="dialog" aria-labelledby="Change Log" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content changelog-modal">
              <iframe src="api?function=getChangelog"></iframe>
            </div>
          </div>
      </div>

      <div class="footnote">
        <a href="https://github.com/TehMuffinMoo" target="_blank"><i class="fab fa-github fa-lg"></i> &copy; 2024 Mat Cox.</a>
        <button class="btn btn-light float-end btn-sm changelog-btn" id="changelog-modal-button" href="#">v0.1.4</button>
      </div>
    </div>
</body>
</html>

<script>
let haltProgress = false;

document.addEventListener('DOMContentLoaded', function() {
  const maxDaysApart = 31;
  const today = new Date();
  const maxPastDate = new Date(today);
  maxPastDate.setDate(today.getDate() - maxDaysApart);

  flatpickr("#startDate", {
    enableTime: true,
    dateFormat: "Y-m-d H:i",
    minDate: maxPastDate,
    maxDate: today,
    onChange: function(selectedDates, dateStr, instance) {
      const endDatePicker = document.getElementById('endDate')._flatpickr;
      const maxEndDate = new Date(selectedDates[0]);
      maxEndDate.setDate(maxEndDate.getDate() + maxDaysApart);
      endDatePicker.set('minDate', dateStr);
      endDatePicker.set('maxDate', maxEndDate > today ? today : maxEndDate);
    }
  });

  flatpickr("#endDate", {
    enableTime: true,
    dateFormat: "Y-m-d H:i",
    minDate: maxPastDate,
    maxDate: today,
    onChange: function(selectedDates, dateStr, instance) {
      const startDatePicker = document.getElementById('startDate')._flatpickr;
      const minStartDate = new Date(selectedDates[0]);
      minStartDate.setDate(minStartDate.getDate() - maxDaysApart);
      startDatePicker.set('maxDate', dateStr);
      startDatePicker.set('minDate', minStartDate < maxPastDate ? maxPastDate : minStartDate);
    }
  });
});

function toast(title,note,body,theme,delay = "8000") {
  $('#toastContainer').append(`
      <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="`+delay+`">
        <div class="toast-header">
          <img class="bg-`+theme+` p-2 rounded-2">&nbsp;
          <strong class="me-auto">`+title+`</strong>
          <small class="text-muted">`+note+`</small>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          `+body+`
        </div>
      </div>
  `);
  $('.toast').toast('show').on('hidden.bs.toast', function (elem) {
    $(elem.target).remove();
  })
};

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
  $.get('/api?function=getReportProgress&id='+id, function(data) {
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

  if(!$('#APIKey')[0].value){
    toast("Error","Missing Required Fields","The API Key is a required field.","danger","30000");
    return null;
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
  $.get( "api?function=getUUID", function( id ) {
    showLoading(id);
    const startDateTime = new Date($('#startDate')[0].value)
    const endDateTime = new Date($('#endDate')[0].value)
    $.post( "api?function=createReport", {
      APIKey: $('#APIKey')[0].value,
      StartDateTime: startDateTime.toISOString(),
      EndDateTime: endDateTime.toISOString(),
      Realm: $('#Realm').find(":selected").val(),
      id: id
    }).done(function( data, status ) {
      if (data['Status'] == 'Success') {
        toast("Success","","The report has been successfully generated.","success","30000");
        download('/api?function=downloadReport&id='+data['id'])
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
