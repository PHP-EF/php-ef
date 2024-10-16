<?php
  require_once(__DIR__.'/scripts/inc/inc.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infoblox Security Assessment Report Generator</title>
    <style>
        body {
            background-color: #1a1a1a; /* Infoblox Dark background color */
            color: #ffffff; /* Infoblox Dark text color */
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            text-align: center;
            background-color: #333333; /* Slightly lighter dark color for contrast */
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        input, button {
            margin: 10px 0;
            padding: 10px;
            border: none;
            border-radius: 5px;
        }
        input {
            width: 80%;
        }
        button {
            background-color: #0078d4; /* Infoblox blue color */
            color: #ffffff;
            cursor: pointer;
        }
        button:hover {
            background-color: #005a9e; /* Darker blue on hover */
        }
        .loading-icon {
            display: none;
            margin-top: 10px;
        }
        input[type="datetime-local"] {
            width: 200px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Infoblox Report Generator</h1>
        <input id="APIKey" type="password" placeholder="Enter API Key">
        <br>
        <input type="datetime-local" id="startDate" placeholder="Start Date/Time">
        <input type="datetime-local" id="endDate" placeholder="End Date/Time">
        <br>
        <button id="Generate">Generate Report</button>
        <div class="loading-icon">
          <hr>
          <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
          </div>
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
