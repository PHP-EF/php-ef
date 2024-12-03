<?php
function GetCSPConfiguration($Uri,$APIKey = "",$Realm = "US") {
  global $ib;
  if (isset($_COOKIE['crypt'])) {
    $B1ApiKey = decrypt($_COOKIE['crypt'],$ib->config->getConfig("Security","salt"));
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

  if ($Uri == null || strpos($Uri,"https://csp.") === FALSE) {
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
    'timeout' => $ib->config->getConfig("System","CURL-Timeout"),
    'connect_timeout' => $ib->config->getConfig("System","CURL-ConnectTimeout")
  );

  return array(
    "APIKey" => $B1ApiKey,
    "Realm" => $Realm,
    "Url" => $Url,
    "Options" => $Options,
    "Headers" => $CSPHeaders
  );
}

function QueryCSPMultiRequestBuilder($Method = 'GET', $Uri = '', $Data = null, $Id = "") {
  return array(
    "Id" => $Id,
    "Method" => $Method,
    "Uri" => $Uri,
    "Data" => $Data
  );
}

function QueryCSPMulti($MultiQuery,$APIKey = "",$Realm = "US") {
  $CSPConfig = GetCSPConfiguration(null,$APIKey,$Realm);

  // Prepare the requests
  $requests = [];
  foreach ($MultiQuery as $Multi) {
    if (isset($Multi['Data'])) { $Data = $Multi['Data']; } else { $Data = null; }
    $requests[] = array(
      "id" => $Multi['Id'],
      "type" => constant('Requests::' . strtoupper($Multi['Method'])),
      "url" => $CSPConfig['Url'].$Multi['Uri'],
      "data" => $Data,
      "headers" => $CSPConfig['Headers'],
      "options" => $CSPConfig['Options']
    );
  }
  // Send the requests simultaneously
  $responses = Requests::request_multiple($requests);

  $Results = [];
  $IdStepIn = 0;
  foreach ($responses as $index => $response) {
    if ($requests[$index]['id'] != "") {
      $Id = $requests[$index]['id'];
    } else {
      $Id = $IdStepIn;
      $IdStepIn++;
    }
    $Results[$Id] = array(
      'Response' => $response,
      'Body' => json_decode($response->body)
    );
  }
  return $Results;
}

function QueryCSP($Method, $Uri, $Data = null, $APIKey = "", $Realm = "US") {
  global $ib;
  $CSPConfig = GetCSPConfiguration($Uri,$APIKey,$Realm);
  try {
    switch ($Method) {
      case 'get':
        $Result = Requests::get($CSPConfig['Url'], $CSPConfig['Headers'], $CSPConfig['Options']);
        break;
      case 'post':
        if ($Data != null) {
          $Result = Requests::post($CSPConfig['Url'], $CSPConfig['Headers'], json_encode($Data,JSON_UNESCAPED_SLASHES), $CSPConfig['Options']);
        } else {
          $Result = Requests::post($CSPConfig['Url'], $CSPConfig['Headers'], $Data, $CSPConfig['Options']);
        }
        break;
      case 'put':
        $Result = Requests::put($CSPConfig['Url'], $CSPConfig['Headers'], json_encode($Data,JSON_UNESCAPED_SLASHES), $CSPConfig['Options']);
        break;
      case 'patch':
        $Result = Requests::patch($CSPConfig['Url'], $CSPConfig['Headers'], json_encode($Data,JSON_UNESCAPED_SLASHES), $CSPConfig['Options']);
        break;
      case 'delete':
        $Result = Requests::delete($CSPConfig['Url'], $CSPConfig['Headers'], $CSPConfig['Options']);
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
    "Url" => $CSPConfig['Url'],
    "Options" => $CSPConfig['Options']
  );

  if ($Result) {
    switch ($Result->status_code) {
      case '401':
        $LogArr['Error'] = "Invalid API Key.";
        $ib->logging->writeLog("CSP","Failed to authenticate to the CSP","debug",$LogArr);
        return array("Status" => "Error", "Error" => "Invalid API Key.");
      default:
        $Output = json_decode($Result->body);
        $ib->logging->writeLog("CSP","Queried the CSP","debug",$LogArr);
        return $Output;
    }
  } elseif ($ErrorOnEmpty) {
    echo "Warning. No results from API.".$CSPConfig->Url;
  }
}

function QueryCubeJSMulti($MultiQuery,$APIKey = "", $Realm = "US") {
  $BuildQuery = [];
  foreach ($MultiQuery as $Id => $Query) {
    $BuildQuery[] = QueryCSPMultiRequestBuilder("get","api/cubejs/v1/query?query=".urlencode($Query),null,$Id);
  }
  $Results = QueryCSPMulti($BuildQuery,$APIKey,$Realm);
  return $Results;
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
  // Workaround
  //$Actors = QueryCubeJS('{"segments":[],"timeDimensions":[{"dimension":"PortunusAggIPSummary.timestamp","granularity":null,"dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"ungrouped":false,"order":{"PortunusAggIPSummary.timestampMax":"desc"},"measures":["PortunusAggIPSummary.count"],"dimensions":["PortunusAggIPSummary.threat_indicator","PortunusAggIPSummary.actor_id"],"limit":1000,"filters":[{"and":[{"operator":"set","member":"PortunusAggIPSummary.threat_indicator"},{"operator":"set","member":"PortunusAggIPSummary.actor_id"}]}]}');
  $Actors = QueryCubeJS('{"measures":[],"segments":[],"dimensions":["ThreatActors.storageid","ThreatActors.ikbactorid","ThreatActors.domain","ThreatActors.ikbfirstsubmittedts","ThreatActors.vtfirstdetectedts","ThreatActors.firstdetectedts","ThreatActors.lastdetectedts"],"timeDimensions":[{"dimension":"ThreatActors.lastdetectedts","granularity":null,"dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"ungrouped":false}');
  if (isset($Actors->result->data)) {
    return $Actors->result->data;
  } else {
    return $Actors;
  }
}

function GetB1ThreatActor($ActorID,$Page,$APIKey = "", $Realm = "US") {
  $Results = QueryCSP('get','/tide/threat-enrichment/clusterfox/actors/search?actor_id='.$ActorID.'&page='.$Page);
  if ($Results) {
    return $Results;
  }
}

// Workaround to new issue
function GetB1ThreatActorsById3($Actors,$unnamed,$substring) {
  $ActorArr = json_decode(json_encode($Actors),true);
  $UniqueIds = array_unique(array_column($ActorArr, 'ThreatActors.ikbactorid'));
  $Results = array();
  $ActorInfo = array();

  $Requests = [];
  $ArrayChunk = array_chunk($UniqueIds, 10);
  $Requests = [];
  foreach ($ArrayChunk as $Chunk) {
    $CsvString = implode(',',$Chunk);
    $Requests[] = QueryCSPMultiRequestBuilder('get','/tide/threat-enrichment/clusterfox/actors/search?actor_id='.$CsvString);
  }
  $Responses = QueryCSPMulti($Requests);
  foreach ($Responses as $Response) {
    foreach ($Response['Body']->actors as $Actor) {
      $ObservedIOCKeys = array_keys(array_column($ActorArr, 'ThreatActors.ikbactorid'),$Actor->actor_id);
      $ObservedIOCCount = count($ObservedIOCKeys);
      $ObservedIOCs = [];
      foreach ($ObservedIOCKeys as $ObservedIOCKey) {
        $ObservedIOCs[] = $ActorArr[$ObservedIOCKey];
      }
      if ($Actor->actor_id != "" && $Actor->actor_name != "") {
        // Ignore Unnamed & Substring Actors
        $UnnamedActor = str_starts_with($Actor->actor_name,'unnamed_actor');
        $SubstringActor = str_starts_with($Actor->actor_name,'unnamed_actor');
        if (($UnnamedActor && $unnamed == 'true') || ($SubstringActor && $substring == 'true') || (!$UnnamedActor && !$SubstringActor)) {
          $NewArr = array(
            'actor_id' => $Actor->actor_id,
            'actor_name' => $Actor->actor_name,
            'actor_description' => $Actor->actor_description,
            'related_count' => $Actor->related_count,
            'observed_count' => $ObservedIOCCount,
            'observed_iocs' => $ObservedIOCs
          );
          if (isset($Actor->external_references)) {
            $NewArr['external_references'] = $Actor->external_references;
          } else {
            $NewArr['external_references'] = [];
          }
          if (isset($Actor->infoblox_references)) {
            $NewArr['infoblox_references'] = $Actor->infoblox_references;
          } else {
            $NewArr['infoblox_references'] = [];
          }
          if (isset($Actor->purpose)) {
            $NewArr['purpose'] = $Actor->purpose;
          } else {
            $NewArr['purpose'] = [];
          }
          if (isset($Actor->ttp)) {
            $NewArr['ttp'] = $Actor->ttp;
          } else {
            $NewArr['ttp'] = [];
          }
          array_push($Results,$NewArr);
        }
      }
    }
  }
  return $Results;
}

function GetB1ThreatActorsById2($Actors) {
  $UniqueIds = array_unique(array_column($Actors, 'PortunusAggIPSummary.actor_id'));
  $Results = array();
  $ActorInfo = array();
  // VirusTotal Indicators
  $VTIndicators = QueryCSP('post','tide-ng-threat-actor/v1/batch_actor_summary_with_indicators');
  foreach ($VTIndicators->actor_responses as $Key => $VTIndicator) {
    if (isset($VTIndicator->actor_id)) {
      if (in_array($VTIndicator->actor_id,$UniqueIds)) {
        $Key = array_search($VTIndicator->actor_id,$UniqueIds);
        unset($UniqueIds[$Key]);
      }
    }
  }
  foreach ($VTIndicators->actor_responses as $AR) {
    if (isset($AR->actor_id) && isset($AR->actor_name) && isset($AR->actor_description) && isset($AR->related_count) && isset($AR->related_indicators_with_dates)) {
      $NewArr = array(
        'actor_id' => $AR->actor_id,
        'actor_name' => $AR->actor_name,
        'actor_description' => $AR->actor_description,
        'related_count' => $AR->related_count,
        'related_indicators_with_dates' => $AR->related_indicators_with_dates,
        'related_indicators' => null,
      );
      if (isset($AR->external_references)) {
        $NewArr['external_references'] = $AR->external_references;
      } else {
        $NewArr['external_references'] = [];
      }
      if (isset($AR->infoblox_references)) {
        $NewArr['infoblox_references'] = $AR->infoblox_references;
      } else {
        $NewArr['infoblox_references'] = [];
      }
      array_push($Results,$NewArr);
    }
  }

  $Requests = [];
  $ArrayChunk = array_chunk($UniqueIds, 10);
  $Requests = [];
  foreach ($ArrayChunk as $Chunk) {
    $CsvString = implode(',',$Chunk);
    $Requests[] = QueryCSPMultiRequestBuilder('get','/tide/threat-enrichment/clusterfox/actors/search?actor_id='.$CsvString);
  }
  $Responses = QueryCSPMulti($Requests);
  foreach ($Responses as $Response) {
    foreach ($Response['Body']->actors as $Actor) {
      $NewArr = array(
        'actor_id' => $Actor->actor_id,
        'actor_name' => $Actor->actor_name,
        'actor_description' => $Actor->actor_description,
        'related_count' => $Actor->related_count,
        'related_indicators_with_dates' => null,
        'related_indicators' => $Actor->related_indicators,
      );
      if (isset($Actor->external_references)) {
        $NewArr['external_references'] = $Actor->external_references;
      } else {
        $NewArr['external_references'] = [];
      }
      if (isset($Actor->infoblox_references)) {
        $NewArr['infoblox_references'] = $Actor->infoblox_references;
      } else {
        $NewArr['infoblox_references'] = [];
      }
      array_push($Results,$NewArr);
    }
  }
  return $Results;
}


function GetB1ThreatActorsById($Actors) {
  $UniqueIds = array_unique(array_column($Actors, 'PortunusAggIPSummary.actor_id'));
  $Results = array();
  $ActorInfo = array();
  foreach ($UniqueIds as $UniqueId) {
    // Workaround for problematic Threat Actors
    // These timeout when using the 'batch_actor_summary_with_indicators' API Endpoint
    $WorkaroundArr = array(
      //'c2303ad0-0f9e-4349-a71e-821794e202bd' // Revolver Rabbit - TIDE-850
    );
    if (in_array($UniqueId, $WorkaroundArr)) {
      $ActorQuery = QueryCSP('get','tide-ng-threat-actor/v1/actor?_filter=id=="'.$UniqueId.'" and page==1');
      if (isset($ActorQuery)) {
        $NewArr = array(
          'actor_id' => $ActorQuery->actor_id,
          'actor_name' => $ActorQuery->actor_name,
          'actor_description' => $ActorQuery->actor_description,
          'related_count' => $ActorQuery->related_count,
          'related_indicators_with_dates' => null,
          'related_indicators' => $ActorQuery->related_indicators,
        );
        if (isset($ActorQuery->external_references)) {
          $NewArr['external_references'] = $ActorQuery->external_references;
        } else {
          $NewArr['external_references'] = [];
        }
        if (isset($ActorQuery->infoblox_references)) {
          $NewArr['infoblox_references'] = $ActorQuery->infoblox_references;
        } else {
          $NewArr['infoblox_references'] = [];
        }
        array_push($Results,$NewArr);
      }
    // End of Workaround
    } else {
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
  }

  $ArrayChunk = array_chunk($ActorInfo, 5);
  $Requests = [];
  foreach ($ArrayChunk as $Chunk) {
    $Query = json_encode(array(
      'actor_indicators' => $Chunk
    ));
    $Requests[] = QueryCSPMultiRequestBuilder('post','tide-ng-threat-actor/v1/batch_actor_summary_with_indicators',$Query);
  }

  $Responses = QueryCSPMulti($Requests);
  foreach ($Responses as $Response) {
    if (isset($Response['Body']->actor_responses)) {
      foreach ($Response['Body']->actor_responses as $AR) {
        $NewArr = array(
          'actor_id' => $AR->actor_id,
          'actor_name' => $AR->actor_name,
          'actor_description' => $AR->actor_description,
          'related_count' => $AR->related_count,
          'related_indicators_with_dates' => $AR->related_indicators_with_dates,
          'related_indicators' => null,
        );
        if (isset($AR->external_references)) {
          $NewArr['external_references'] = $AR->external_references;
        } else {
          $NewArr['external_references'] = [];
        }
        if (isset($AR->infoblox_references)) {
          $NewArr['infoblox_references'] = $AR->infoblox_references;
        } else {
          $NewArr['infoblox_references'] = [];
        }
        array_push($Results,$NewArr);
      }
    }
  }
  return $Results;
}