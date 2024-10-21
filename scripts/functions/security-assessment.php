<?php
use Label305\PptxExtractor\Basic\BasicExtractor;
use Label305\PptxExtractor\Basic\BasicInjector;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

function generateSecurityReport($APIKey,$StartDateTime,$EndDateTime,$Realm,$UUID) {
    // Check API Key is valid & get User Info
    $UserInfo = QueryCSP("get","v2/current_user");
    if ($UserInfo) {
        $Progress = 0;
        // Set Time Dimensions
        $StartDimension = str_replace('Z','',$StartDateTime);
        $EndDimension = str_replace('Z','',$EndDateTime);

        // Set Directory
        $FilesDir = __DIR__.'/../../files';

        // Extract Powerpoint Template Strings
        // ** Using external library to save re-writing the string replacement functions manually. Will probably pull this in as native code at some point.
        $extractor = new BasicExtractor();
        $mapping = $extractor->extractStringsAndCreateMappingFile(
            $FilesDir.'/template-sept-24.pptx',
            $FilesDir.'/reports/report-'.$UUID.'-extracted.pptx'
        );
        $Progress = writeProgress($UUID,$Progress);

        // Extract Powerpoint Template Zip
        extractZip($FilesDir.'/reports/report-'.$UUID.'-extracted.pptx',$FilesDir.'/reports/report-'.$UUID);
        $Progress = writeProgress($UUID,$Progress);
        //
        // Do Chart, Spreadsheet & Image Stuff Here ....
        // Top threat feeds
        $TopThreatFeeds = QueryCubeJS('{"measures":["PortunusAggSecurity.feednameCount"],"dimensions":["PortunusAggSecurity.feed_name"],"timeDimensions":[{"dimension":"PortunusAggSecurity.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggSecurity.type","operator":"equals","values":["2"]},{"member":"PortunusAggSecurity.severity","operator":"equals","values":["High"]}],"limit":"10","ungrouped":false}');
        $TopThreatFeedsSS = IOFactory::load($FilesDir.'/reports/report-'.$UUID.'/ppt/embeddings/Microsoft_Excel_Worksheet.xlsx');
        $RowNo = 2;
        foreach ($TopThreatFeeds->result->data as $TopThreatFeed) {
            $TopThreatFeedsS = $TopThreatFeedsSS->getActiveSheet();
            $TopThreatFeedsS->setCellValue('A'.$RowNo, $TopThreatFeed->{'PortunusAggSecurity.feed_name'});
            $TopThreatFeedsS->setCellValue('B'.$RowNo, $TopThreatFeed->{'PortunusAggSecurity.feednameCount'});
            $RowNo++;
        }
        $TopThreatFeedsW = IOFactory::createWriter($TopThreatFeedsSS, 'Xlsx');
        $TopThreatFeedsW->save($FilesDir.'/reports/report-'.$UUID.'/ppt/embeddings/Microsoft_Excel_Worksheet.xlsx');
        $Progress = writeProgress($UUID,$Progress);

        // Top detected properties
        $TopDetectedProperties = QueryCubeJS('{"measures":["PortunusDnsLogs.tpropertyCount"],"dimensions":["PortunusDnsLogs.tproperty"],"timeDimensions":[{"dimension":"PortunusDnsLogs.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusDnsLogs.type","operator":"equals","values":["2"]},{"member":"PortunusDnsLogs.feed_name","operator":"notEquals","values":["Public_DOH","public-doh","Public_DOH_IP","public-doh-ip"]},{"member":"PortunusDnsLogs.severity","operator":"notEquals","values":["Low","Info"]}],"limit":"10","ungrouped":false}');
        $TopDetectedPropertiesSS = IOFactory::load($FilesDir.'/reports/report-'.$UUID.'/ppt/embeddings/Microsoft_Excel_Worksheet1.xlsx');
        $RowNo = 2;
        foreach ($TopDetectedProperties->result->data as $TopDetectedProperty) {
            $TopDetectedPropertiesS = $TopDetectedPropertiesSS->getActiveSheet();
            $TopDetectedPropertiesS->setCellValue('A'.$RowNo, $TopDetectedProperty->{'PortunusDnsLogs.tproperty'});
            $TopDetectedPropertiesS->setCellValue('B'.$RowNo, $TopDetectedProperty->{'PortunusDnsLogs.tpropertyCount'});
            $RowNo++;
        }
        $TopDetectedPropertiesW = IOFactory::createWriter($TopDetectedPropertiesSS, 'Xlsx');
        $TopDetectedPropertiesW->save($FilesDir.'/reports/report-'.$UUID.'/ppt/embeddings/Microsoft_Excel_Worksheet1.xlsx');
        $Progress = writeProgress($UUID,$Progress);

        // Content filtration
        $ContentFiltration = QueryCubeJS('{"measures":["PortunusAggWebcontent.categoryCount"],"dimensions":["PortunusAggWebcontent.category"],"timeDimensions":[{"dimension":"PortunusAggWebcontent.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[],"limit":"10","ungrouped":false}');
        $ContentFiltrationSS = IOFactory::load($FilesDir.'/reports/report-'.$UUID.'/ppt/embeddings/Microsoft_Excel_Worksheet2.xlsx');
        $RowNo = 2;
        foreach ($ContentFiltration->result->data as $ContentFilter) {
            $ContentFiltrationS = $ContentFiltrationSS->getActiveSheet();
            $ContentFiltrationS->setCellValue('A'.$RowNo, $ContentFilter->{'PortunusAggWebcontent.category'});
            $ContentFiltrationS->setCellValue('B'.$RowNo, $ContentFilter->{'PortunusAggWebcontent.categoryCount'});
            $RowNo++;
        }
        $ContentFiltrationW = IOFactory::createWriter($ContentFiltrationSS, 'Xlsx');
        $ContentFiltrationW->save($FilesDir.'/reports/report-'.$UUID.'/ppt/embeddings/Microsoft_Excel_Worksheet2.xlsx');
        $Progress = writeProgress($UUID,$Progress);

        // Insight Distribution by Threat Type - Sheet 3
        $InsightDistribution = QueryCubeJS('{"measures":["InsightsAggregated.count"],"dimensions":["InsightsAggregated.threatType"],"filters":[{"member":"InsightsAggregated.insightStatus","operator":"equals","values":["Active"]}]}');
        $InsightDistributionSS = IOFactory::load($FilesDir.'/reports/report-'.$UUID.'/ppt/embeddings/Microsoft_Excel_Worksheet3.xlsx');
        $RowNo = 2;
        foreach ($InsightDistribution->result->data as $InsightThreatType) {
            $InsightDistributionS = $InsightDistributionSS->getActiveSheet();
            $InsightDistributionS->setCellValue('A'.$RowNo, $InsightThreatType->{'InsightsAggregated.threatType'});
            $InsightDistributionS->setCellValue('B'.$RowNo, $InsightThreatType->{'InsightsAggregated.count'});
            $RowNo++;
        }
        $InsightDistributionW = IOFactory::createWriter($InsightDistributionSS, 'Xlsx');
        $InsightDistributionW->save($FilesDir.'/reports/report-'.$UUID.'/ppt/embeddings/Microsoft_Excel_Worksheet3.xlsx');
        $Progress = writeProgress($UUID,$Progress);

        // Threat Types (Lookalikes) - Sheet 4
        $LookalikeThreatCountUri = urlencode('/api/atclad/v1/lookalike_threat_counts?_filter=detected_at>="'.$StartDimension.'" and detected_at<="'.$EndDimension.'"');
        $LookalikeThreatCounts = QueryCSP("get",$LookalikeThreatCountUri);
        $LookalikeThreatCountsSS = IOFactory::load($FilesDir.'/reports/report-'.$UUID.'/ppt/embeddings/Microsoft_Excel_Worksheet4.xlsx');
        $LookalikeThreatCountsS = $LookalikeThreatCountsSS->getActiveSheet();
        $RowNo = 2;
        if (isset($LookalikeThreatCounts->results->suspicious_count)) {
            $LookalikeThreatCountsS->setCellValue('A'.$RowNo, 'Suspicious');
            $LookalikeThreatCountsS->setCellValue('B'.$RowNo, $LookalikeThreatCounts->results->suspicious_count);
            $RowNo++;
        }
        if (isset($LookalikeThreatCounts->results->malware_count)) {
            $LookalikeThreatCountsS->setCellValue('A'.$RowNo, 'Malware');
            $LookalikeThreatCountsS->setCellValue('B'.$RowNo, $LookalikeThreatCounts->results->malware_count);
            $RowNo++;
        }
        if (isset($LookalikeThreatCounts->results->phishing_count)) {
            $LookalikeThreatCountsS->setCellValue('A'.$RowNo, 'Phishing');
            $LookalikeThreatCountsS->setCellValue('B'.$RowNo, $LookalikeThreatCounts->results->phishing_count);
            $RowNo++;
        }
        if (isset($LookalikeThreatCounts->results->others_count)) {
            $LookalikeThreatCountsS->setCellValue('A'.$RowNo, 'Others');
            $LookalikeThreatCountsS->setCellValue('B'.$RowNo, $LookalikeThreatCounts->results->others_count);
            $RowNo++;
        }
        $LookalikeThreatCountsW = IOFactory::createWriter($LookalikeThreatCountsSS, 'Xlsx');
        $LookalikeThreatCountsW->save($FilesDir.'/reports/report-'.$UUID.'/ppt/embeddings/Microsoft_Excel_Worksheet4.xlsx');
        //
        $Progress = writeProgress($UUID,$Progress);

        // Rebuild Powerpoint Template Zip
        compressZip($FilesDir.'/reports/report-'.$UUID.'-extracted.pptx',$FilesDir.'/reports/report-'.$UUID);
        $Progress = writeProgress($UUID,$Progress);

        // Cleanup Extracted Zip
        rmdirRecursive($FilesDir.'/reports/report-'.$UUID);
        $Progress = writeProgress($UUID,$Progress);

        // ** Reusable Metrics ** //
        // DNS Firewall Activity - Used on Slides 2, 5 & 6
        $DNSFirewallActivity = QueryCubeJS('{"measures":["PortunusAggSecurity.severityCount"],"dimensions":["PortunusAggSecurity.severity"],"timeDimensions":[{"dimension":"PortunusAggSecurity.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggSecurity.type","operator":"equals","values":["2","3"]},{"member":"PortunusAggSecurity.severity","operator":"equals","values":["High","Medium","Low"]}],"limit":"3","ungrouped":false}');
        if (isset($DNSFirewallActivity->result)) {
            $HighId = array_search('High', array_column($DNSFirewallActivity->result->data, 'PortunusAggSecurity.severity'));
            $MediumId = array_search('Medium', array_column($DNSFirewallActivity->result->data, 'PortunusAggSecurity.severity'));
            $LowId = array_search('Low', array_column($DNSFirewallActivity->result->data, 'PortunusAggSecurity.severity'));
            if ($HighId !== false) {$HighEventsCount = $DNSFirewallActivity->result->data[$HighId]->{'PortunusAggSecurity.severityCount'};} else {$HighEventsCount = 0;}
            if ($MediumId !== false) {$MediumEventsCount = $DNSFirewallActivity->result->data[$MediumId]->{'PortunusAggSecurity.severityCount'};} else {$MediumEventsCount = 0;}
            if ($LowId !== false) {$LowEventsCount = $DNSFirewallActivity->result->data[$LowId]->{'PortunusAggSecurity.severityCount'};} else {$LowEventsCount = 0;}
        } else {
            $HighEventsCount = 0;
            $MediumEventsCount = 0;
            $LowEventsCount = 0;
        }

        $HML = $HighEventsCount+$MediumEventsCount+$LowEventsCount;
        if ($HML > 0) {
            $HMLP = 100 / $HML;
        } else {
            $HMLP = 0;
        }
        $HighPerc = $HighEventsCount * $HMLP;
        $MediumPerc = $MediumEventsCount * $HMLP;
        $LowPerc = $LowEventsCount * $HMLP;
        $Progress = writeProgress($UUID,$Progress);

        // Total DNS Activity - Used on Slides 6 & 9
        $DNSActivity = QueryCubeJS('{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["1"]}],"limit":"1","ungrouped":false}');
        $Progress = writeProgress($UUID,$Progress);

        // Lookalike Domains - Used on Slides 5, 6 & 24
        $LookalikeDomainCounts = QueryCSP("get","/api/atcfw/v1/lookalike_domain_counts");
        if (isset($LookalikeDomainCounts->results->count_total)) { $LookalikeTotalCount = $LookalikeDomainCounts->results->count_total; } else { $LookalikeTotalCount = 0; }
        if (isset($LookalikeDomainCounts->results->percentage_increase_total)) { $LookalikeTotalPercentage = $LookalikeDomainCounts->results->percentage_increase_total; } else { $LookalikeTotalPercentage = 0; }
        if (isset($LookalikeDomainCounts->results->count_custom)) { $LookalikeCustomCount = $LookalikeDomainCounts->results->count_custom; } else { $LookalikeCustomCount = 0; }
        if (isset($LookalikeDomainCounts->results->percentage_increase_custom)) { $LookalikeCustomPercentage = $LookalikeDomainCounts->results->percentage_increase_custom; } else { $LookalikeCustomPercentage = 0; }
        if (isset($LookalikeDomainCounts->results->count_threats)) { $LookalikeThreatCount = $LookalikeDomainCounts->results->count_threats; } else { $LookalikeThreatCount = 0; }
        if (isset($LookalikeDomainCounts->results->percentage_increase_threats)) { $LookalikeThreatPercentage = $LookalikeDomainCounts->results->percentage_increase_threats; } else { $LookalikeThreatPercentage = 0; }
        $Progress = writeProgress($UUID,$Progress);

        // SOC Insights - Used on Slides 15 & 28
        $SOCInsights = QueryCubeJS('{"measures":["InsightsAggregated.count","InsightsAggregated.mostRecentAt","InsightsAggregated.startedAtMin"],"dimensions":["InsightsAggregated.priorityText"],"filters":[{"member":"InsightsAggregated.insightStatus","operator":"equals","values":["Active"]}],"timezone":"UTC"}');
        if (isset($SOCInsights->result)) {
            $InfoInsightsId = array_search('INFO', array_column($SOCInsights->result->data, 'InsightsAggregated.priorityText'));
            $LowInsightsId = array_search('LOW', array_column($SOCInsights->result->data, 'InsightsAggregated.priorityText'));
            $MediumInsightsId = array_search('MEDIUM', array_column($SOCInsights->result->data, 'InsightsAggregated.priorityText'));
            $HighInsightsId = array_search('HIGH', array_column($SOCInsights->result->data, 'InsightsAggregated.priorityText'));
            $CriticalInsightsId = array_search('CRITICAL', array_column($SOCInsights->result->data, 'InsightsAggregated.priorityText'));
            $TotalInsights = number_abbr(array_sum(array_column($SOCInsights->result->data, 'InsightsAggregated.count')));
        } else {
            $TotalInsights = 0;
        }
        if (isset($InfoInsightsId) AND $InfoInsightsId !== false) {$InfoInsights = $SOCInsights->result->data[$InfoInsightsId]->{'InsightsAggregated.count'};} else {$InfoInsights = 0;}
        if (isset($LowInsightsId) AND $LowInsightsId !== false) {$LowInsights = $SOCInsights->result->data[$LowInsightsId]->{'InsightsAggregated.count'};} else {$LowInsights = 0;}
        if (isset($MediumInsightsId) AND $MediumInsightsId !== false) {$MediumInsights = $SOCInsights->result->data[$MediumInsightsId]->{'InsightsAggregated.count'};} else {$MediumInsights = 0;}
        if (isset($HighInsightsId) AND $HighInsightsId !== false) {$HighInsights = $SOCInsights->result->data[$HighInsightsId]->{'InsightsAggregated.count'};} else {$HighInsights = 0;}
        if (isset($CriticalInsightsId) AND $CriticalInsightsId !== false) {$CriticalInsights = $SOCInsights->result->data[$CriticalInsightsId]->{'InsightsAggregated.count'};} else {$CriticalInsights = 0;}
        $Progress = writeProgress($UUID,$Progress);

        // Security Activity
        $SecurityEvents = QueryCubeJS('{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"contains","values":["2","3"]}],"limit":"1","ungrouped":false}');
        $Progress = writeProgress($UUID,$Progress);

        // Data Exfiltration Events
        $DataExfilEvents = QueryCubeJS('{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["4"]},{"member":"PortunusAggInsight.tclass","operator":"equals","values":["TI-DNST"]}],"ungrouped":false}');
        $Progress = writeProgress($UUID,$Progress);

        // Zero Day DNS Events
        $ZeroDayDNSEvents = QueryCubeJS('{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2","3"]},{"member":"PortunusAggInsight.tclass","operator":"equals","values":["Zero Day DNS"]}],"ungrouped":false}');
        $Progress = writeProgress($UUID,$Progress);

        // Suspicious Domains
        $Suspicious = QueryCubeJS('{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2"]},{"member":"PortunusAggInsight.tclass","operator":"equals","values":["Suspicious"]}],"ungrouped":false}');
        $Progress = writeProgress($UUID,$Progress);

        // High Risk Websites
        $HighRiskWebsites = QueryCubeJS('{"timeDimensions":[{"dimension":"PortunusAggWebContentDiscovery.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"measures":["PortunusAggWebContentDiscovery.count","PortunusAggWebContentDiscovery.deviceCount"],"dimensions":["PortunusAggWebContentDiscovery.domain_category"],"order":{"PortunusAggWebContentDiscovery.count":"desc"},"filters":[{"member":"PortunusAggWebContentDiscovery.domain_category","operator":"equals","values":["Risky Activity","Suspicious and Malicious Software","Uncategorized","Adult","Abortion","Abortion Pro Choice","Abortion Pro Life","Child Inappropriate","Gambling","Gay","Lingerie","Nudity","Pornography","Profanity","R-Rated","Sex & Erotic","Sex Education","Tobacco","Anonymizer","Criminal Skills","Self Harm","Criminal Activities - Other","Illegal Drugs","Marijuana","Child Abuse Images","Hacking","Hate Speech","Piracy & Copyright Theft","Torrent Repository","Terrorism","Peer-to-Peer","Violence","Weapons","School Cheating","Ad Fraud","Botnet","Command and Control Centers","Compromised & Links To Malware","Malware Call-Home","Malware Distribution Point","Phishing/Fraud","Spam URLs","Spyware & Questionable Software","Cryptocurrency Mining","Sexuality","Parked & For Sale Domains"]}]}');
        $Progress = writeProgress($UUID,$Progress);

        // ** ** //

        ##// Slide 2 / 45 - Title Page & Contact Page
        // Get & Inject Customer Name, Contact Name & Email
        $AccountInfo = QueryCSP("get","v2/current_user/accounts");
        $CurrentAccount = $AccountInfo->results[array_search($UserInfo->result->account_id, array_column($AccountInfo->results, 'id'))];
        $mapping = replaceTag($mapping,'#TAG01',$CurrentAccount->name);
        $mapping = replaceTag($mapping,'#DATE',date("dS F Y"));
        $mapping = replaceTag($mapping,'#NAME',$UserInfo->result->name);
        $mapping = replaceTag($mapping,'#EMAIL',$UserInfo->result->email);
        $Progress = writeProgress($UUID,$Progress);

        ##// Slide 5 - Executive Summary
        $mapping = replaceTag($mapping,'#TAG02',number_abbr($HighEventsCount)); // High-Risk Events
        $mapping = replaceTag($mapping,'#TAG03',number_abbr(array_sum(array_column($HighRiskWebsites->result->data, 'PortunusAggWebContentDiscovery.count')))); // High-Risk Websites
        $mapping = replaceTag($mapping,'#TAG04',number_abbr($DataExfilEvents->result->data[0]->{'PortunusAggInsight.requests'})); // Data Exfil / Tunneling
        $mapping = replaceTag($mapping,'#TAG05',number_abbr($LookalikeThreatCount)); // Lookalike Domains
        $mapping = replaceTag($mapping,'#TAG06',number_abbr($ZeroDayDNSEvents->result->data[0]->{'PortunusAggInsight.requests'})); // Zero Day DNS
        $mapping = replaceTag($mapping,'#TAG07',number_abbr($Suspicious->result->data[0]->{'PortunusAggInsight.requests'})); // Suspicious Domains
        $Progress = writeProgress($UUID,$Progress);

        ##// Slide 6 - Security Indicator Summary
        $mapping = replaceTag($mapping,'#TAG08',number_abbr($DNSActivity->result->data[0]->{'PortunusAggInsight.requests'})); // DNS Requests
        $mapping = replaceTag($mapping,'#TAG09',number_abbr($HighEventsCount)); // High-Risk Events
        $mapping = replaceTag($mapping,'#TAG10',number_abbr($MediumEventsCount)); // Medium-Risk Events
        $mapping = replaceTag($mapping,'#TAG11',number_abbr($TotalInsights)); // Insights
        $mapping = replaceTag($mapping,'#TAG12',number_abbr($LookalikeThreatCount)); // Custom Lookalike Domains
        $DOH = QueryCubeJS('{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2"]},{"member":"PortunusAggInsight.tproperty","operator":"equals","values":["DoHService"]}],"ungrouped":false}');
        $mapping = replaceTag($mapping,'#TAG13',number_abbr($DOH->result->data[0]->{'PortunusAggInsight.requests'})); // DoH
        $mapping = replaceTag($mapping,'#TAG14',number_abbr($ZeroDayDNSEvents->result->data[0]->{'PortunusAggInsight.requests'})); // Zero Day DNS
        $mapping = replaceTag($mapping,'#TAG15',number_abbr($Suspicious->result->data[0]->{'PortunusAggInsight.requests'})); // Suspicious Domains
        $NOD = QueryCubeJS('{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2"]},{"member":"PortunusAggInsight.tproperty","operator":"equals","values":["NewlyObservedDomains"]}],"ungrouped":false}');
        $mapping = replaceTag($mapping,'#TAG16',number_abbr($NOD->result->data[0]->{'PortunusAggInsight.requests'})); // Newly Observed Domains
        $DGAs = QueryCubeJS('{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2"]},{"member":"PortunusAggInsight.tclass","operator":"equals","values":["DGA"]}],"ungrouped":false}');
        $mapping = replaceTag($mapping,'#TAG17',number_abbr($DGAs->result->data[0]->{'PortunusAggInsight.requests'})); // Domain Generated Algorithms
        $mapping = replaceTag($mapping,'#TAG18',number_abbr($DataExfilEvents->result->data[0]->{'PortunusAggInsight.requests'})); // DNS Tunnelling
        $UniqueApplications = QueryCubeJS('{"measures":["PortunusAggAppDiscovery.requests"],"dimensions":["PortunusAggAppDiscovery.app_name","PortunusAggAppDiscovery.app_approval"],"timeDimensions":[{"dimension":"PortunusAggAppDiscovery.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggAppDiscovery.app_name","operator":"set"},{"member":"PortunusAggAppDiscovery.app_name","operator":"notEquals","values":[""]}],"order":{}}');
        $mapping = replaceTag($mapping,'#TAG19',number_abbr(count($UniqueApplications->result->data))); // Unique Applications
        $mapping = replaceTag($mapping,'#TAG20',count($HighRiskWebsites->result->data)); // High-Risk Web Categories
        $ThreatActors = QueryCubeJS('{"timeDimensions":[{"dateRange":["'.$StartDimension.'","'.$EndDimension.'"],"dimension":"PortunusAggIPSummary.timestamp","granularity":null}],"measures":["PortunusAggIPSummary.count"],"dimensions":["PortunusAggIPSummary.threat_indicator","PortunusAggIPSummary.actor_id"],"filters":[{"and":[{"member":"PortunusAggIPSummary.threat_indicator","operator":"set"},{"member":"PortunusAggIPSummary.actor_id","operator":"set"}]}],"order":{"PortunusAggIPSummary.timestampMax":"desc"}}');
        $mapping = replaceTag($mapping,'#TAG21',number_abbr(count(array_unique(array_column($ThreatActors->result->data, 'PortunusAggIPSummary.actor_id'))))); // Threat Actors
        $Progress = writeProgress($UUID,$Progress);

        ##// Slide 9 - Traffic Usage Analysis
        // Total DNS Activity
        $mapping = replaceTag($mapping,'#TAG22',number_abbr($DNSActivity->result->data[0]->{'PortunusAggInsight.requests'}));
        // DNS Firewall Activity
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
        $mapping = replaceTag($mapping,'#TAG31',number_abbr($DataExfilEvents->result->data[0]->{'PortunusAggInsight.requests'}));
        $Progress = writeProgress($UUID,$Progress);

        ##// Slide 15 - Key Insights
        // Insight Severity
        $mapping = replaceTag($mapping,'#TAG32',number_abbr($TotalInsights)); // Total Open Insights
        $mapping = replaceTag($mapping,'#TAG33',number_abbr($MediumInsights)); // Medium Priority Insights
        $mapping = replaceTag($mapping,'#TAG34',number_abbr($HighInsights)); // High Priority Insights
        $mapping = replaceTag($mapping,'#TAG35',number_abbr($CriticalInsights)); // Critical Priority Insights
        // Event To Insight Aggregation
        $mapping = replaceTag($mapping,'#TAG36',number_abbr($SecurityEvents->result->data[0]->{'PortunusAggInsight.requests'})); // Events
        $mapping = replaceTag($mapping,'#TAG37',number_abbr($TotalInsights)); // Key Insights
        $Progress = writeProgress($UUID,$Progress);

        ##// Slide 24 - Lookalike Domains
        $mapping = replaceTag($mapping,'#TAG38',number_abbr($LookalikeTotalCount)); // Total Lookalikes
        if ($LookalikeTotalPercentage >= 0){$arrow='↑';} else {$arrow='↓';}
        $mapping = replaceTag($mapping,'#TAG39',$arrow); // Arrow Up/Down
        $mapping = replaceTag($mapping,'#TAG40',number_abbr($LookalikeTotalPercentage)); // Total Percentage Increase
        $mapping = replaceTag($mapping,'#TAG41',number_abbr($LookalikeCustomCount)); // Total Lookalikes from Custom Watched Domains
        if ($LookalikeCustomPercentage >= 0){$arrow='↑';} else {$arrow='↓';}
        $mapping = replaceTag($mapping,'#TAG42',$arrow); // Arrow Up/Down
        $mapping = replaceTag($mapping,'#TAG43',number_abbr($LookalikeCustomPercentage)); // Custom Percentage Increase
        $mapping = replaceTag($mapping,'#TAG44',number_abbr($LookalikeThreatCount)); // Threats from Custom Watched Domains
        if ($LookalikeThreatPercentage >= 0){$arrow='↑';} else {$arrow='↓';}
        $mapping = replaceTag($mapping,'#TAG45',$arrow); // Arrow Up/Down
        $mapping = replaceTag($mapping,'#TAG46',number_abbr($LookalikeThreatPercentage)); // Threats Percentage Increase
        $Progress = writeProgress($UUID,$Progress);

        ##// Slide 28 - Security Activities
        // Security Events
        $mapping = replaceTag($mapping,'#TAG47',number_abbr($SecurityEvents->result->data[0]->{'PortunusAggInsight.requests'}));
        // DNS Firewall
        $DNSFirewall = QueryCubeJS('{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"and":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2"]},{"or":[{"member":"PortunusAggInsight.severity","operator":"equals","values":["High","Medium","Low"]},{"and":[{"member":"PortunusAggInsight.severity","operator":"equals","values":["Info"]},{"member":"PortunusAggInsight.policy_action","operator":"equals","values":["Block","Log"]}]}]},{"member":"PortunusAggInsight.confidence","operator":"equals","values":["High","Medium","Low"]}]}],"limit":"1","ungrouped":false}');
        $mapping = replaceTag($mapping,'#TAG48',number_abbr($DNSFirewall->result->data[0]->{'PortunusAggInsight.requests'}));
        $Progress = writeProgress($UUID,$Progress);
        // Web Content
        $WebContent = QueryCubeJS('{"measures":["PortunusAggWebcontent.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggWebcontent.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggWebcontent.type","operator":"equals","values":["3"]},{"member":"PortunusAggWebcontent.category","operator":"notEquals","values":[null]}],"limit":"1","ungrouped":false}');
        $mapping = replaceTag($mapping,'#TAG49',number_abbr($WebContent->result->data[0]->{'PortunusAggWebcontent.requests'}));
        $Progress = writeProgress($UUID,$Progress);
        // Devices
        $Devices = QueryCubeJS('{"measures":["PortunusAggInsight.deviceCount"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"contains","values":["2","3"]},{"member":"PortunusAggInsight.severity","operator":"contains","values":["High","Medium","Low"]}],"limit":"1","ungrouped":false}');
        $mapping = replaceTag($mapping,'#TAG50',number_abbr($Devices->result->data[0]->{'PortunusAggInsight.deviceCount'}));
        $Progress = writeProgress($UUID,$Progress);
        // Users
        $Users = QueryCubeJS('{"measures":["PortunusAggInsight.userCount"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"contains","values":["2","3"]}],"limit":"1","ungrouped":false}');
        $mapping = replaceTag($mapping,'#TAG51',number_abbr($Users->result->data[0]->{'PortunusAggInsight.userCount'}));
        $Progress = writeProgress($UUID,$Progress);
        // Insights
        $mapping = replaceTag($mapping,'#TAG52',number_abbr($TotalInsights));
        // Threat Insight
        $ThreatInsight = QueryCubeJS('{"measures":[],"dimensions":["PortunusDnsLogs.tproperty"],"timeDimensions":[{"dimension":"PortunusDnsLogs.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusDnsLogs.type","operator":"equals","values":["4"]}],"limit":"10000","ungrouped":false}');
        $mapping = replaceTag($mapping,'#TAG53',number_abbr(count($ThreatInsight->result->data)));
        $Progress = writeProgress($UUID,$Progress);
        // Threat View
        $ThreatView = QueryCubeJS('{"measures":["PortunusAggInsight.tpropertyCount"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2"]}],"limit":"1","ungrouped":false}');
        $mapping = replaceTag($mapping,'#TAG54',number_abbr($ThreatView->result->data[0]->{'PortunusAggInsight.tpropertyCount'}));
        $Progress = writeProgress($UUID,$Progress);
        // Sources
        $Sources = QueryCubeJS('{"measures":["PortunusAggSecurity.networkCount"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggSecurity.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggSecurity.type","operator":"contains","values":["2","3"]}],"limit":"1","ungrouped":false}');
        $mapping = replaceTag($mapping,'#TAG55',number_abbr($Sources->result->data[0]->{'PortunusAggSecurity.networkCount'}));
        $Progress = writeProgress($UUID,$Progress);

        // Rebuild Powerpoint
        // ** Using external library to save re-writing the string replacement functions manually. Will probably pull this in as native code at some point.
        $injector = new BasicInjector();
        $injector->injectMappingAndCreateNewFile(
            $mapping,
            $FilesDir.'/reports/report-'.$UUID.'-extracted.pptx',
            $FilesDir.'/reports/report-'.$UUID.'.pptx'
        );
        $Progress = writeProgress($UUID,$Progress);

        // Cleanup
        unlink($FilesDir.'/reports/report-'.$UUID.'-extracted.pptx');
        $Progress = writeProgress($UUID,$Progress);

        $Status = 'Success';
    } else {
        $Status = 'API Error';
        $Error = 'Invalid API Key.';
    }

    ## Generate Response
    $response = array(
        'Status' => $Status,
    );
    if (isset($Error)) {
        $response['Error'] = $Error;
    } else {
        $response['id'] = $UUID;
    }
    return $response;
}

function writeProgress($id,$Count) {
    $Count++;
    $myfile = fopen(__DIR__.'/../../files/reports/report-'.$id.'.progress', "w") or die("Unable to save progress file");
    fwrite($myfile, $Count);
    fclose($myfile);
    return $Count;
}

function getProgress($id,$Total) {
    $myfile = fopen(__DIR__.'/../../files/reports/report-'.$id.'.progress', "r") or die("0");
    $Current = fread($myfile,filesize(__DIR__.'/../../files/reports/report-'.$id.'.progress'));
    return (100 / $Total) * $Current;
}

function getReportFiles() {
    $files = array_diff(scandir(__DIR__.'/../../files/reports/'),array('.', '..','placeholder.txt'));
    return $files;
  }