<?php
$app->get('/auth/heartbeat', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
	if ($ib->auth->getAuth()['Authenticated'] == true) {
		$ib->api->setAPIResponseCode(200);
	} else {
		$ib->api->setAPIResponse('error','Session timed out',301);
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/auth/whoami', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if (null !== $ib->auth->getAuth()) {
        $AuthContent = $ib->auth->getAuth();
        $AuthContent['headers'] = getallheaders();
        $UnsetHeaders = array(
            "Remote-Email",
            "Remote-Groups",
            "Remote-Name",
            "Remote-User"
        );
        foreach ($UnsetHeaders as $UnsetHeader) {
            unset($AuthContent['headers'][$UnsetHeader]);
        }
        $ib->api->setAPIData($AuthContent);
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/auth/login', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    $ib->auth->login($ib->api->getApiData($request));
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/auth/logout', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    $ib->auth->logout();
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/auth/sso', function ($request, $response, $args) {
    $ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->config->getConfig('SAML', 'enabled')) {
        $ib->auth->sso();
    } else {
		$ib->api->setAPIResponse('Error', 'SSO is not enabled', '400');
		$response->getBody()->write(jsonE($GLOBALS['api']));
		return $response
			->withHeader('Content-Type', 'application/json;charset=UTF-8')
			->withStatus($GLOBALS['responseCode']);
    }
});

$app->get('/auth/slo', function ($request, $response, $args) {
    $ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->config->getConfig('SAML', 'enabled')) {
        $ib->auth->slo();
    } else {
		$ib->api->setAPIResponse('Error', 'SSO is not enabled', '400');
		$response->getBody()->write(jsonE($GLOBALS['api']));
		return $response
			->withHeader('Content-Type', 'application/json;charset=UTF-8')
			->withStatus($GLOBALS['responseCode']);
    }
});

$app->post('/auth/acs', function ($request, $response, $args) {
    $ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->config->getConfig('SAML', 'enabled')) {
        if (isset($ib->api->getApiData($request)['SAMLResponse'])) {
            $response->getBody()->write(jsonE($ib->auth->acs()));
        }
    } else {
		$ib->api->setAPIResponse('Error', 'SSO is not enabled', '400');
		$response->getBody()->write(jsonE($GLOBALS['api']));
    }
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/auth/samlMetadata', function ($request, $response, $args) {
    $ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->config->getConfig('SAML', 'enabled')) {
        $response->getBody()->write($ib->auth->getSamlMetadata());
		return $response
			->withHeader('Content-Type', 'application/xml')
			->withStatus($GLOBALS['responseCode']);
    } else {
		$ib->api->setAPIResponse('Error', 'SSO is not enabled', '400');
		$response->getBody()->write(jsonE($GLOBALS['api']));
		return $response
			->withHeader('Content-Type', 'application/json;charset=UTF-8')
			->withStatus($GLOBALS['responseCode']);
    }
});