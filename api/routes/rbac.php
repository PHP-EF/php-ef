<?php
$app->get('/rbac/group/{id}', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->auth->checkAccess("ADMIN-RBAC")) {
        if (isset($args['id'])) {
            $ib->api->setAPIResponseData($ib->auth->getRBACGroupById($args['id']));
        } else {
            $ib->api->setAPIResponse('Error','id missing from request',400);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/rbac/groups', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->auth->checkAccess("ADMIN-RBAC")) {
        $ib->api->setAPIResponseData($ib->auth->getRBACGroups());
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/rbac/groups/protected', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->auth->checkAccess("ADMIN-RBAC")) {
        $ib->api->setAPIResponseData($ib->auth->getRBACGroups(true));
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/rbac/groups/configurable', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->auth->checkAccess("ADMIN-RBAC")) {
        $ib->api->setAPIResponseData($ib->auth->getRBACGroups(null,true));
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/rbac/groups', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->auth->checkAccess("ADMIN-RBAC")) {
        $data = $ib->api->getAPIRequestData($request);
        if (isset($data['name'])) {
            $Description = $data['description'] ?? null;
            $ib->auth->newRBACGroup($data['name'],$Description);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->patch('/rbac/group/{id}', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    $data = $ib->api->getAPIRequestData($request);
    if ($ib->auth->checkAccess("ADMIN-RBAC")) {
        if (isset($args['id'])) {
            $GroupName = $data['name'] ?? null;
            $Description = $data['description'] ?? null;
            $Key = $data['key'] ?? null;
            $Value = $data['value'] ?? null;
            if (!$GroupName && !$Description && !$Key && !$Value) {
                $ib->api->setAPIResponseMessage('Nothing to update');
            } else {
                $ib->auth->updateRBACGroup($args['id'],$GroupName,$Description,$Key,$Value);
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

$app->delete('/rbac/group/{id}', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->auth->checkAccess("ADMIN-RBAC")) {
        if (isset($args['id'])) {
            $ib->auth->deleteRBACGroup($args['id']);
        } else {
            $ib->api->setAPIResponse('Error','id missing from request',400);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/rbac/roles', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->auth->checkAccess("ADMIN-RBAC")) {
        $ib->api->setAPIResponseData($ib->auth->getRBACRoles());
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/rbac/roles', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->auth->checkAccess("ADMIN-RBAC")) {
        $data = $ib->api->getAPIRequestData($request);
        if (isset($data['name'])) {
            $Description = $data['description'] ?? null;
            $ib->auth->newRBACRole($data['name'],$Description);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->patch('/rbac/role/{id}', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->auth->checkAccess("ADMIN-RBAC")) {
        $data = $ib->api->getAPIRequestData($request);
        if (isset($args['id'])) {
            $RoleName = $data['name'] ?? null;
            $RoleDescription = $data['description'] ?? null;
            $ib->auth->updateRBACRole($args['id'],$RoleName,$RoleDescription);
        } else {
            $ib->api->setAPIResponse('Error','id missing from request',400);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->delete('/rbac/role/{id}', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->auth->checkAccess("ADMIN-RBAC")) {
        if (isset($args['id'])) {
            $ib->auth->deleteRBACRole($args['id']);
        } else {
            $ib->api->setAPIResponse('Error','id missing from request',400);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/rbac/checkAccess', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    $data = $request->getQueryParams();
    if (isset($data['node'])) {
        $Result = array(
            "node" => $_REQUEST['node']
        );
        if ($ib->auth->checkAccess($data['node'])) {
            $Result['permitted'] = true;
        } else {
            $Result['permitted'] = false;
        }
        $ib->api->setAPIResponseData($Result);
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});