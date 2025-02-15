<?php
$app->get('/auth/heartbeat', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	if ($phpef->auth->getAuth()['Authenticated'] == true) {
		$phpef->api->setAPIResponseCode(200);
	} else {
		$phpef->api->setAPIResponse('Error','Session timed out',301);
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/auth/whoami', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if (null !== $phpef->auth->getAuth()) {
        $AuthContent = $phpef->auth->getAuth();
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
        $phpef->api->setAPIResponseData($AuthContent);
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/auth/login', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    $phpef->auth->login($phpef->api->getAPIRequestData($request));
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/auth/logout', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    $phpef->auth->logout();
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Administrative Password Reset
$app->post('/auth/password/reset', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    $data = $phpef->api->getAPIRequestData($request);
	if (isset($data['pw'])) {
		$phpef->auth->resetPassword($data['pw']);
	} else {
		$phpef->api->setAPIResponse('Error','New password missing from request');
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// User Expired Password Reset
$app->post('/auth/password/expired', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    $data = $phpef->api->getAPIRequestData($request);
	if (isset($data['un']) && isset($data['cpw']) && isset($data['pw'])) {
		$phpef->auth->resetExpiredPassword($data['un'],$data['cpw'],$data['pw']);
	} else {
		$phpef->api->setAPIResponse('Error','Required values missing from the request');
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Crypt - Used for returning encrypted strings for saving API Keys locally within the browser
$app->post('/auth/crypt', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    $data = $phpef->api->getAPIRequestData($request);
	if (isset($data['key'])) {
		$phpef->api->setAPIResponseData(encrypt($data['key'],$phpef->config->get("Security","salt")));
	} else {
		$phpef->api->setAPIResponse('Error','key is missing from the request');
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// SAML SSO
$app->get('/auth/sso', function ($request, $response, $args) {
    $phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->config->get('SAML', 'enabled')) {
        $phpef->auth->sso();
    } else {
		$phpef->api->setAPIResponse('Error', 'SSO is not enabled', '400');
		$response->getBody()->write(jsonE($GLOBALS['api']));
		return $response
			->withHeader('Content-Type', 'application/json;charset=UTF-8')
			->withStatus($GLOBALS['responseCode']);
    }
});

$app->get('/auth/slo', function ($request, $response, $args) {
    $phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->config->get('SAML', 'enabled')) {
        $phpef->auth->slo();
    } else {
		$phpef->api->setAPIResponse('Error', 'SSO is not enabled', '400');
		$response->getBody()->write(jsonE($GLOBALS['api']));
		return $response
			->withHeader('Content-Type', 'application/json;charset=UTF-8')
			->withStatus($GLOBALS['responseCode']);
    }
});

$app->post('/auth/acs', function ($request, $response, $args) {
    $phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->config->get('SAML', 'enabled')) {
        if (isset($phpef->api->getAPIRequestData($request)['SAMLResponse'])) {
            $phpef->auth->acs();
			$response->getBody()->write(jsonE($GLOBALS['api']));
        }
    } else {
		$phpef->api->setAPIResponse('Error', 'SSO is not enabled', '400');
		$response->getBody()->write(jsonE($GLOBALS['api']));
    }
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/auth/samlMetadata', function ($request, $response, $args) {
    $phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->config->get('SAML', 'enabled')) {
        $response->getBody()->write($phpef->auth->getSamlMetadata());
		return $response
			->withHeader('Content-Type', 'application/xml')
			->withStatus($GLOBALS['responseCode']);
    } else {
		$phpef->api->setAPIResponse('Error', 'SSO is not enabled', '400');
		$response->getBody()->write(jsonE($GLOBALS['api']));
		return $response
			->withHeader('Content-Type', 'application/json;charset=UTF-8')
			->withStatus($GLOBALS['responseCode']);
    }
});

// Multi Factor
$app->get('/auth/mfa/settings', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->getAuth()['Authenticated']) {
		$phpef->api->setAPIResponseData($phpef->auth->mfaSettings());
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/auth/mfa/totp/register', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->getAuth()['Authenticated']) {
        $result = $phpef->auth->totpNewRegistration();
		$phpef->api->setAPIResponseData(base64_encode($result->getString()));
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/auth/mfa/totp/register/verify', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->getAuth()['Authenticated']) {
		$data = $phpef->api->getAPIRequestData($request);
		if (isset($data['totp_code'])) {
			$verify = $phpef->auth->totpVerifyRegistration($data['totp_code']);
		}
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/auth/mfa/totp/verify', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	$data = $phpef->api->getAPIRequestData($request);
	if (isset($data['totp_code']) && isset($data['jwt'])) {
		$verify = $phpef->auth->totpVerifyUser($data['totp_code'],$data['jwt']);
	} else {
		$phpef->api->setAPIResponse('Error','TOTP Code or JWT Missing or Invalid');
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/auth/mfa/reset/{id}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-USERS")) {
		$reset = $phpef->auth->mfaReset($args['id']);
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});