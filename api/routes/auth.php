<?php
$app->get('/auth/heartbeat', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
	if ($ib->auth->getAuth()['Authenticated'] == true) {
		$ib->api->setAPIResponseCode(200);
	} else {
		$ib->api->setAPIResponse('Error','Session timed out',301);
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
        $ib->api->setAPIResponseData($AuthContent);
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/auth/login', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    $ib->auth->login($ib->api->getAPIRequestData($request));
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

// Administrative Password Reset
$app->post('/auth/password/reset', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    $data = $ib->api->getAPIRequestData($request);
	if (isset($data['pw'])) {
		$ib->auth->resetPassword($data['pw']);
	} else {
		$ib->api->setAPIResponse('Error','New password missing from request');
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// User Expired Password Reset
$app->post('/auth/password/expired', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    $data = $ib->api->getAPIRequestData($request);
	if (isset($data['un']) && isset($data['cpw']) && isset($data['pw'])) {
		$ib->auth->resetExpiredPassword($data['un'],$data['cpw'],$data['pw']);
	} else {
		$ib->api->setAPIResponse('Error','Required values missing from the request');
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Crypt - Used for returning encrypted strings for saving API Keys locally within the browser
$app->post('/auth/crypt', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    $data = $ib->api->getAPIRequestData($request);
	if (isset($data['key'])) {
		$ib->api->setAPIResponseData(encrypt($data['key'],$ib->config->get("Security","salt")));
	} else {
		$ib->api->setAPIResponse('Error','key is missing from the request');
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// SAML SSO
$app->get('/auth/sso', function ($request, $response, $args) {
    $ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->config->get('SAML', 'enabled')) {
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
    if ($ib->config->get('SAML', 'enabled')) {
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
    if ($ib->config->get('SAML', 'enabled')) {
        if (isset($ib->api->getAPIRequestData($request)['SAMLResponse'])) {
            $ib->auth->acs();
			$response->getBody()->write(jsonE($GLOBALS['api']));
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
    if ($ib->config->get('SAML', 'enabled')) {
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