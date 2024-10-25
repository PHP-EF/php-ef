<?php
function QueryCSP($Method, $Uri, $Data = "", $APIKey = "", $Realm = "US") {
  if (isset($_POST['APIKey'])) {
    $B1ApiKey = $_POST['APIKey'];
  } else {
    $B1ApiKey = $APIKey;
  }

  $CSPHeaders = array(
  'Authorization' => "Token $B1ApiKey",
  'Content-Type' => "application/json"
  );

  $ErrorOnEmpty = true;

  if (isset($_POST['Realm'])) {
    $Realm = $_POST['Realm'];
  }

  if (strpos($Uri,"https://csp.") === FALSE) {
    if ($Realm == "US") {
      $Url = "https://csp.infoblox.com/".$Uri;
    } elseif ($Realm == "EU") {
      $Url = "https://csp.eu.infoblox.com/".$Uri;
    } else {
      echo 'Error. Invalid Realm';
      return false;
    }
  } else {
    $Url = $Uri;
  }

  $Options = array(
    'timeout' => 60,
    'connect_timeout' => 60
  );

  switch ($Method) {
    case 'get':
      $Result = WpOrg\Requests\Requests::get($Url, $CSPHeaders, $Options);
      break;
    case 'post':
      $Result = WpOrg\Requests\Requests::post($Url, $CSPHeaders, json_encode($Data,JSON_UNESCAPED_SLASHES), $Options);
      break;
    case 'put':
      $Result = WpOrg\Requests\Requests::put($Url, $CSPHeaders, json_encode($Data,JSON_UNESCAPED_SLASHES), $Options);
      break;
    case 'patch':
      $Result = WpOrg\Requests\Requests::patch($Url, $CSPHeaders, json_encode($Data,JSON_UNESCAPED_SLASHES), $Options);
      break;
    case 'delete':
      $Result = WpOrg\Requests\Requests::delete($Url, $CSPHeaders, $Options);
      break;
  }
  if ($Result) {
    $Output = json_decode($Result->body);
    return $Output;
  } elseif ($ErrorOnEmpty) {
    echo "Warning. No results from API.".$Url;
  }
}

function QueryCubeJS($Query,$APIKey = "", $Realm = "US") {
  $BuildQuery = urlencode($Query);
  $Result = QueryCSP("get","/api/cubejs/v1/query?query=".$BuildQuery,null,$APIKey,$Realm);
  return $Result;
}