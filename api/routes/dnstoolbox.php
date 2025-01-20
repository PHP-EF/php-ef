<?php
$app->get('/dnstoolbox', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("DNS-TOOLBOX")) {
        $data = $request->getQueryParams();
        if (isset($data['request']) && isset($data['domain'])) {

            $domain = $data['domain'];
            if ($domain == '.') {
                $phpef->api->setAPIResponse('Error','Domain Name or IP missing from request',400);
            }
            if ($data['request'] != 'port') {
                if (!isset($data['source']) || $data['source'] == 'null') {
                    $phpef->api->setAPIResponse('Error','DNS Server missing from request',400);
                    return false;
                } else {
                    $source = $data['source'];
                    switch ($source) {
                        case 'google':
                            $sourceserver = 'dns.google';
                            break;
                        case 'cloudflare':
                            $sourceserver = 'one.one.one.one';
                            break;
                    }
                }
            } else {
                if ($data['port'] == "") {
                    $port = [];
                } else {
                    $port = explode(',',$data['port']);
                }
            }

            $DNSToolbox = new DNSToolbox();

            $phpef->logging->writeLog("DNSToolbox","A query was performed using type: ".$data['request'],"debug",$data);
            $methods = [
                'a' => 'a',
                'aaaa' => 'aaaa',
                'cname' => 'cname',
                'all' => 'all',
                'mx' => 'mx',
                'port' => 'port',
                'txt' => 'txt',
                'dmarc' => 'dmarc',
                'nameserver' => 'ns',
                'soa' => 'soa',
                'reverse' => 'reverse'
            ];
            if (array_key_exists($data['request'], $methods)) {
                $method = $methods[$data['request']];
                if ($method === 'port') {
                    $phpef->api->setAPIResponseData($DNSToolbox->$method($domain, $port));
                } else {
                    $phpef->api->setAPIResponseData($DNSToolbox->$method($domain, $sourceserver));
                }
            } else {
                $phpef->api->setAPIResponse('Error','Invalid Request Type',400);
            }
        }
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});