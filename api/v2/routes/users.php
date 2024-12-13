<?php
$app->get('/users', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->rbac->checkAccess("ADMIN-USERS")) {
        $ib->api->setAPIResponseData($ib->auth->getAllUsers());
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/users', function ($request, $response, $args) {
    $ib = ($request->getAttribute('ib')) ?? new ib();
    $data = $ib->api->getAPIRequestData($request);
    if ($ib->rbac->checkAccess("ADMIN-USERS")) {
        $UN = $data['un'] ?? exit($ib->api->setAPIResponse('Error','Username missing from request'));
        $PW = $data['pw'] ?? exit($ib->api->setAPIResponse('Error','Password missing from request'));
        $FN = $data['fn'] ?? exit($ib->api->setAPIResponse('Error','Firstname missing from request'));
        $SN = $data['sn'] ?? exit($ib->api->setAPIResponse('Error','Surname missing from request'));
        $EM = $data['em'] ?? exit($ib->api->setAPIResponse('Error','Email missing from request'));
        $Groups = $data['groups'] ?? null;
        $Expire = $data['expire'] ?? 'false';
        $ib->auth->newUser($UN,$PW,$FN,$SN,$EM,$Groups,'Local',$Expire);
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->put('/user/{id}', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    $data = $ib->api->getAPIRequestData($request);
    if ($ib->rbac->checkAccess("ADMIN-USERS")) {
        if (isset($args['id'])) {
            $FN = $data['fn'] ?? null;
            $SN = $data['sn'] ?? null;
            $EM = $data['em'] ?? null;
            $UN = $data['un'] ?? null;
            $PW = $data['pw'] ?? null;
            $Groups = $data['groups'] ?? null;
            if (!$FN && !$SN && !$EM && !$UN && !$PW && !$Groups) {
                $ib->api->setAPIResponseMessage('Nothing to update');
            } else {
                $ib->auth->updateUser($args['id'],$UN,$PW,$FN,$SN,$EM,$Groups);
            }
        } else {
            $ib->api->setAPIResponse('Error','id missing from request',400);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->delete('/user/{id}', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->rbac->checkAccess("ADMIN-USERS")) {
        if (isset($args['id'])) {
            $ib->auth->removeUser($args['id']);
        } else {
            $ib->api->setAPIResponse('Error','id missing from request',400);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});