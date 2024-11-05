<?php

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
        } else {
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
        } else {
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