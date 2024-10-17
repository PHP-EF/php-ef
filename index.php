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
          <input id="APIKey" type="password" placeholder="Enter API Key">
          <br>
          <input type="datetime-local" id="startDate" placeholder="Start Date/Time">
          <input type="datetime-local" id="endDate" placeholder="End Date/Time">
          <br>
          <div class="alert alert-info" role="alert">
            It can take up to 2 minutes to generate the report. Please be patient and do not click Generate again until it has completed.
          </div>
          <button id="Generate">Generate Report</button>
          <div class="loading-icon">
            <hr>
            <div class="spinner-border text-primary" role="status">
              <span class="sr-only">Loading...</span>
            </div>
          </div>
      </div>
      <div class="footnote">
        <a href="https://github.com/TehMuffinMoo"><i class="fab fa-github fa-lg"></i> &copy; 2024 Mat Cox.</a>
      </div>
    </div>
</body>
</html>

<script>
function download(url) {
  const a = document.createElement('a')
  a.href = url
  console.log(url)
  a.download = url.split('/').pop()
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
}

function showLoading() {
  document.querySelector('.loading-icon').style.display = 'block';
}
function hideLoading() {
  document.querySelector('.loading-icon').style.display = 'none';
}

$("#Generate").click(function(){
  showLoading()
  const startDateTime = new Date($('#startDate')[0].value)
  const endDateTime = new Date($('#endDate')[0].value)
  $.post( "api/create_pptx.php", {
    APIKey: $('#APIKey')[0].value,
    StartDateTime: startDateTime.toISOString(),
    EndDateTime: endDateTime.toISOString()
  }).done(function( data ) {
    download(data['Path'])
    hideLoading()
  });
});
</script>
