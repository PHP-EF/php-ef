<?php
function QueryCSP($Method, $Uri, $Data = "") {
  $B1ApiKey = $_POST['APIKey'];

  $CSPHeaders = array(
  'Authorization' => "Token $B1ApiKey",
  'Content-Type' => "application/json"
  );

  $ErrorOnEmpty = true;

  if (strpos($Uri,"https://csp.infoblox.com/") === FALSE) {
    $Url = "https://csp.infoblox.com/".$Uri;
  } else {
    $Url = $Uri;
  }

  $Options = array(
    'timeout' => 30,
    'connect_timeout' => 30
  );

  // if (getConfig("System","proxyurl") != "") {
  //   $Options['proxy'] = getConfig("System","proxyurl");
  // }

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