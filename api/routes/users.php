<?php
$app->get('/users', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-USERS")) {
        $phpef->api->setAPIResponseData($phpef->auth->getAllUsers());
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/users', function ($request, $response, $args) {
    $phpef = ($request->getAttribute('phpef')) ?? new phpef();
    $data = $phpef->api->getAPIRequestData($request);
    if ($phpef->auth->checkAccess("ADMIN-USERS")) {
        $UN = $data['userUsername'] ?? exit($phpef->api->setAPIResponse('Error','Username missing from request'));
        $PW = $data['userPassword'] ?? exit($phpef->api->setAPIResponse('Error','Password missing from request'));
        $FN = $data['userFirstName'] ?? null;
        $SN = $data['userLastName'] ?? null;
        $EM = $data['userEmail'] ?? null;
        $Groups = $data['groups'] ?? null;
        $Expire = $data['expire'] ?? 'false';
        $phpef->auth->newUser($UN,$PW,$FN,$SN,$EM,$Groups,'Local',$Expire);
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->patch('/user/{id}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    $data = $phpef->api->getAPIRequestData($request);
    if ($phpef->auth->checkAccess("ADMIN-USERS")) {
        if (isset($args['id'])) {
            $FN = $data['userFirstName'] ?? null;
            $SN = $data['userLastName'] ?? null;
            $EM = $data['userEmail'] ?? null;
            $UN = $data['userUsername'] ?? null;
            $PW = $data['userPassword'] ?? null;
            $Groups = $data['groups'] ?? null;
            $phpef->auth->updateUser($args['id'],$UN,$PW,$FN,$SN,$EM,$Groups);
        } else {
            $phpef->api->setAPIResponse('Error','id missing from request',400);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->delete('/user/{id}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-USERS")) {
        if (isset($args['id'])) {
            $phpef->auth->removeUser($args['id']);
        } else {
            $phpef->api->setAPIResponse('Error','id missing from request',400);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});