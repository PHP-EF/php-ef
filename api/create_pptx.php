<?php
$SkipCSS = true;
require_once(__DIR__.'/../scripts/inc/inc.php');
use Label305\PptxExtractor\Basic\BasicExtractor;
use Label305\PptxExtractor\Basic\BasicInjector;

function array_search_partial($keyword,$arr) {
    foreach($arr as $index => $string) {
        if (strpos($string, $keyword) !== FALSE)
            return $index;
    }
}

function replaceTag($Mapping,$TagName,$Value) {
    $TAG = array_search_partial($TagName,$Mapping);
    if ($TAG) {
        $Mapping[$TAG] = str_replace($TagName, $Value, $Mapping[$TAG]);
    }
    return $Mapping;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $Rand = rand();

    // Extract Powerpoint Template
    $extractor = new BasicExtractor();
    $mapping = $extractor->extractStringsAndCreateMappingFile(
        __DIR__.'/../files/template-sept-24.pptx',
        __DIR__.'/../files/reports/report-'.$Rand.'-extracted.pptx'
    );

    // Debug
    // file_put_contents(__DIR__.'/../files/template-arr.txt', var_export($mapping, true));

    // TODO - Time Dimension - Needs input from WebUI
    $StartDimension = '2024-09-15T22:00:00.000';
    $EndDimension = '2024-10-15T21:59:59.000';

    ##// Slide 2 - Title Page
    // Get & Inject Customer Name
    $AccountInfo = QueryCSP("get","v2/current_user/accounts");
    $mapping = replaceTag($mapping,'#TAG01',$AccountInfo->results[0]->name);

    ##// Slide 5 - Executive Summary
    $mapping = replaceTag($mapping,'#TAG02',"TBC"); // High-Risk Events
    $mapping = replaceTag($mapping,'#TAG03',"TBC"); // High-Risk Websites
    $mapping = replaceTag($mapping,'#TAG04',"TBC"); // Data Exfil / Tunneling
    $mapping = replaceTag($mapping,'#TAG05',"TBC"); // Lookalike Domains
    $mapping = replaceTag($mapping,'#TAG06',"TBC"); // Zero Day DNS
    $mapping = replaceTag($mapping,'#TAG07',"TBC"); // Suspicious Domains


    ##// Slide 6 - Security Indicator Summary
    $mapping = replaceTag($mapping,'#TAG08',"TBC"); // DNS Requests
    $mapping = replaceTag($mapping,'#TAG09',"TBC"); // High-Risk Events
    $mapping = replaceTag($mapping,'#TAG10',"TBC"); // Medium-Risk Events
    $mapping = replaceTag($mapping,'#TAG11',"TBC"); // Insights
    $mapping = replaceTag($mapping,'#TAG12',"TBC"); // Custom Lookalike Domains
    $mapping = replaceTag($mapping,'#TAG13',"TBC"); // DoH
    $mapping = replaceTag($mapping,'#TAG14',"TBC"); // Zero Day DNS
    $mapping = replaceTag($mapping,'#TAG15',"TBC"); // Suspicious Domains
    $mapping = replaceTag($mapping,'#TAG16',"TBC"); // Newly Observed Domains
    $mapping = replaceTag($mapping,'#TAG17',"TBC"); // Domain Generated Algorithms
    $mapping = replaceTag($mapping,'#TAG18',"TBC"); // DNS Tunnelling
    $mapping = replaceTag($mapping,'#TAG19',"TBC"); // Unique Applications
    $mapping = replaceTag($mapping,'#TAG20',"TBC"); // High-Risk Web Categories
    $mapping = replaceTag($mapping,'#TAG21',"TBC"); // Threat Actors

    ##// Slide 9 - Traffic Usage Analysis
    // Total DNS Activity
    $DNSActivity = QueryCubeJS('{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["2024-09-17T00:00:00.000","2024-10-16T23:59:59.000"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["1"]}],"limit":"1","ungrouped":false}');
    $mapping = replaceTag($mapping,'#TAG22',number_abbr($DNSActivity->result->data[0]->{'PortunusAggInsight.requests'}));
    // DNS Firewall Activity
    $DNSFirewallActivity = QueryCubeJS('{"measures":["PortunusAggSecurity.severityCount"],"dimensions":["PortunusAggSecurity.severity"],"timeDimensions":[{"dimension":"PortunusAggSecurity.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggSecurity.type","operator":"equals","values":["2","3"]},{"member":"PortunusAggSecurity.severity","operator":"equals","values":["High","Medium","Low"]}],"limit":"3","ungrouped":false}');
    $HighId = array_search('High', array_column($DNSFirewallActivity->result->data, 'PortunusAggSecurity.severity'));
    $HighEventsCount = $DNSFirewallActivity->result->data[$HighId]->{'PortunusAggSecurity.severityCount'};
    $MediumId = array_search('Medium', array_column($DNSFirewallActivity->result->data, 'PortunusAggSecurity.severity'));
    $MediumEventsCount = $DNSFirewallActivity->result->data[$MediumId]->{'PortunusAggSecurity.severityCount'};
    $LowId = array_search('Low', array_column($DNSFirewallActivity->result->data, 'PortunusAggSecurity.severity'));
    $LowEventsCount = $DNSFirewallActivity->result->data[$LowId]->{'PortunusAggSecurity.severityCount'};
    $HML = $HighEventsCount+$MediumEventsCount+$LowEventsCount;
    $HMLP = 100 / $HML;
    $HighPerc = $HighEventsCount * $HMLP;
    $MediumPerc = $MediumEventsCount * $HMLP;
    $LowPerc = $LowEventsCount * $HMLP;
    $mapping = replaceTag($mapping,'#TAG23',number_abbr($HML)); // Total
    $mapping = replaceTag($mapping,'#TAG24',number_abbr($HighEventsCount)); // High Int
    $mapping = replaceTag($mapping,'#TAG25',number_format($HighPerc,2).'%'); // High Percent
    $mapping = replaceTag($mapping,'#TAG26',number_abbr($MediumEventsCount)); // Medium Int
    $mapping = replaceTag($mapping,'#TAG27',number_format($MediumPerc,2).'%'); // Medium Percent
    $mapping = replaceTag($mapping,'#TAG28',number_abbr($LowEventsCount)); // Low Int
    $mapping = replaceTag($mapping,'#TAG29',number_format($LowPerc,2).'%'); // Low Percent
    // Threat Activity
    $ThreatActivityEvents = QueryCubeJS('{"measures":["PortunusAggInsight.threatCount"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2"]},{"member":"PortunusAggInsight.severity","operator":"equals","values":["High","Medium","Low"]},{"member":"PortunusAggInsight.threat_indicator","operator":"notEquals","values":[""]}],"limit":"1","ungrouped":false}');
    $mapping = replaceTag($mapping,'#TAG30',number_abbr($ThreatActivityEvents->result->data[0]->{'PortunusAggInsight.threatCount'}));
    // Data Exfiltration Incidents
    $DataExfilEvents = QueryCubeJS('{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["4"]},{"member":"PortunusAggInsight.tclass","operator":"equals","values":["TI-DNST"]}],"ungrouped":false}');
    $mapping = replaceTag($mapping,'#TAG31',number_abbr($DataExfilEvents->result->data[0]->{'PortunusAggInsight.requests'}));

    ##// Slide 15 - Key Insights
    // Insight Severity
    $mapping = replaceTag($mapping,'#TAG32',"TBC"); // Total Open Insights
    $mapping = replaceTag($mapping,'#TAG33',"TBC"); // Medium Priority Insights
    $mapping = replaceTag($mapping,'#TAG34',"TBC"); // High Priority Insights
    $mapping = replaceTag($mapping,'#TAG35',"TBC"); // Critical Priority Insights
    // Event To Insight Aggregation
    $mapping = replaceTag($mapping,'#TAG36',"TBC"); // Events
    $mapping = replaceTag($mapping,'#TAG37',"TBC"); // Key Insights


    ##// Slide 24 - Lookalike Domains
    $LookalikeDomainCounts = QueryCSP("get","/api/atcfw/v1/lookalike_domain_counts");
    $mapping = replaceTag($mapping,'#TAG38',number_abbr($LookalikeDomainCounts->results->count_total)); // Total Lookalikes
    $mapping = replaceTag($mapping,'#TAG39',number_abbr($LookalikeDomainCounts->results->percentage_increase_total)); // Total Percentage Increase
    $mapping = replaceTag($mapping,'#TAG40',number_abbr($LookalikeDomainCounts->results->count_custom)); // Total Lookalikes from Custom Watched Domains
    $mapping = replaceTag($mapping,'#TAG41',number_abbr($LookalikeDomainCounts->results->percentage_increase_custom)); // Custom Percentage Increase
    $mapping = replaceTag($mapping,'#TAG42',number_abbr($LookalikeDomainCounts->results->count_threats)); // Threats from Custom Watched Domains
    $mapping = replaceTag($mapping,'#TAG43',number_abbr($LookalikeDomainCounts->results->percentage_increase_threats)); // Threats Percentage Increase

    ##// Slide 28 - Security Activities
    // Security Events
    $SecurityEvents = QueryCubeJS('{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"contains","values":["2","3"]}],"limit":"1","ungrouped":false}');
    $mapping = replaceTag($mapping,'#TAG44',number_abbr($SecurityEvents->result->data[0]->{'PortunusAggInsight.requests'}));
    // DNS Firewall
    $DNSFirewall = QueryCubeJS('{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"and":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2"]},{"or":[{"member":"PortunusAggInsight.severity","operator":"equals","values":["High","Medium","Low"]},{"and":[{"member":"PortunusAggInsight.severity","operator":"equals","values":["Info"]},{"member":"PortunusAggInsight.policy_action","operator":"equals","values":["Block","Log"]}]}]},{"member":"PortunusAggInsight.confidence","operator":"equals","values":["High","Medium","Low"]}]}],"limit":"1","ungrouped":false}');
    $mapping = replaceTag($mapping,'#TAG45',number_abbr($DNSFirewall->result->data[0]->{'PortunusAggInsight.requests'}));
    // Web Content
    $WebContent = QueryCubeJS('{"measures":["PortunusAggWebcontent.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggWebcontent.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggWebcontent.type","operator":"equals","values":["3"]},{"member":"PortunusAggWebcontent.category","operator":"notEquals","values":[null]}],"limit":"1","ungrouped":false}');
    $mapping = replaceTag($mapping,'#TAG46',number_abbr($WebContent->result->data[0]->{'PortunusAggWebcontent.requests'}));
    // Devices
    $Devices = QueryCubeJS('{"measures":["PortunusAggInsight.deviceCount"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"contains","values":["2","3"]},{"member":"PortunusAggInsight.severity","operator":"contains","values":["High","Medium","Low"]}],"limit":"1","ungrouped":false}');
    $mapping = replaceTag($mapping,'#TAG47',number_abbr($Devices->result->data[0]->{'PortunusAggInsight.deviceCount'}));
    // Users
    $Users = QueryCubeJS('{"measures":["PortunusAggInsight.userCount"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"contains","values":["2","3"]}],"limit":"1","ungrouped":false}');
    $mapping = replaceTag($mapping,'#TAG48',number_abbr($Users->result->data[0]->{'PortunusAggInsight.userCount'}));

    // Insights
    $mapping = replaceTag($mapping,'#TAG49',"TBC");

    // Threat Insight
    $ThreatInsight = QueryCubeJS('{"measures":[],"dimensions":["PortunusDnsLogs.tproperty"],"timeDimensions":[{"dimension":"PortunusDnsLogs.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusDnsLogs.type","operator":"equals","values":["4"]}],"limit":"10000","ungrouped":false}');
    $mapping = replaceTag($mapping,'#TAG50',number_abbr(count($ThreatInsight->result->data)));
    // Threat View
    $ThreatView = QueryCubeJS('{"measures":["PortunusAggInsight.tpropertyCount"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2"]}],"limit":"1","ungrouped":false}');
    $mapping = replaceTag($mapping,'#TAG51',number_abbr($ThreatView->result->data[0]->{'PortunusAggInsight.tpropertyCount'}));
    // Sources
    $Sources = QueryCubeJS('{"measures":["PortunusAggSecurity.networkCount"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggSecurity.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggSecurity.type","operator":"contains","values":["2","3"]}],"limit":"1","ungrouped":false}');
    $mapping = replaceTag($mapping,'#TAG52',number_abbr($Sources->result->data[0]->{'PortunusAggSecurity.networkCount'}));

    // Rebuild Powerpoint
    $injector = new BasicInjector();
    $injector->injectMappingAndCreateNewFile(
        $mapping,
        __DIR__.'/../files/reports/report-'.$Rand.'-extracted.pptx',
        __DIR__.'/../files/reports/report-'.$Rand.'.pptx'
    );

    // Cleanup
    unlink(__DIR__.'/../files/reports/report-'.$Rand.'-extracted.pptx');

    ## Generate Response
    $response = array(
        'Status' => 'Success',
        'Path' => '/files/reports/report-'.$Rand.'.pptx'
    );
    $responseJSON = json_encode($response);
    header('Content-Type: application/json; charset=utf-8');
    echo $responseJSON;
}