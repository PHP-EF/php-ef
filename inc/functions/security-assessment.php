<?php
use Label305\PptxExtractor\Basic\BasicExtractor;
use Label305\PptxExtractor\Basic\BasicInjector;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

function generateSecurityReport($StartDateTime,$EndDateTime,$Realm,$UUID) {
    // Check API Key is valid & get User Info
    $UserInfo = GetCSPCurrentUser();
    if (is_array($UserInfo) && isset($UserInfo['Error'])) {
        $Status = $UserInfo['Status'];
        $Error = $UserInfo['Error'];
    } else {
        $Progress = 0;
        // Set Time Dimensions
        $StartDimension = str_replace('Z','',$StartDateTime);
        $EndDimension = str_replace('Z','',$EndDateTime);

        // Set Directories
        $FilesDir = __DIR__.'/../../files';
        $AssetsDir = __DIR__.'/../../assets';

        // Extract Powerpoint Template Zip
        $Progress = writeProgress($UUID,$Progress,"Extracting template");
        extractZip($FilesDir.'/'.getConfig()['SecurityAssessment']['TemplateName'],$FilesDir.'/reports/report-'.$UUID);

        //
        // Do Chart, Spreadsheet & Image Stuff Here ....
        // Top threat feeds
        $Progress = writeProgress($UUID,$Progress,"Getting Threat Feeds");
        $TopThreatFeeds = QueryCubeJS('{"measures":["PortunusAggSecurity.feednameCount"],"dimensions":["PortunusAggSecurity.feed_name"],"timeDimensions":[{"dimension":"PortunusAggSecurity.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggSecurity.type","operator":"equals","values":["2"]},{"member":"PortunusAggSecurity.severity","operator":"equals","values":["High"]}],"limit":"10","ungrouped":false}');
        if (isset($TopThreatFeeds->result->data)) {
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
        }

        // Top detected properties
        $Progress = writeProgress($UUID,$Progress,"Getting Threat Properties");
        $TopDetectedProperties = QueryCubeJS('{"measures":["PortunusDnsLogs.tpropertyCount"],"dimensions":["PortunusDnsLogs.tproperty"],"timeDimensions":[{"dimension":"PortunusDnsLogs.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusDnsLogs.type","operator":"equals","values":["2"]},{"member":"PortunusDnsLogs.feed_name","operator":"notEquals","values":["Public_DOH","public-doh","Public_DOH_IP","public-doh-ip"]},{"member":"PortunusDnsLogs.severity","operator":"notEquals","values":["Low","Info"]}],"limit":"10","ungrouped":false}');
        if (isset($TopDetectedProperties->result->data)) {
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
        }

        // Content filtration
        $Progress = writeProgress($UUID,$Progress,"Getting Content Filters");
        $ContentFiltration = QueryCubeJS('{"measures":["PortunusAggWebcontent.categoryCount"],"dimensions":["PortunusAggWebcontent.category"],"timeDimensions":[{"dimension":"PortunusAggWebcontent.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[],"limit":"10","ungrouped":false}');
        if (isset($ContentFiltration->result->data)) {
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
        }

        // Insight Distribution by Threat Type - Sheet 3
        $Progress = writeProgress($UUID,$Progress,"Getting SOC Insight Threat Types");
        $InsightDistribution = QueryCubeJS('{"measures":["InsightsAggregated.count"],"dimensions":["InsightsAggregated.threatType"],"filters":[{"member":"InsightsAggregated.insightStatus","operator":"equals","values":["Active"]}]}');
        if (isset($InsightDistribution->result->data)) {
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
        }

        // Threat Types (Lookalikes) - Sheet 4
        $Progress = writeProgress($UUID,$Progress,"Getting Lookalike Threats");
        $LookalikeThreatCountUri = urlencode('/api/atclad/v1/lookalike_threat_counts?_filter=detected_at>="'.$StartDimension.'" and detected_at<="'.$EndDimension.'"');
        $LookalikeThreatCounts = QueryCSP("get",$LookalikeThreatCountUri);
        if (isset($LookalikeThreatCounts->results)) {
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
        }

        // ** Reusable Metrics ** //
        // DNS Firewall Activity - Used on Slides 2, 5 & 6
        $Progress = writeProgress($UUID,$Progress,"Getting DNS Firewall Event Criticality");
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

        // Total DNS Activity - Used on Slides 6 & 9
        $Progress = writeProgress($UUID,$Progress,"Getting DNS Activity");
        $DNSActivity = QueryCubeJS('{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["1"]}],"limit":"1","ungrouped":false}');
        if (isset($DNSActivity->result->data[0])) {
            $DNSActivityCount = $DNSActivity->result->data[0]->{'PortunusAggInsight.requests'};
        } else {
            $DNSActivityCount = 0;
        }

        // Lookalike Domains - Used on Slides 5, 6 & 24
        $Progress = writeProgress($UUID,$Progress,"Getting Lookalike Domain Counts");
        $LookalikeDomainCounts = QueryCSP("get","api/atcfw/v1/lookalike_domain_counts");
        if (isset($LookalikeDomainCounts->results->count_total)) { $LookalikeTotalCount = $LookalikeDomainCounts->results->count_total; } else { $LookalikeTotalCount = 0; }
        if (isset($LookalikeDomainCounts->results->percentage_increase_total)) { $LookalikeTotalPercentage = $LookalikeDomainCounts->results->percentage_increase_total; } else { $LookalikeTotalPercentage = 0; }
        if (isset($LookalikeDomainCounts->results->count_custom)) { $LookalikeCustomCount = $LookalikeDomainCounts->results->count_custom; } else { $LookalikeCustomCount = 0; }
        if (isset($LookalikeDomainCounts->results->percentage_increase_custom)) { $LookalikeCustomPercentage = $LookalikeDomainCounts->results->percentage_increase_custom; } else { $LookalikeCustomPercentage = 0; }
        if (isset($LookalikeDomainCounts->results->count_threats)) { $LookalikeThreatCount = $LookalikeDomainCounts->results->count_threats; } else { $LookalikeThreatCount = 0; }
        if (isset($LookalikeDomainCounts->results->percentage_increase_threats)) { $LookalikeThreatPercentage = $LookalikeDomainCounts->results->percentage_increase_threats; } else { $LookalikeThreatPercentage = 0; }

        // SOC Insights - Used on Slides 15 & 28
        $Progress = writeProgress($UUID,$Progress,"Getting SOC Insight Threat Criticality");
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

        // Security Activity
        $Progress = writeProgress($UUID,$Progress,"Getting Security Activity");
        $SecurityEvents = QueryCubeJS('{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"contains","values":["2","3"]}],"limit":"1","ungrouped":false}');
        if (isset($SecurityEvents->result->data[0])) {
            $SecurityEventsCount = $SecurityEvents->result->data[0]->{'PortunusAggInsight.requests'};
        } else {
            $SecurityEventsCount = 0;
        }

        // Data Exfiltration Events
        $Progress = writeProgress($UUID,$Progress,"Getting Data Exfiltration Events");
        $DataExfilEvents = QueryCubeJS('{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["4"]},{"member":"PortunusAggInsight.tclass","operator":"equals","values":["TI-DNST"]}],"ungrouped":false}');
        if (isset($DataExfilEvents->result->data[0])) {
            $DataExfilEventsCount = $DataExfilEvents->result->data[0]->{'PortunusAggInsight.requests'};
        } else {
            $DataExfilEventsCount = 0;
        }

        // Zero Day DNS Events
        $Progress = writeProgress($UUID,$Progress,"Getting Zero Day DNS Events");
        $ZeroDayDNSEvents = QueryCubeJS('{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2","3"]},{"member":"PortunusAggInsight.tclass","operator":"equals","values":["Zero Day DNS"]}],"ungrouped":false}');
        if (isset($ZeroDayDNSEvents->result->data[0])) {
            $ZeroDayDNSEventsCount = $ZeroDayDNSEvents->result->data[0]->{'PortunusAggInsight.requests'};
        } else {
            $ZeroDayDNSEventsCount = 0;
        }

        // Suspicious Domains
        $Progress = writeProgress($UUID,$Progress,"Getting Suspicious Domain Events");
        $SuspiciousEvents = QueryCubeJS('{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2"]},{"member":"PortunusAggInsight.tclass","operator":"equals","values":["Suspicious"]}],"ungrouped":false}');
        if (isset($SuspiciousEvents->result->data[0])) {
            $SuspiciousEventsCount = $SuspiciousEvents->result->data[0]->{'PortunusAggInsight.requests'};
        } else {
            $SuspiciousEventsCount = 0;
        }

        // High Risk Websites
        $Progress = writeProgress($UUID,$Progress,"Getting High Risk Website Events");
        $HighRiskWebsites = QueryCubeJS('{"timeDimensions":[{"dimension":"PortunusAggWebContentDiscovery.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"measures":["PortunusAggWebContentDiscovery.count","PortunusAggWebContentDiscovery.deviceCount"],"dimensions":["PortunusAggWebContentDiscovery.domain_category"],"order":{"PortunusAggWebContentDiscovery.count":"desc"},"filters":[{"member":"PortunusAggWebContentDiscovery.domain_category","operator":"equals","values":["Risky Activity","Suspicious and Malicious Software","Uncategorized","Adult","Abortion","Abortion Pro Choice","Abortion Pro Life","Child Inappropriate","Gambling","Gay","Lingerie","Nudity","Pornography","Profanity","R-Rated","Sex & Erotic","Sex Education","Tobacco","Anonymizer","Criminal Skills","Self Harm","Criminal Activities - Other","Illegal Drugs","Marijuana","Child Abuse Images","Hacking","Hate Speech","Piracy & Copyright Theft","Torrent Repository","Terrorism","Peer-to-Peer","Violence","Weapons","School Cheating","Ad Fraud","Botnet","Command and Control Centers","Compromised & Links To Malware","Malware Call-Home","Malware Distribution Point","Phishing/Fraud","Spam URLs","Spyware & Questionable Software","Cryptocurrency Mining","Sexuality","Parked & For Sale Domains"]}]}');
        if (isset($HighRiskWebsites->result->data)) {
            $HighRiskWebsiteCount = array_sum(array_column($HighRiskWebsites->result->data, 'PortunusAggWebContentDiscovery.count'));
            $HighRiskWebCategoryCount = count($HighRiskWebsites->result->data);
        } else {
            $HighRiskWebsiteCount = 0;
            $HighRiskWebCategoryCount = 0;
        }

        // DNS over HTTPS
        $Progress = writeProgress($UUID,$Progress,"Getting DoH Events");
        $DOHEvents = QueryCubeJS('{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2"]},{"member":"PortunusAggInsight.tproperty","operator":"equals","values":["DoHService"]}],"ungrouped":false}');
        if (isset($DOHEvents->result->data[0])) {
            $DOHEventsCount = $DOHEvents->result->data[0]->{'PortunusAggInsight.requests'};
        } else {
            $DOHEventsCount = 0;
        }

        // Newly Observed Domains
        $Progress = writeProgress($UUID,$Progress,"Getting Newly Observed Domain Events");
        $NODEvents = QueryCubeJS('{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2"]},{"member":"PortunusAggInsight.tproperty","operator":"equals","values":["NewlyObservedDomains"]}],"ungrouped":false}');
        if (isset($NODEvents->result->data[0])) {
            $NODEventsCount = $NODEvents->result->data[0]->{'PortunusAggInsight.requests'};
        } else {
            $NODEventsCount = 0;
        }

        // Domain Generation Algorithms
        $Progress = writeProgress($UUID,$Progress,"Getting DGA Events");
        $DGAEvents = QueryCubeJS('{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"or":[{"member":"PortunusAggInsight.tproperty","operator":"equals","values":["suspicious_rdga","DGA"]},{"member":"PortunusAggInsight.tclass","operator":"equals","values":["DGA","MalwareC2DGA"]}]},{"member":"PortunusAggInsight.type","operator":"equals","values":["2","3"]}],"ungrouped":false}');
        if (isset($DGAEvents->result->data[0])) {
            $DGAEventsCount = $DGAEvents->result->data[0]->{'PortunusAggInsight.requests'};
        } else {
            $DGAEventsCount = 0;
        }

        // Unique Applications
        $Progress = writeProgress($UUID,$Progress,"Getting list of Unique Applications");
        $UniqueApplications = QueryCubeJS('{"measures":["PortunusAggAppDiscovery.requests"],"dimensions":["PortunusAggAppDiscovery.app_name","PortunusAggAppDiscovery.app_approval"],"timeDimensions":[{"dimension":"PortunusAggAppDiscovery.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggAppDiscovery.app_name","operator":"set"},{"member":"PortunusAggAppDiscovery.app_name","operator":"notEquals","values":[""]}],"order":{}}');
        if (isset($UniqueApplications->result->data)) {
            $UniqueApplicationsCount = count($UniqueApplications->result->data);
        } else {
            $UniqueApplicationsCount = 0;
        }

        // Threat Actors Metrics
        $Progress = writeProgress($UUID,$Progress,"Getting Threat Actor Metrics");
        $ThreatActors = GetB1ThreatActors($StartDateTime,$EndDateTime);
        if (isset($ThreatActors)) {
            $Progress = writeProgress($UUID,$Progress,"Getting Threat Actor Information (This may take a moment)");
            $ThreatActorsCount = count(array_unique(array_column($ThreatActors, 'PortunusAggIPSummary.actor_id')));
            $ThreatActorInfo = GetB1ThreatActorsById($ThreatActors);
            if (isset($ThreatActorInfo->error)) {
                $ThreatActorInfo = array();
                $ThreatActorSlideCount = 0;
            } else {
                $ThreatActorSlideCount = count($ThreatActorInfo);
            }
        } else {
            $ThreatActorsCount = 0;
        }

        // Threat Activity
        $Progress = writeProgress($UUID,$Progress,"Getting Threat Activity");
        $ThreatActivityEvents = QueryCubeJS('{"measures":["PortunusAggInsight.threatCount"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2"]},{"member":"PortunusAggInsight.severity","operator":"equals","values":["High","Medium","Low"]},{"member":"PortunusAggInsight.threat_indicator","operator":"notEquals","values":[""]}],"limit":"1","ungrouped":false}');
        if (isset($ThreatActivityEvents->result->data[0])) {
            $ThreatActivityEventsCount = $ThreatActivityEvents->result->data[0]->{'PortunusAggInsight.threatCount'};
        } else {
            $ThreatActivityEventsCount = 0;
        }

        // DNS Firewall
        $Progress = writeProgress($UUID,$Progress,"Getting DNS Firewall Activity");
        $DNSFirewallEvents = QueryCubeJS('{"measures":["PortunusAggInsight.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"and":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2"]},{"or":[{"member":"PortunusAggInsight.severity","operator":"equals","values":["High","Medium","Low"]},{"and":[{"member":"PortunusAggInsight.severity","operator":"equals","values":["Info"]},{"member":"PortunusAggInsight.policy_action","operator":"equals","values":["Block","Log"]}]}]},{"member":"PortunusAggInsight.confidence","operator":"equals","values":["High","Medium","Low"]}]}],"limit":"1","ungrouped":false}');
        if (isset($DNSFirewallEvents->result->data[0])) {
            $DNSFirewallEventsCount = $DNSFirewallEvents->result->data[0]->{'PortunusAggInsight.requests'};
        } else {
            $DNSFirewallEventsCount = 0;
        }

        // Web Content
        $Progress = writeProgress($UUID,$Progress,"Getting Web Content Events");
        $WebContentEvents = QueryCubeJS('{"measures":["PortunusAggWebcontent.requests"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggWebcontent.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggWebcontent.type","operator":"equals","values":["3"]},{"member":"PortunusAggWebcontent.category","operator":"notEquals","values":[null]}],"limit":"1","ungrouped":false}');
        if (isset($WebContentEvents->result->data[0])) {
            $WebContentEventsCount = $WebContentEvents->result->data[0]->{'PortunusAggWebcontent.requests'};
        } else {
            $WebContentEventsCount = 0;
        }

        // Device Count
        $Progress = writeProgress($UUID,$Progress,"Getting Device Count");
        $Devices = QueryCubeJS('{"measures":["PortunusAggInsight.deviceCount"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"contains","values":["2","3"]},{"member":"PortunusAggInsight.severity","operator":"contains","values":["High","Medium","Low"]}],"limit":"1","ungrouped":false}');
        if (isset($Devices->result->data[0])) {
            $DeviceCount = $Devices->result->data[0]->{'PortunusAggInsight.deviceCount'};
        } else {
            $DeviceCount = 0;
        }

        // User Count
        $Progress = writeProgress($UUID,$Progress,"Getting User Count");
        $Users = QueryCubeJS('{"measures":["PortunusAggInsight.userCount"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"contains","values":["2","3"]}],"limit":"1","ungrouped":false}');
        if (isset($Users->result->data[0])) {
            $UserCount = $Users->result->data[0]->{'PortunusAggInsight.userCount'};
        } else {
            $UserCount = 0;
        }

        // Threat Insight Count
        $Progress = writeProgress($UUID,$Progress,"Getting Threat Insight Count");
        $ThreatInsight = QueryCubeJS('{"measures":[],"dimensions":["PortunusDnsLogs.tproperty"],"timeDimensions":[{"dimension":"PortunusDnsLogs.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusDnsLogs.type","operator":"equals","values":["4"]}],"limit":"10000","ungrouped":false}'); // Threat Insight
        if (isset($ThreatInsight->result->data)) {
            $ThreatInsightCount = count($ThreatInsight->result->data);
        } else {
            $ThreatInsightCount = 0;
        }

        // Threat View Count
        $Progress = writeProgress($UUID,$Progress,"Getting Threat View Count");
        $ThreatView = QueryCubeJS('{"measures":["PortunusAggInsight.tpropertyCount"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggInsight.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggInsight.type","operator":"equals","values":["2"]}],"limit":"1","ungrouped":false}');
        if (isset($ThreatView->result->data[0])) {
            $ThreatViewCount = $ThreatView->result->data[0]->{'PortunusAggInsight.tpropertyCount'};
        } else {
            $ThreatViewCount = 0;
        }

        // Source Count
        $Progress = writeProgress($UUID,$Progress,"Getting Sources Count");
        $Sources = QueryCubeJS('{"measures":["PortunusAggSecurity.networkCount"],"dimensions":[],"timeDimensions":[{"dimension":"PortunusAggSecurity.timestamp","dateRange":["'.$StartDimension.'","'.$EndDimension.'"]}],"filters":[{"member":"PortunusAggSecurity.type","operator":"contains","values":["2","3"]}],"limit":"1","ungrouped":false}');
        if (isset($Sources->result->data[0])) {
            $SourcesCount = $Sources->result->data[0]->{'PortunusAggSecurity.networkCount'};
        } else {
            $SourcesCount = 0;
        }

        // ** ** //


        //
        // Do Threat Actor Stuff Here ....
        //
        $Progress = writeProgress($UUID,$Progress,"Generating Threat Actor Slides");
        // New slides to be appended after this slide number
        $ThreatActorSlideStart = getConfig()['SecurityAssessment']['ThreatActorSlide'];
        // Calculate the slide position based on above value
        $ThreatActorSlidePosition = $ThreatActorSlideStart-2;

        // Tag Numbers Start
        $TagStart = 100;

        // Open PPTX Presentation _rels XML
        $xml_rels = new DOMDocument('1.0', 'utf-8');
        $xml_rels->formatOutput = true; 
        $xml_rels->preserveWhiteSpace = false;
        $xml_rels->load($FilesDir.'/reports/report-'.$UUID.'/ppt/_rels/presentation.xml.rels');
        $xml_rels_f = $xml_rels->createDocumentFragment();
        $xml_rels_fstart = ($xml_rels->getElementsByTagName('Relationship')->length)+50;

        // Open PPTX Presentation XML
        $xml_pres = new DOMDocument('1.0', 'utf-8');
        $xml_pres->formatOutput = true;
        $xml_pres->preserveWhiteSpace = false;
        $xml_pres->load($FilesDir.'/reports/report-'.$UUID.'/ppt/presentation.xml');
        $xml_pres_f = $xml_pres->createDocumentFragment();
        $xml_pres_fstart = 14700;

        // Get Slide Count
        $SlidesCount = iterator_count(new FilesystemIterator($FilesDir.'/reports/report-'.$UUID.'/ppt/slides'));
        // Set first slide number
        $SlideNumber = $SlidesCount++;

        // Copy Blank Threat Actor Image
        copy($AssetsDir.'/images/Threat Actors/Other/logo-only.png',$FilesDir.'/reports/report-'.$UUID.'/ppt/media/logo-only.png');

        // Build new Threat Actor Slides & Update PPTX Resources
        $KnownActors = getThreatActorConfig();
        foreach  ($ThreatActorInfo as $TAI) {
            if (($ThreatActorSlideCount - 1) > 0) {
                $xml_rels_f->appendXML('<Relationship Id="rId'.$xml_rels_fstart.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slide" Target="slides/slide'.$SlideNumber.'.xml"/>');
                $xml_pres_f->appendXML('<p:sldId id="'.$xml_pres_fstart.'" r:id="rId'.$xml_rels_fstart.'"/>');
    
                $xml_rels_fstart++;
                $xml_pres_fstart++;
    
                copy($FilesDir.'/reports/report-'.$UUID.'/ppt/slides/slide'.$ThreatActorSlideStart.'.xml',$FilesDir.'/reports/report-'.$UUID.'/ppt/slides/slide'.$SlideNumber.'.xml');
                copy($FilesDir.'/reports/report-'.$UUID.'/ppt/slides/_rels/slide'.$ThreatActorSlideStart.'.xml.rels',$FilesDir.'/reports/report-'.$UUID.'/ppt/slides/_rels/slide'.$SlideNumber.'.xml.rels');
            } else {
                $SlideNumber = $ThreatActorSlideStart;
            }

            // Update Tag Numbers
            $TASFile = file_get_contents($FilesDir.'/reports/report-'.$UUID.'/ppt/slides/slide'.$SlideNumber.'.xml');
            $TASFile = str_replace('#TATAG00', '#TATAG'.$TagStart, $TASFile);

            // Add Threat Actor Icon
            $ThreatActorIconString = '<p:pic><p:nvPicPr><p:cNvPr id="36" name="Graphic 35"><a:extLst><a:ext uri="{FF2B5EF4-FFF2-40B4-BE49-F238E27FC236}"><a16:creationId xmlns:a16="http://schemas.microsoft.com/office/drawing/2014/main" id="{898E1A10-3ABF-AED0-2C71-1F26BBB6304B}"/></a:ext></a:extLst></p:cNvPr><p:cNvPicPr><a:picLocks noChangeAspect="1"/></p:cNvPicPr><p:nvPr/></p:nvPicPr><p:blipFill><a:blip r:embed="rId115"><a:extLst><a:ext uri="{96DAC541-7B7A-43D3-8B79-37D633B846F1}"><asvg:svgBlip xmlns:asvg="http://schemas.microsoft.com/office/drawing/2016/SVG/main" r:embed="rId115"/></a:ext></a:extLst></a:blip><a:stretch><a:fillRect/></a:stretch></p:blipFill><p:spPr><a:xfrm><a:off x="5522998" y="2349624"/><a:ext cx="1246722" cy="1582377"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></p:spPr></p:pic></p:spTree>';
            $TASFile = str_replace('</p:spTree>',$ThreatActorIconString,$TASFile);
            
            // Append Virus Total Stuff if applicable to the slide
            if (isset($TAI['related_indicators_with_dates'])) {
                foreach ($TAI['related_indicators_with_dates'] as $TAII) {
                    if (isset($TAII->vt_first_submission_date)) {
                        $TASFileString = '<p:cxnSp><p:nvCxnSpPr><p:cNvPr id="6" name="Straight Connector 5"><a:extLst><a:ext uri="{FF2B5EF4-FFF2-40B4-BE49-F238E27FC236}"><a16:creationId xmlns:a16="http://schemas.microsoft.com/office/drawing/2014/main" id="{3B07D3CE-83DF-306C-1740-B15E60D50B68}"/></a:ext></a:extLst></p:cNvPr><p:cNvCxnSpPr><a:cxnSpLocks/></p:cNvCxnSpPr><p:nvPr/></p:nvCxnSpPr><p:spPr><a:xfrm><a:off x="2663429" y="6816436"/><a:ext cx="0" cy="445863"/></a:xfrm><a:prstGeom prst="line"><a:avLst/></a:prstGeom><a:ln w="9525"><a:solidFill><a:schemeClr val="accent3"><a:lumMod val="40000"/><a:lumOff val="60000"/></a:schemeClr></a:solidFill><a:prstDash val="dash"/></a:ln></p:spPr><p:style><a:lnRef idx="1"><a:schemeClr val="accent1"/></a:lnRef><a:fillRef idx="0"><a:schemeClr val="accent1"/></a:fillRef><a:effectRef idx="0"><a:schemeClr val="accent1"/></a:effectRef><a:fontRef idx="minor"><a:schemeClr val="tx1"/></a:fontRef></p:style></p:cxnSp><p:sp><p:nvSpPr><p:cNvPr id="11" name="Rectangle 10"><a:extLst><a:ext uri="{FF2B5EF4-FFF2-40B4-BE49-F238E27FC236}"><a16:creationId xmlns:a16="http://schemas.microsoft.com/office/drawing/2014/main" id="{5CF57A2B-9E16-9EF8-CC46-DEACEC1E9222}"/></a:ext></a:extLst></p:cNvPr><p:cNvSpPr/><p:nvPr/></p:nvSpPr><p:spPr><a:xfrm><a:off x="2390809" y="6646115"/><a:ext cx="546397" cy="151573"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom><a:solidFill><a:schemeClr val="bg1"/></a:solidFill><a:ln><a:noFill/></a:ln></p:spPr><p:style><a:lnRef idx="2"><a:schemeClr val="accent1"><a:shade val="50000"/></a:schemeClr></a:lnRef><a:fillRef idx="1"><a:schemeClr val="accent1"/></a:fillRef><a:effectRef idx="0"><a:schemeClr val="accent1"/></a:effectRef><a:fontRef idx="minor"><a:schemeClr val="lt1"/></a:fontRef></p:style><p:txBody><a:bodyPr rtlCol="0" anchor="ctr"/><a:lstStyle/><a:p><a:pPr algn="ctr"/><a:endParaRPr lang="en-US" dirty="0" err="1"><a:solidFill><a:srgbClr val="101820"/></a:solidFill></a:endParaRPr></a:p></p:txBody></p:sp><p:pic><p:nvPicPr><p:cNvPr id="14" name="Graphic 13"><a:extLst><a:ext uri="{FF2B5EF4-FFF2-40B4-BE49-F238E27FC236}"><a16:creationId xmlns:a16="http://schemas.microsoft.com/office/drawing/2014/main" id="{6680C076-3929-2FD5-9B2D-C8EEC6FB5791}"/></a:ext></a:extLst></p:cNvPr><p:cNvPicPr><a:picLocks noChangeAspect="1"/></p:cNvPicPr><p:nvPr/></p:nvPicPr><p:blipFill><a:blip r:embed="rId120"><a:extLst><a:ext uri="{96DAC541-7B7A-43D3-8B79-37D633B846F1}"><asvg:svgBlip xmlns:asvg="http://schemas.microsoft.com/office/drawing/2016/SVG/main" r:embed="rId121"/></a:ext></a:extLst></a:blip><a:stretch><a:fillRect/></a:stretch></p:blipFill><p:spPr><a:xfrm><a:off x="2407408" y="6670008"/><a:ext cx="499438" cy="100897"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></p:spPr></p:pic><p:sp><p:nvSpPr><p:cNvPr id="15" name="Oval 14"><a:extLst><a:ext uri="{FF2B5EF4-FFF2-40B4-BE49-F238E27FC236}"><a16:creationId xmlns:a16="http://schemas.microsoft.com/office/drawing/2014/main" id="{BF608D1F-2449-B2E7-8286-C23F058ABA75}"/></a:ext></a:extLst></p:cNvPr><p:cNvSpPr/><p:nvPr/></p:nvSpPr><p:spPr><a:xfrm><a:off x="2641147" y="7268668"/><a:ext cx="45719" cy="45719"/></a:xfrm><a:prstGeom prst="ellipse"><a:avLst/></a:prstGeom><a:solidFill><a:schemeClr val="accent3"><a:lumMod val="20000"/><a:lumOff val="80000"/></a:schemeClr></a:solidFill><a:ln><a:noFill/></a:ln></p:spPr><p:style><a:lnRef idx="2"><a:schemeClr val="accent1"><a:shade val="50000"/></a:schemeClr></a:lnRef><a:fillRef idx="1"><a:schemeClr val="accent1"/></a:fillRef><a:effectRef idx="0"><a:schemeClr val="accent1"/></a:effectRef><a:fontRef idx="minor"><a:schemeClr val="lt1"/></a:fontRef></p:style><p:txBody><a:bodyPr rtlCol="0" anchor="ctr"/><a:lstStyle/><a:p><a:pPr algn="ctr"/><a:endParaRPr lang="en-US" dirty="0" err="1"><a:solidFill><a:srgbClr val="101820"/></a:solidFill></a:endParaRPr></a:p></p:txBody></p:sp></p:spTree>';
                        $TASFile = str_replace('</p:spTree>',$TASFileString,$TASFile);
                        $VTIndicatorFound = true;
                        break;
                    } else {
                        $VTIndicatorFound = false;
                    }
                }
            } else {
                $VTIndicatorFound = false;
            }
            // Add Report Link
            // ** // Use the following code to link based on presence of 'infoblox_references' parameter
            // if (isset($TAI['infoblox_references'][0])) {
            // ** // Use the following code to link based on the Threat Actor config
            //$InfobloxReferenceFound = false;
            if ((array_key_exists($TAI['actor_name'],$KnownActors)) && isset($KnownActors[$TAI['actor_name']]['URLStub'])) {
                $ThreatActorExternalLinkString = '<p:sp><p:nvSpPr><p:cNvPr id="7" name="Text Placeholder 20"><a:hlinkClick r:id="rId122"/><a:extLst><a:ext uri="{FF2B5EF4-FFF2-40B4-BE49-F238E27FC236}"><a16:creationId xmlns:a16="http://schemas.microsoft.com/office/drawing/2014/main" id="{4A652F23-47D6-59A0-1D85-972482B29234}"/></a:ext></a:extLst></p:cNvPr><p:cNvSpPr txBox="1"><a:spLocks/></p:cNvSpPr><p:nvPr/></p:nvSpPr><p:spPr><a:xfrm><a:off x="5574269" y="3869404"/><a:ext cx="1168604" cy="271567"/></a:xfrm><a:prstGeom prst="roundRect"><a:avLst><a:gd name="adj" fmla="val 20777"/></a:avLst></a:prstGeom><a:noFill/><a:ln w="19050"><a:solidFill><a:schemeClr val="accent1"/></a:solidFill></a:ln><a:effectLst><a:glow rad="63500"><a:srgbClr val="00B24C"><a:alpha val="40000"/></a:srgbClr></a:glow></a:effectLst></p:spPr><p:txBody><a:bodyPr lIns="0" tIns="0" rIns="0" bIns="0" anchor="ctr" anchorCtr="0"/><a:lstStyle><a:lvl1pPr marL="141755" indent="-141755" algn="l" defTabSz="567019" rtl="0" eaLnBrk="1" latinLnBrk="0" hangingPunct="1"><a:lnSpc><a:spcPct val="90000"/></a:lnSpc><a:spcBef><a:spcPts val="620"/></a:spcBef><a:buFont typeface="Arial" panose="020B0604020202020204" pitchFamily="34" charset="0"/><a:buChar char="•"/><a:defRPr sz="1736" kern="1200"><a:solidFill><a:schemeClr val="tx1"/></a:solidFill><a:latin typeface="+mn-lt"/><a:ea typeface="+mn-ea"/><a:cs typeface="+mn-cs"/></a:defRPr></a:lvl1pPr><a:lvl2pPr marL="425265" indent="-141755" algn="l" defTabSz="567019" rtl="0" eaLnBrk="1" latinLnBrk="0" hangingPunct="1"><a:lnSpc><a:spcPct val="90000"/></a:lnSpc><a:spcBef><a:spcPts val="310"/></a:spcBef><a:buFont typeface="Arial" panose="020B0604020202020204" pitchFamily="34" charset="0"/><a:buChar char="•"/><a:defRPr sz="1488" kern="1200"><a:solidFill><a:schemeClr val="tx1"/></a:solidFill><a:latin typeface="+mn-lt"/><a:ea typeface="+mn-ea"/><a:cs typeface="+mn-cs"/></a:defRPr></a:lvl2pPr><a:lvl3pPr marL="708774" indent="-141755" algn="l" defTabSz="567019" rtl="0" eaLnBrk="1" latinLnBrk="0" hangingPunct="1"><a:lnSpc><a:spcPct val="90000"/></a:lnSpc><a:spcBef><a:spcPts val="310"/></a:spcBef><a:buFont typeface="Arial" panose="020B0604020202020204" pitchFamily="34" charset="0"/><a:buChar char="•"/><a:defRPr sz="1240" kern="1200"><a:solidFill><a:schemeClr val="tx1"/></a:solidFill><a:latin typeface="+mn-lt"/><a:ea typeface="+mn-ea"/><a:cs typeface="+mn-cs"/></a:defRPr></a:lvl3pPr><a:lvl4pPr marL="992284" indent="-141755" algn="l" defTabSz="567019" rtl="0" eaLnBrk="1" latinLnBrk="0" hangingPunct="1"><a:lnSpc><a:spcPct val="90000"/></a:lnSpc><a:spcBef><a:spcPts val="310"/></a:spcBef><a:buFont typeface="Arial" panose="020B0604020202020204" pitchFamily="34" charset="0"/><a:buChar char="•"/><a:defRPr sz="1116" kern="1200"><a:solidFill><a:schemeClr val="tx1"/></a:solidFill><a:latin typeface="+mn-lt"/><a:ea typeface="+mn-ea"/><a:cs typeface="+mn-cs"/></a:defRPr></a:lvl4pPr><a:lvl5pPr marL="1275794" indent="-141755" algn="l" defTabSz="567019" rtl="0" eaLnBrk="1" latinLnBrk="0" hangingPunct="1"><a:lnSpc><a:spcPct val="90000"/></a:lnSpc><a:spcBef><a:spcPts val="310"/></a:spcBef><a:buFont typeface="Arial" panose="020B0604020202020204" pitchFamily="34" charset="0"/><a:buChar char="•"/><a:defRPr sz="1116" kern="1200"><a:solidFill><a:schemeClr val="tx1"/></a:solidFill><a:latin typeface="+mn-lt"/><a:ea typeface="+mn-ea"/><a:cs typeface="+mn-cs"/></a:defRPr></a:lvl5pPr><a:lvl6pPr marL="1559303" indent="-141755" algn="l" defTabSz="567019" rtl="0" eaLnBrk="1" latinLnBrk="0" hangingPunct="1"><a:lnSpc><a:spcPct val="90000"/></a:lnSpc><a:spcBef><a:spcPts val="310"/></a:spcBef><a:buFont typeface="Arial" panose="020B0604020202020204" pitchFamily="34" charset="0"/><a:buChar char="•"/><a:defRPr sz="1116" kern="1200"><a:solidFill><a:schemeClr val="tx1"/></a:solidFill><a:latin typeface="+mn-lt"/><a:ea typeface="+mn-ea"/><a:cs typeface="+mn-cs"/></a:defRPr></a:lvl6pPr><a:lvl7pPr marL="1842813" indent="-141755" algn="l" defTabSz="567019" rtl="0" eaLnBrk="1" latinLnBrk="0" hangingPunct="1"><a:lnSpc><a:spcPct val="90000"/></a:lnSpc><a:spcBef><a:spcPts val="310"/></a:spcBef><a:buFont typeface="Arial" panose="020B0604020202020204" pitchFamily="34" charset="0"/><a:buChar char="•"/><a:defRPr sz="1116" kern="1200"><a:solidFill><a:schemeClr val="tx1"/></a:solidFill><a:latin typeface="+mn-lt"/><a:ea typeface="+mn-ea"/><a:cs typeface="+mn-cs"/></a:defRPr></a:lvl7pPr><a:lvl8pPr marL="2126323" indent="-141755" algn="l" defTabSz="567019" rtl="0" eaLnBrk="1" latinLnBrk="0" hangingPunct="1"><a:lnSpc><a:spcPct val="90000"/></a:lnSpc><a:spcBef><a:spcPts val="310"/></a:spcBef><a:buFont typeface="Arial" panose="020B0604020202020204" pitchFamily="34" charset="0"/><a:buChar char="•"/><a:defRPr sz="1116" kern="1200"><a:solidFill><a:schemeClr val="tx1"/></a:solidFill><a:latin typeface="+mn-lt"/><a:ea typeface="+mn-ea"/><a:cs typeface="+mn-cs"/></a:defRPr></a:lvl8pPr><a:lvl9pPr marL="2409833" indent="-141755" algn="l" defTabSz="567019" rtl="0" eaLnBrk="1" latinLnBrk="0" hangingPunct="1"><a:lnSpc><a:spcPct val="90000"/></a:lnSpc><a:spcBef><a:spcPts val="310"/></a:spcBef><a:buFont typeface="Arial" panose="020B0604020202020204" pitchFamily="34" charset="0"/><a:buChar char="•"/><a:defRPr sz="1116" kern="1200"><a:solidFill><a:schemeClr val="tx1"/></a:solidFill><a:latin typeface="+mn-lt"/><a:ea typeface="+mn-ea"/><a:cs typeface="+mn-cs"/></a:defRPr></a:lvl9pPr></a:lstStyle><a:p><a:pPr marL="0" indent="0" algn="ctr"><a:lnSpc><a:spcPct val="100000"/></a:lnSpc><a:spcBef><a:spcPts val="300"/></a:spcBef><a:spcAft><a:spcPts val="600"/></a:spcAft><a:buClr><a:schemeClr val="tx1"/></a:buClr><a:buNone/></a:pPr><a:r><a:rPr lang="en-US" sz="600" b="1" dirty="0"><a:solidFill><a:schemeClr val="bg1"/></a:solidFill><a:latin typeface="Lato" panose="020F0502020204030203" pitchFamily="34" charset="77"/><a:ea typeface="Lato" panose="020F0502020204030203" pitchFamily="34" charset="0"/><a:cs typeface="Lato" panose="020F0502020204030203" pitchFamily="34" charset="0"/></a:rPr><a:t>THREAT ACTOR REPORT</a:t></a:r><a:endParaRPr lang="en-US" sz="600" dirty="0"><a:solidFill><a:schemeClr val="bg1"/></a:solidFill><a:latin typeface="Lato" panose="020F0502020204030203" pitchFamily="34" charset="77"/><a:ea typeface="Lato" panose="020F0502020204030203" pitchFamily="34" charset="0"/><a:cs typeface="Lato" panose="020F0502020204030203" pitchFamily="34" charset="0"/></a:endParaRPr></a:p></p:txBody></p:sp></p:spTree>';
                $TASFile = str_replace('</p:spTree>',$ThreatActorExternalLinkString,$TASFile);
                //$InfobloxReferenceFound = true;
            }
            
            // Update Slide XML with changes
            file_put_contents($FilesDir.'/reports/report-'.$UUID.'/ppt/slides/slide'.$SlideNumber.'.xml', $TASFile);

            $xml_tas = new DOMDocument('1.0', 'utf-8');
            $xml_tas->formatOutput = true; 
            $xml_tas->preserveWhiteSpace = false;
            $xml_tas->load($FilesDir.'/reports/report-'.$UUID.'/ppt/slides/_rels/slide'.$SlideNumber.'.xml.rels');

            foreach ($xml_tas->getElementsByTagName('Relationship') as $element) {
                // Remove notes references to avoid having to create unneccessary notes resources
                if ($element->getAttribute('Type') == "http://schemas.openxmlformats.org/officeDocument/2006/relationships/notesSlide") {
                    $element->remove();
                }
            }

            $xml_tas_f = $xml_tas->createDocumentFragment();

            if ((array_key_exists($TAI['actor_name'],$KnownActors)) && isset($KnownActors[$TAI['actor_name']]['IMG'])) {
                $UniqueActor = $KnownActors[$TAI['actor_name']]['IMG'];
                // Threat Actor PNG
                copy($AssetsDir.'/images/Threat Actors/Glow/'.$UniqueActor.'.png',$FilesDir.'/reports/report-'.$UUID.'/ppt/media/'.$UniqueActor.'.png');
                copy($AssetsDir.'/images/Threat Actors/Glow/'.$UniqueActor.'.svg',$FilesDir.'/reports/report-'.$UUID.'/ppt/media/'.$UniqueActor.'.svg');
                $xml_tas_f->appendXML('<Relationship Id="rId115" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/'.$UniqueActor.'.png"/>');
                $xml_tas_f->appendXML('<Relationship Id="rId116" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/'.$UniqueActor.'.svg"/>');
            } else {
                $xml_tas_f->appendXML('<Relationship Id="rId115" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/logo-only.png"/>');
                $xml_tas_f->appendXML('<Relationship Id="rId116" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/logo-only.svg"/>');
            }

            // Virus Total PNG / SVG
            if ($VTIndicatorFound) {
                copy($AssetsDir.'/images/Threat Actors/Other/virustotal.png',$FilesDir.'/reports/report-'.$UUID.'/ppt/media/virustotal.png');
                copy($AssetsDir.'/images/Threat Actors/Other/virustotal.svg',$FilesDir.'/reports/report-'.$UUID.'/ppt/media/virustotal.svg');
                $xml_tas_f->appendXML('<Relationship Id="rId120" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/virustotal.png"/>');
                $xml_tas_f->appendXML('<Relationship Id="rId121" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/virustotal.svg"/>');
            }

            // Infoblox Blog URL
            // ** // Use the following code to link based on presence of 'infoblox_references' parameter
            // if ($InfobloxReferenceFound) {
            //     $xml_tas_f2 = $xml_tas->createDocumentFragment();
            //     $xml_tas_f2->appendXML('<Relationship Id="rId122" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink" Target="'.$TAI['infoblox_references'][0].'" TargetMode="External"/>');
            //     $xml_tas->getElementsByTagName('Relationships')->item(0)->appendChild($xml_tas_f2);
            // }
            // ** // Use the following code to link based on the Threat Actor config
            if ((array_key_exists($TAI['actor_name'],$KnownActors)) && isset($KnownActors[$TAI['actor_name']]['URLStub'])) {
                $URLStub = $KnownActors[$TAI['actor_name']]['URLStub'];
                $URL = 'https://www.infoblox.com/threat-intel/threat-actors/'.$URLStub.'/';
                $xml_tas_f->appendXML('<Relationship Id="rId122" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink" Target="'.$URL.'" TargetMode="External"/>');
            }

            $xml_tas->getElementsByTagName('Relationships')->item(0)->appendChild($xml_tas_f);
            $xml_tas->save($FilesDir.'/reports/report-'.$UUID.'/ppt/slides/_rels/slide'.$SlideNumber.'.xml.rels');
            $TagStart += 10;
            // Iterate slide number
            $SlideNumber++;
            $ThreatActorSlideCount--;
        }

        // Append Elements to Core XML Files
        $xml_rels->getElementsByTagName('Relationships')->item(0)->appendChild($xml_rels_f);
        // Append new slides to specific position
        $xml_pres->getElementsByTagName('sldId')->item($ThreatActorSlidePosition)->after($xml_pres_f);
        
        // Save Core XML Files
        $xml_rels->save($FilesDir.'/reports/report-'.$UUID.'/ppt/_rels/presentation.xml.rels');
        $xml_pres->save($FilesDir.'/reports/report-'.$UUID.'/ppt/presentation.xml');
        
        //
        // End of Threat Actors
        //
       

        // Rebuild Powerpoint Template Zip
        $Progress = writeProgress($UUID,$Progress,"Stitching Powerpoint Template");
        compressZip($FilesDir.'/reports/report-'.$UUID.'.pptx',$FilesDir.'/reports/report-'.$UUID);

        // Cleanup Extracted Zip
        $Progress = writeProgress($UUID,$Progress,"Cleaning up");
        rmdirRecursive($FilesDir.'/reports/report-'.$UUID);

        // Extract Powerpoint Template Strings
        // ** Using external library to save re-writing the string replacement functions manually. Will probably pull this in as native code at some point.
        $Progress = writeProgress($UUID,$Progress,"Extract Powerpoint Strings");
        $extractor = new BasicExtractor();
        $mapping = $extractor->extractStringsAndCreateMappingFile(
            $FilesDir.'/reports/report-'.$UUID.'.pptx',
            $FilesDir.'/reports/report-'.$UUID.'-extracted.pptx'
        );

        $Progress = writeProgress($UUID,$Progress,"Injecting Powerpoint Strings");
        ##// Slide 2 / 45 - Title Page & Contact Page
        // Get & Inject Customer Name, Contact Name & Email
        $AccountInfo = QueryCSP("get","v2/current_user/accounts");
        $CurrentAccount = $AccountInfo->results[array_search($UserInfo->result->account_id, array_column($AccountInfo->results, 'id'))];
        writeLog("SecurityAssessment",$UserInfo->result->name." requested a security assessment report for: ".$CurrentAccount->name,"info");
        $mapping = replaceTag($mapping,'#TAG01',$CurrentAccount->name);
        $mapping = replaceTag($mapping,'#DATE',date("dS F Y"));
        $mapping = replaceTag($mapping,'#NAME',$UserInfo->result->name);
        $mapping = replaceTag($mapping,'#EMAIL',$UserInfo->result->email);

        ##// Slide 5 - Executive Summary
        $mapping = replaceTag($mapping,'#TAG02',number_abbr($HighEventsCount)); // High-Risk Events
        $mapping = replaceTag($mapping,'#TAG03',number_abbr($HighRiskWebsiteCount)); // High-Risk Websites
        $mapping = replaceTag($mapping,'#TAG04',number_abbr($DataExfilEventsCount)); // Data Exfil / Tunneling
        $mapping = replaceTag($mapping,'#TAG05',number_abbr($LookalikeThreatCount)); // Lookalike Domains
        $mapping = replaceTag($mapping,'#TAG06',number_abbr($ZeroDayDNSEventsCount)); // Zero Day DNS
        $mapping = replaceTag($mapping,'#TAG07',number_abbr($SuspiciousEventsCount)); // Suspicious Domains

        ##// Slide 6 - Security Indicator Summary
        $mapping = replaceTag($mapping,'#TAG08',number_abbr($DNSActivityCount)); // DNS Requests
        $mapping = replaceTag($mapping,'#TAG09',number_abbr($HighEventsCount)); // High-Risk Events
        $mapping = replaceTag($mapping,'#TAG10',number_abbr($MediumEventsCount)); // Medium-Risk Events
        $mapping = replaceTag($mapping,'#TAG11',number_abbr($TotalInsights)); // Insights
        $mapping = replaceTag($mapping,'#TAG12',number_abbr($LookalikeThreatCount)); // Custom Lookalike Domains
        $mapping = replaceTag($mapping,'#TAG13',number_abbr($DOHEventsCount)); // DoH
        $mapping = replaceTag($mapping,'#TAG14',number_abbr($ZeroDayDNSEventsCount)); // Zero Day DNS
        $mapping = replaceTag($mapping,'#TAG15',number_abbr($SuspiciousEventsCount)); // Suspicious Domains

        $mapping = replaceTag($mapping,'#TAG16',number_abbr($NODEventsCount)); // Newly Observed Domains
        $mapping = replaceTag($mapping,'#TAG17',number_abbr($DGAEventsCount)); // Domain Generated Algorithms
        $mapping = replaceTag($mapping,'#TAG18',number_abbr($DataExfilEventsCount)); // DNS Tunnelling
        $mapping = replaceTag($mapping,'#TAG19',number_abbr($UniqueApplicationsCount)); // Unique Applications
        $mapping = replaceTag($mapping,'#TAG20',number_abbr($HighRiskWebCategoryCount)); // High-Risk Web Categories
        $mapping = replaceTag($mapping,'#TAG21',number_abbr($ThreatActorsCount)); // Threat Actors

        ##// Slide 9 - Traffic Usage Analysis
        // Total DNS Activity
        $mapping = replaceTag($mapping,'#TAG22',number_abbr($DNSActivityCount));
        // DNS Firewall Activity
        $mapping = replaceTag($mapping,'#TAG23',number_abbr($HML)); // Total
        $mapping = replaceTag($mapping,'#TAG24',number_abbr($HighEventsCount)); // High Int
        $mapping = replaceTag($mapping,'#TAG25',number_format($HighPerc,2).'%'); // High Percent
        $mapping = replaceTag($mapping,'#TAG26',number_abbr($MediumEventsCount)); // Medium Int
        $mapping = replaceTag($mapping,'#TAG27',number_format($MediumPerc,2).'%'); // Medium Percent
        $mapping = replaceTag($mapping,'#TAG28',number_abbr($LowEventsCount)); // Low Int
        $mapping = replaceTag($mapping,'#TAG29',number_format($LowPerc,2).'%'); // Low Percent
        // Threat Activity
        $mapping = replaceTag($mapping,'#TAG30',number_abbr($ThreatActivityEventsCount));
        // Data Exfiltration Incidents
        $mapping = replaceTag($mapping,'#TAG31',number_abbr($DataExfilEventsCount));

        ##// Slide 15 - Key Insights
        // Insight Severity
        $mapping = replaceTag($mapping,'#TAG32',number_abbr($TotalInsights)); // Total Open Insights
        $mapping = replaceTag($mapping,'#TAG33',number_abbr($MediumInsights)); // Medium Priority Insights
        $mapping = replaceTag($mapping,'#TAG34',number_abbr($HighInsights)); // High Priority Insights
        $mapping = replaceTag($mapping,'#TAG35',number_abbr($CriticalInsights)); // Critical Priority Insights
        // Event To Insight Aggregation
        $mapping = replaceTag($mapping,'#TAG36',number_abbr($SecurityEventsCount)); // Events
        $mapping = replaceTag($mapping,'#TAG37',number_abbr($TotalInsights)); // Key Insights

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

        ##// Slide 28 - Security Activities
        $mapping = replaceTag($mapping,'#TAG47',number_abbr($SecurityEventsCount)); // Security Events
        $mapping = replaceTag($mapping,'#TAG48',number_abbr($DNSFirewallEventsCount)); // DNS Firewall
        $mapping = replaceTag($mapping,'#TAG49',number_abbr($WebContentEventsCount)); // Web Content
        $mapping = replaceTag($mapping,'#TAG50',number_abbr($DeviceCount)); // Devices
        $mapping = replaceTag($mapping,'#TAG51',number_abbr($UserCount)); // Users
        $mapping = replaceTag($mapping,'#TAG52',number_abbr($TotalInsights)); // Insights
        $mapping = replaceTag($mapping,'#TAG53',number_abbr($ThreatInsightCount)); // Threat Insight
        $mapping = replaceTag($mapping,'#TAG54',number_abbr($ThreatViewCount)); // Threat View
        $mapping = replaceTag($mapping,'#TAG55',number_abbr($SourcesCount)); // Sources

        ##// Slide 32 -> Onwards - Threat Actors
        // This is where the Threat Actor Tag replacement occurs
        // Set Tag Start Number
        $TagStart = 100;
        foreach ($ThreatActorInfo as $TAI) {
            // Get sorted list of observed IOCs not found in Virus Total
            if (isset($TAI['related_indicators_with_dates'])) {
                $ObservedIndicators = $TAI['related_indicators_with_dates'];
                $IndicatorsInVT = [];
                $IndicatorCount = count($TAI['related_indicators_with_dates']);
                if ($IndicatorCount > 0) {
                    foreach ($ObservedIndicators as $OI) {
                        if (array_key_exists('vt_first_submission_date', json_decode(json_encode($OI), true))) {
                            $IndicatorsInVT[] = $OI;
                        }
                    }
                }
                if (count($IndicatorsInVT) > 0) {
                    // Sort the array based on the time difference
                    usort($IndicatorsInVT, function($a, $b) {
                        return calculateVirusTotalDifference($b) <=> calculateVirusTotalDifference($a);
                    });
                    $IndicatorsNotInVT = count($ObservedIndicators) - count($IndicatorsInVT);
                    $ExampleDomain = $IndicatorsInVT[0]->indicator;
                    $FirstSeen = new DateTime($IndicatorsInVT[0]->te_ik_submitted);
                    $LastSeen = new DateTime($IndicatorsInVT[0]->te_customer_last_dns_query);
                    $VTDate = new DateTime($IndicatorsInVT[0]->vt_first_submission_date);
                    $ProtectedFor = $FirstSeen->diff($LastSeen)->days;
                    $DaysAhead = 'Discovered '.($ProtectedFor - $LastSeen->diff($VTDate)->days).' days ahead';
                } else {
                    $IndicatorsNotInVT = count($ObservedIndicators);
                    $ExampleDomain = $ObservedIndicators[0]->indicator;
                    $FirstSeen = new DateTime($ObservedIndicators[0]->te_ik_submitted);
                    $LastSeen = new DateTime($ObservedIndicators[0]->te_customer_last_dns_query);
                    $DaysAhead = 'Discovered';
                    $ProtectedFor = $FirstSeen->diff($LastSeen)->days;
                }
            } else {
                $IndicatorsNotInVT = 'N/A';
                $ExampleDomain = 'N/A';
                $FirstSeen = new DateTime('1901-01-01 00:00');
                $LastSeen = new DateTime('1901-01-01 00:00');
                $DaysAhead = 'Discovered';
                $ProtectedFor = 'N/A';
                $IndicatorCount = 'N/A';
            }

            $mapping = replaceTag($mapping,'#TATAG'.$TagStart.'01',ucwords($TAI['actor_name'])); // Threat Actor Name
            $mapping = replaceTag($mapping,'#TATAG'.$TagStart.'02',$TAI['actor_description']); // Threat Actor Description
            $mapping = replaceTag($mapping,'#TATAG'.$TagStart.'03',$IndicatorCount); // Number of Observed IOCs
            $mapping = replaceTag($mapping,'#TATAG'.$TagStart.'04',$IndicatorsNotInVT); // Number of Observed IOCs not found in Virus Total
            $mapping = replaceTag($mapping,'#TATAG'.$TagStart.'05',$TAI['related_count']); // Number of Related IOCs
            $mapping = replaceTag($mapping,'#TATAG'.$TagStart.'06',$DaysAhead); // Discovered X Days Ahead
            $mapping = replaceTag($mapping,'#TATAG'.$TagStart.'07',$FirstSeen->format('d/m/y')); // First Detection Date
            $mapping = replaceTag($mapping,'#TATAG'.$TagStart.'08',$LastSeen->format('d/m/y')); // Last Detection Date
            $mapping = replaceTag($mapping,'#TATAG'.$TagStart.'09',$ProtectedFor); // Protected X Days
            $mapping = replaceTag($mapping,'#TATAG'.$TagStart.'10',$ExampleDomain); // Example Domain
            $TagStart += 10;
        }

        // Rebuild Powerpoint
        // ** Using external library to save re-writing the string replacement functions manually. Will probably pull this in as native code at some point.
        $Progress = writeProgress($UUID,$Progress,"Rebuilding Powerpoint Template");
        $injector = new BasicInjector();
        $injector->injectMappingAndCreateNewFile(
            $mapping,
            $FilesDir.'/reports/report-'.$UUID.'-extracted.pptx',
            $FilesDir.'/reports/report-'.$UUID.'.pptx'
        );

        // Cleanup
        $Progress = writeProgress($UUID,$Progress,"Final Cleanup");
        unlink($FilesDir.'/reports/report-'.$UUID.'-extracted.pptx');

        $Status = 'Success';
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

function getThreatActorConfig() {
    return json_decode(file_get_contents(__DIR__.'/../../inc/config/threat-actors.json'), true);
}

function writeProgress($id,$Count,$Action = "") {
    $Count++;
    $myfile = fopen(__DIR__.'/../../files/reports/report-'.$id.'.progress', "w") or die("Unable to save progress file");
    $Progress = json_encode(array(
        'Count' => $Count,
        'Action' => $Action
    ));
    fwrite($myfile, $Progress);
    fclose($myfile);
    return $Count;
}

function getProgress($id,$Total) {
    $ProgressFile = __DIR__.'/../../files/reports/report-'.$id.'.progress';
    if (file_exists($ProgressFile)) {
        $myfile = fopen($ProgressFile, "r") or die("0");
        $Current = json_decode(fread($myfile,filesize($ProgressFile)));
        return array(
            'Progress' => (100 / $Total) * $Current->Count,
            'Action' => $Current->Action.'..'
        );
    } else {
        return array(
            'Progress' => 0,
            'Action' => 'Starting..'
        );
    }
}

function getReportFiles() {
    $files = array_diff(scandir(__DIR__.'/../../files/reports/'),array('.', '..','placeholder.txt'));
    return $files;
}

function calculateProtectedDifference($te_ik_submitted,$te_customer_last_dns_query) {
    $submitted = new DateTime($te_ik_submitted);
    $lastQuery = new DateTime($te_customer_last_dns_query);
    return $lastQuery->getTimestamp() - $submitted->getTimestamp();
}

function calculateVirusTotalDifference($obj) {
    $submitted = new DateTime($obj->te_ik_submitted);
    $vtsubmitted = new DateTime($obj->vt_first_submission_date);
    return $vtsubmitted->getTimestamp() - $submitted->getTimestamp();
}