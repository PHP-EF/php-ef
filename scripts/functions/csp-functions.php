<?php
function QueryCSP($Method, $Uri, $Data = "") {
  $B1ApiKey = $_POST['APIKey'];

  $CSPHeaders = array(
  'Authorization' => "Token $B1ApiKey",
  'Content-Type' => "application/json"
  );

  $ErrorOnEmpty = true;

  $Realm = $_POST['Realm'];

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
    'timeout' => 30,
    'connect_timeout' => 30
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

function QueryCubeJS($Query) {
  $BuildQuery = urlencode($Query);
  $Result = QueryCSP("get","/api/cubejs/v1/query?query=".$BuildQuery);
  return $Result;
}