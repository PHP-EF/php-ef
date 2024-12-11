<?php
  require_once(__DIR__.'/../../inc/inc.php');
  if ($ib->rbac->checkAccess("B1-LICENSE-USAGE") == false) {
    die();
  }
?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

	<title>License Usage</title>

	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
  <meta name="viewport" content="width=device-width" />
</head>
<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-12 col-xl-12 mx-auto">
        <h2 class="h3 mb-4 page-title">License Usage</h2>

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
              <button class="btn btn-success" id="Generate">Get Usage</button>
            </div>
        </div>
        <br>
        <div class="alert alert-info genInfo" role="alert">
          <center>It can take up to 2 minutes to generate the License Usage, please be patient.</center>
        </div>
        <div class="calendar"></div>
            <div class="loading-icon">
            <hr>
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <div class="card-group license-card-group">
          <div class="card">
            <div class="card-header">DNS</div>
            <div class="card-body">
              <!-- <h5 class="card-title">DNS</h5> -->
              <p class="card-text" id="ip_unique_dns"></p>
              <p class="card-text"><small class="text-muted">This is the number of unique DNS IPs.</small></p>
            </div>
          </div>
          <div class="card">
            <div class="card-header">DHCP</div>
            <!-- <img class="card-img-top" src="..." alt="Card image cap"> -->
            <div class="card-body">
              <!-- <h5 class="card-title">DHCP</h5> -->
              <p class="card-text" id="ip_unique_dhcp"></p>
              <p class="card-text"><small class="text-muted">This is the number of unique DHCP IPs.</small></p>
            </div>
          </div>
          <div class="card">
            <div class="card-header">DFP</div>
            <!-- <img class="card-img-top" src="..." alt="Card image cap"> -->
            <div class="card-body">
              <!-- <h5 class="card-title">DFP</h5> -->
              <p class="card-text" id="ip_unique_dfp"></p>
              <p class="card-text"><small class="text-muted">This is the number of unique DFP IPs.</small></p>
            </div>
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
}
function hideLoading() {
  document.querySelector('.loading-icon').style.display = 'none';
}

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
    const startDateTime = new Date($('#startDate')[0].value)
    const endDateTime = new Date($('#endDate')[0].value)
    showLoading();
      var postArr = {}
      postArr.StartDateTime = startDateTime.toISOString()
      postArr.EndDateTime = endDateTime.toISOString()
      postArr.Realm = $('#Realm').find(":selected").val()
      if ($('#APIKey')[0].value) {
        postArr.APIKey = $('#APIKey')[0].value
      }
      $.post( "/api?f=createLicenseReport", postArr).done(function( data, status ) {
        if (data['Status'] == 'Error') {
          toast(data['Status'],"",data['Error'],"danger","30000");
        } else {
          $('#ip_unique_dns').text(data['Unique']['DNS'])
          $('#ip_unique_dhcp').text(data['Unique']['DHCP'])
          $('#ip_unique_dfp').text(data['Unique']['DFP'])
          toast("Success","","License Usage has been successfully generated.","success","30000");
        }
    }).fail(function( data, status ) {
        toast("API Error","","Unknown API Error","danger","30000");
    }).always(function() {
        hideLoading()
        $("#Generate").prop('disabled', false)
    });
});
</script>