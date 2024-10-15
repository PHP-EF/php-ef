<?php
  require_once '../scripts/inc/inc.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infoblox Security Assessment Report Generator</title>
    <style>
        body {
            background-color: #f8f9fa; /* Light grey background */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .input-container {
            text-align: center;
        }
        .input-box {
            width: 300px;
            padding: 10px;
            border: 2px solid #007bff; /* Infoblox blue */
            border-radius: 5px;
        }
        .loading-icon {
            display: none;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="input-container">
        <input id="APIKey" type="password" class="input-box" placeholder="Enter API Key Here">
        <input id="Generate" type="submit" value="Generate Report">
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
  $.post( "api/create_pptx.php", { APIKey: $('#APIKey')[0].value}).done(function( data ) {
    console.log(data['Path'])
    download(data['Path'])
    hideLoading()
  });
});
</script>
