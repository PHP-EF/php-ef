<?php
$app->get('/users', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->rbac->checkAccess("ADMIN-USERS")) {
        $ib->api->setAPIData($ib->auth->getAllUsers());
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->put('/user/{id}', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    $data = $ib->api->getAPIData($request);
    if ($ib->rbac->checkAccess("ADMIN-USERS")) {
        if (isset($args['id'])) {
            if (isset($data['fn'])) {$FN = $data['fn'];} else {$FN = null;}
            if (isset($data['sn'])) {$SN = $data['sn'];} else {$SN = null;}
            if (isset($data['em'])) {$EM = $data['em'];} else {$EM = null;}
            if (isset($data['un'])) {$UN = $data['un'];} else {$UN = null;}
            if (isset($data['pw'])) {$PW = $data['pw'];} else {$PW = null;}
            if (isset($data['groups'])) {$Groups = $data['groups'];} else {$Groups = null;}
            $ib->auth->updateUser($args['id'],$UN,$PW,$FN,$SN,$EM,$Groups);
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