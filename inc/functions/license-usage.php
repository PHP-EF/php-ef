<?php

function getLicenseCount2($StartDateTime,$EndDateTime,$Realm) {
    // Set Time Dimensions
    $StartDimension = str_replace('Z','',$StartDateTime);
    $EndDimension = str_replace('Z','',$EndDateTime);
    $SpacesWithDNSData = QueryCubeJS('{"segments":[],"dimensions":["NstarDnsActivity.ip_space_id"],"ungrouped":false,"measures":["NstarDnsActivity.total_query_count"],"timeDimensions":[{"dateRange":["'.$StartDimension.'","'.$EndDimension.'"],"dimension":"NstarDnsActivity.timestamp","granularity":null}]}');
    $CSPRequests = [];
    $CubeJSRequests = [];
    $CSPRequests[] = QueryCSPMultiRequestBuilder("get",'api/infra/v1/detail_hosts?_limit=10001&_fields=id,display_name,ip_space,site_id',null,'uddi_hosts'); // Collect list of UDDI Hosts
    $Responses = QueryCSPMulti($CSPRequests);
    $Hosts = $Responses['uddi_hosts']['Body']->results;
    // return json_decode(json_encode($Hosts),false);
    $filtered_hosts = array_values(array_filter(json_decode(json_encode($Hosts),true), function($item) {
        return array_key_exists('ip_space', $item);
    }));
    foreach ($SpacesWithDNSData->result->data as $SpaceWithDNSData) {
        if ($SpaceWithDNSData->{'NstarDnsActivity.ip_space_id'} != '') {
            $Space = 'ipam/ip_space/'.$SpaceWithDNSData->{'NstarDnsActivity.ip_space_id'};
            $SiteIds = [];
            $HostsWithThisSpace = array_keys(array_column($filtered_hosts,'ip_space'),$Space);
            foreach ($HostsWithThisSpace as $HostWithThisSpace) {
                $SiteIds[] = $filtered_hosts[$HostWithThisSpace]['site_id'];
            }
            if (count($SiteIds) > 0) {
                $Query = json_encode(array(
                    "dimensions" => [
                        "NstarDnsActivity.device_ip"
                    ],
                    "ungrouped" => false,
                    "timeDimensions" => [
                        array(
                            "dateRange" => [
                                $StartDimension,
                                $EndDimension
                            ],
                            "dimension" => "NstarDnsActivity.timestamp",
                            "granularity" => null
                        )
                    ],
                    "measures" => [
                        "NstarDnsActivity.total_query_count"
                    ],
                    "filters" => [
                        array(
                            "member" => "NstartDnsActivity.site_id",
                            "values" => $SiteIds,
                            "operator" => "equals"
                        )
                    ]
                ));
                $CubeJSRequests[$Space] = $Query;
            }   
        }
    }
    // return $CubeJSRequests;
    $CubeJSResults = QueryCubeJSMulti($CubeJSRequests);
    $ResultsArr = array();
    foreach ($CubeJSResults as $CubeJSResultKey => $CubeJSResultVal) {
        $ResultsArr[] = array(
            'IP Space' => $CubeJSResultKey,
            'Count' => count($CubeJSResultVal['Body']->result->data),
            'Data' => $CubeJSResultVal['Body']->result->data
        );
    }
    return $ResultsArr;
}

function getLicenseCount($StartDateTime,$EndDateTime,$Realm) {
    // Set Time Dimensions
    $StartDimension = str_replace('Z','',$StartDateTime);
    $EndDimension = str_replace('Z','',$EndDateTime);

    $moreDNSIPs = true;
    $moreDHCPIPs = true;
    $moreDFPIPs = true;
    $offsetDNS = 0;
    $offsetDHCP = 0;
    $offsetDFP = 0;
    $TotalDNSIPCount = 0;
    $TotalDHCPIPCount = 0;
    $TotalDFPIPCount = 0;

    while ($moreDNSIPs) {
        $UniqueDNSIPs = QueryCubeJS('{"measures":["NstarDnsActivity.total_query_count"],"segments":[],"dimensions":["NstarDnsActivity.device_ip","NstarDnsActivity.site_id"],"ungrouped":false,"limit":50000,"offset":'.$offsetDNS.',"timeDimensions":[{"dimension":"NstarDnsActivity.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"],"granularity":null}]}');
        if (isset($UniqueDNSIPs->result->data)) {
            $DNSIPCount = count($UniqueDNSIPs->result->data);
            if ($DNSIPCount == 50000) {
                $offsetDNS += 50000;
            } else {
                $moreDNSIPs = false;
            }
            $TotalDNSIPCount += $DNSIPCount;
        } else if (isset($UniqueDNSIPs['Error'])) {
            return $UniqueDNSIPs;
        } else {
            $TotalDNSIPCount = 0;
            $moreDNSIPs = false;
        }
    }

    while ($moreDHCPIPs) {
        $UniqueDHCPIPs = QueryCubeJS('{"measures":["NstarLeaseActivity.total_count"],"segments":[],"dimensions":["NstarLeaseActivity.lease_ip","NstarLeaseActivity.host_id"],"ungrouped":false,"limit":50000,"offset":'.$offsetDHCP.',"timeDimensions":[{"dimension":"NstarLeaseActivity.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"],"granularity":null}]}');
        if (isset($UniqueDHCPIPs->result->data)) {
            $DHCPIPCount = count($UniqueDHCPIPs->result->data);
            if ($DHCPIPCount == 50000) {
                $offsetDHCP += 50000;
            } else {
                $moreDHCPIPs = false;
            }
            $TotalDHCPIPCount += $DHCPIPCount;
        } else if (isset($UniqueDHCPIPs['Error'])) {
            return $UniqueDHCPIPs;
        }  else {
            $TotalDHCPIPCount = 0;
            $moreDHCPIPs = false;
        }
    }

    while ($moreDFPIPs) {
        $UniqueDFPIPs = QueryCubeJS('{"ungrouped":false,"dimensions":["PortunusAggUserDevices.device_name"],"filters":[{"operator":"equals","member":"PortunusAggUserDevices.type","values":["1"]}],"measures":["PortunusAggUserDevices.deviceCount"],"timeDimensions":[{"granularity":null,"dimension":"PortunusAggUserDevices.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"limit":"50000","offset":'.$offsetDFP.',"segments":[]}');
        if (isset($UniqueDFPIPs->result->data)) {
            $DFPIPCount = count($UniqueDFPIPs->result->data);
            if ($DFPIPCount == 50000) {
                $offsetDFP += 50000;
            } else {
                $moreDFPIPs = false;
            }
            $TotalDFPIPCount += $DFPIPCount;
        } else if (isset($UniqueDFPIPs['Error'])) {
            return $UniqueDFPIPs;
        }  else {
            $TotalDFPIPCount = 0;
            $moreDFPIPs = false;
        }
    }

    return array(
        'Total' => 0,
        'Unique' => array(
            'DFP' => $TotalDFPIPCount,
            'DNS' => $TotalDNSIPCount,
            'DHCP' => $TotalDHCPIPCount,
        )
    );
}