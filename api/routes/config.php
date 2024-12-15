<?php
$app->get('/config', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();

    if ($ib->rbac->checkAccess("ADMIN-CONFIG")) {
        $config = $ib->config->getConfig();
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
        $config = $ib->config->getConfig();
        $config['Security']['salt'] = "********";
        if ($config['SAML']['sp']['privateKey'] != "") {
            $config['SAML']['sp']['privateKey'] = "********";
        }
        $config['SAML']['idp']['x509cert'] = substr($config['SAML']['idp']['x509cert'],0,24).'...';
        if (isset($data['systemLogFileName'])) { $ib->config->setConfig("System","logfilename",$data['systemLogFileName']); }
        if (isset($data['systemLogDirectory'])) { $ib->config->setConfig("System","logdirectory",$data['systemLogDirectory']); }
        if (isset($data['systemLogLevel'])) { $ib->config->setConfig("System","loglevel",$data['systemLogLevel']); }
        if (isset($data['systemLogRetention'])) { $ib->config->setConfig("System","logretention",$data['systemLogRetention']); }
        if (isset($data['systemCURLTimeout'])) { $ib->config->setConfig("System","CURL-Timeout",$data['systemCURLTimeout']); }
        if (isset($data['systemCURLTimeoutConnect'])) { $ib->config->setConfig("System","CURL-ConnectTimeout",$data['systemCURLTimeoutConnect']); }
        if (isset($data['securitySalt'])) { $ib->config->setConfig("Security","salt",$data['securitySalt']); }
    
        // SAML Stuff //
        if (isset($data['samlEnabled'])) { $ib->config->setConfig("SAML","enabled",filter_var($data['samlEnabled'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)); }
        if (isset($data['samlAutoCreateUsers'])) { $ib->config->setConfig("SAML","AutoCreateUsers",filter_var($data['samlAutoCreateUsers'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)); }
        if (isset($data['samlStrict'])) { $ib->config->setConfig("SAML","strict",filter_var($data['samlStrict'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)); }
        if (isset($data['samlDebug'])) { $ib->config->setConfig("SAML","debug",filter_var($data['samlDebug'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)); }
    
        // SP
        if (isset($data['spEntityId']) || isset($data['spAcsUrl']) || isset($data['spSloUrl']) || isset($data['spX509Cert']) || isset($data['spPrivateKey'])) {
            $spconfig = $config['SAML']['sp'];
            if (isset($data['spEntityId'])) {
                $spconfig['entityId'] = urldecode($data['spEntityId']);
            }
            if (isset($data['spAcsUrl'])) {
                $spconfig['assertionConsumerService']['url'] = urldecode($data['spAcsUrl']);
            }
            if (isset($data['spSloUrl'])) {
                $spconfig['singleLogoutService']['url'] = urldecode($data['spSloUrl']);
            }
            if (isset($data['spX509Cert'])) {
                $spconfig['x509cert'] = urldecode($data['spX509Cert']);
            }
            if (isset($data['spPrivateKey'])) {
                $spconfig['privateKey'] = urldecode($data['spPrivateKey']);
            }
            $ib->config->setConfig("SAML","sp",$spconfig);
        }
    
        // IdP
        if (isset($data['idpEntityId']) || isset($data['idpSsoUrl']) || isset($data['idpSloUrl']) || isset($data['idpX509Cert'])) {
            $idpconfig = $config['SAML']['idp'];
            if (isset($data['idpEntityId'])) {
                $idpconfig['entityId'] = urldecode($data['idpEntityId']);
            }
            if (isset($data['idpSsoUrl'])) {
                $idpconfig['singleSignOnService']['url'] = urldecode($data['idpSsoUrl']);
            }
            if (isset($data['idpSloUrl'])) {
                $idpconfig['singleLogoutService']['url'] = urldecode($data['idpSloUrl']);
            }
            if (isset($data['idpX509Cert'])) {
                $idpconfig['x509cert'] = urldecode($data['idpX509Cert']);
            }
            $ib->config->setConfig("SAML","idp",$idpconfig);
        }
    
        // User Attributes
        if (isset($data['attributeUsername']) || isset($data['attributeFirstName']) || isset($data['attributeLastName']) || isset($data['attributeEmail']) || isset($data['attributeGroups'])) {
            $attributeconfig = $config['SAML']['attributes'];
            if (isset($data['attributeUsername'])) {
                $attributeconfig['Username'] = urldecode($data['attributeUsername']);
            }
            if (isset($data['attributeFirstName'])) {
                $attributeconfig['FirstName'] = urldecode($data['attributeFirstName']);
            }
            if (isset($data['attributeLastName'])) {
                $attributeconfig['LastName'] = urldecode($data['attributeLastName']);
            }
            if (isset($data['attributeEmail'])) {
                $attributeconfig['Email'] = urldecode($data['attributeEmail']);
            }
            if (isset($data['attributeGroups'])) {
                $attributeconfig['Groups'] = urldecode($data['attributeGroups']);
            }
            $ib->config->setConfig("SAML","attributes",$attributeconfig);
        }
        // End of SAML stuff //
    
        $newConfig = $ib->config->getConfig();
        $newConfig['Security']['salt'] = "********";
        if ($newConfig['SAML']['sp']['privateKey'] != "") {
            $newConfig['SAML']['sp']['privateKey'] = "********";
        }
        $newConfig['SAML']['idp']['x509cert'] = substr($newConfig['SAML']['idp']['x509cert'],0,24).'...';
        $ib->api->setAPIResponseData($newConfig);
        $logArr = array(
            "Old Configuration" => $config,
            "New Configuration" => $newConfig
        );
        $ib->logging->writeLog("Config","Updated configuration","warning",$logArr);
        $ib->api->setAPIResponseData($newConfig);
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});