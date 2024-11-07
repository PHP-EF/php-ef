<?php
function QueryCSP($Method, $Uri, $Data = "", $APIKey = "", $Realm = "US") {
  if (isset($_COOKIE['crypt'])) {
    $B1ApiKey = decrypt($_COOKIE['crypt'],getConfig("Security","salt"));
  } elseif (isset($_POST['APIKey'])) {
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
    'timeout' => getConfig("System","CURL-Timeout"),
    'connect_timeout' => getConfig("System","CURL-ConnectTimeout")
  );

  try {
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
  } catch (Exception $e) {
    return array(
      'Status' => 'Error',
      'Error' => $e->getMessage()
    );
  }

  $LogArr = array(
    "Method" => $Method,
    "Url" => $Url,
    "Options" => $Options
  );

  if ($Result) {
    switch ($Result->status_code) {
      case '401':
        $LogArr['Error'] = "Invalid API Key.";
        writeLog("CSP","Failed to authenticate to the CSP","debug",$LogArr);
        return array("Status" => "Error", "Error" => "Invalid API Key.");
        break;
      default:
        $Output = json_decode($Result->body);
        writeLog("CSP","Queried the CSP","debug",$LogArr);
        return $Output;
        break;
    }
  } elseif ($ErrorOnEmpty) {
    echo "Warning. No results from API.".$Url;
  }
}

function QueryCubeJS($Query,$APIKey = "", $Realm = "US") {
  $BuildQuery = urlencode($Query);
  $Result = QueryCSP("get","api/cubejs/v1/query?query=".$BuildQuery,null,$APIKey,$Realm);
  return $Result;
}

function GetCSPCurrentUser($APIKey = "") {
  $UserInfo = QueryCSP("get","v2/current_user",null,$APIKey);
  return $UserInfo;
}

function GetB1ThreatActors($StartDateTime,$EndDateTime,$APIKey = "", $Realm = "US") {
  $StartDimension = str_replace('Z','',$StartDateTime);
  $EndDimension = str_replace('Z','',$EndDateTime);
  $Actors = QueryCubeJS('{"segments":[],"timeDimensions":[{"dimension":"PortunusAggIPSummary.timestamp","granularity":null,"dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"ungrouped":false,"order":{"PortunusAggIPSummary.timestampMax":"desc"},"measures":["PortunusAggIPSummary.count"],"dimensions":["PortunusAggIPSummary.threat_indicator","PortunusAggIPSummary.actor_id"],"limit":1000,"filters":[{"and":[{"operator":"set","member":"PortunusAggIPSummary.threat_indicator"},{"operator":"set","member":"PortunusAggIPSummary.actor_id"}]}]}');
  if (isset($Actors->result->data)) {
    return $Actors->result->data;
  } else {
    return $Actors;
  }
}

function GetB1ThreatActorsById($Actors) {
  $UniqueIds = array_unique(array_column($Actors, 'PortunusAggIPSummary.actor_id'));
  $Results = array();
  $ActorInfo = array();
  foreach ($UniqueIds as $UniqueId) {
    $Ids = array();
    $Ids[] = array_keys(array_column($Actors, 'PortunusAggIPSummary.actor_id'),$UniqueId);
    $Indicators = array();
    foreach ($Ids as $Id) {
      foreach ($Id as $Idsub) {
        $Indicators[] = $Actors[$Idsub]->{'PortunusAggIPSummary.threat_indicator'};
      }
    }
    $ActorInfo[] = array(
      "actor_id" => $Actors[$Id[0]]->{'PortunusAggIPSummary.actor_id'},
      "indicators" => $Indicators
    );
  }
  $ArrayChunk = array_chunk($ActorInfo, 10);
  foreach ($ArrayChunk as $Chunk) {
    $Query = array(
      'actor_indicators' => $Chunk
    );
    $Cube = QueryCSP('post','tide-ng-threat-actor/v1/batch_actor_summary_with_indicators',$Query);
    if (isset($Cube->actor_responses)) {
      foreach ($Cube->actor_responses as $AR) {
        array_push($Results,$AR);
      }
    } else {
      return $Cube;
    }
  }
  return $Results;
}