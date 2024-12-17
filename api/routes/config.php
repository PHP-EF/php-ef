<?php
$app->get('/config', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();

    if ($ib->rbac->checkAccess("ADMIN-CONFIG")) {
        $config = $ib->config->get();
        $config['Security']['salt'] = "********";
        if ($config['SAML']['sp']['privateKey'] != "") {
            $config['SAML']['sp']['privateKey'] = "********";
        }
        $config['SAML']['idp']['x509cert'] = substr($config['SAML']['idp']['x509cert'],0,24).'...';
        $ib->api->setAPIResponseData($config);
        $ib->logging->writeLog("Config","Queried Configuration","info",$_REQUEST);
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->patch('/config', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->rbac->checkAccess("ADMIN-CONFIG")) {
        $data = $ib->api->getAPIRequestData($request);
        $config = $ib->config->get();
        // Update the config values with the submitted data
        $ib->config->set($config, $data);
        $ib->api->setAPIResponseMessage('Successfully updated configuration');
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});