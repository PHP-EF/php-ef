<?php
$app->get('/users', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('ib')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-USERS")) {
        $phpef->api->setAPIResponseData($phpef->auth->getAllUsers());
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/users', function ($request, $response, $args) {
    $phpef = ($request->getAttribute('ib')) ?? new phpef();
    $data = $phpef->api->getAPIRequestData($request);
    if ($phpef->auth->checkAccess("ADMIN-USERS")) {
        $UN = $data['un'] ?? exit($phpef->api->setAPIResponse('Error','Username missing from request'));
        $PW = $data['pw'] ?? exit($phpef->api->setAPIResponse('Error','Password missing from request'));
        $FN = $data['fn'] ?? null;
        $SN = $data['sn'] ?? null;
        $EM = $data['em'] ?? null;
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
	$phpef = ($request->getAttribute('ib')) ?? new phpef();
    $data = $phpef->api->getAPIRequestData($request);
    if ($phpef->auth->checkAccess("ADMIN-USERS")) {
        if (isset($args['id'])) {
            $FN = $data['fn'] ?? null;
            $SN = $data['sn'] ?? null;
            $EM = $data['em'] ?? null;
            $UN = $data['un'] ?? null;
            $PW = $data['pw'] ?? null;
            $Groups = $data['groups'] ?? null;
            // if (!$FN && !$SN && !$EM && !$UN && !$PW && $Groups) {
            //     $phpef->api->setAPIResponseMessage('Nothing to update');
            // } else {
                $phpef->auth->updateUser($args['id'],$UN,$PW,$FN,$SN,$EM,$Groups);
            // }
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
	$phpef = ($request->getAttribute('ib')) ?? new phpef();
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