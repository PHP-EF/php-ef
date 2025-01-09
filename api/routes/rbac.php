<?php
$app->get('/rbac/group/{id}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-RBAC")) {
        if (isset($args['id'])) {
            $phpef->api->setAPIResponseData($phpef->auth->getRBACGroupById($args['id']));
        } else {
            $phpef->api->setAPIResponse('Error','id missing from request',400);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/rbac/groups', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-RBAC")) {
        $phpef->api->setAPIResponseData($phpef->auth->getRBACGroups());
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/rbac/groups/protected', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-RBAC")) {
        $phpef->api->setAPIResponseData($phpef->auth->getRBACGroups(true));
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/rbac/groups/configurable', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-RBAC")) {
        $phpef->api->setAPIResponseData($phpef->auth->getRBACGroups(null,true));
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/rbac/groups', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-RBAC")) {
        $data = $phpef->api->getAPIRequestData($request);
        if (isset($data['name'])) {
            $Description = $data['description'] ?? null;
            $phpef->auth->newRBACGroup($data['name'],$Description);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->patch('/rbac/group/{id}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    $data = $phpef->api->getAPIRequestData($request);
    if ($phpef->auth->checkAccess("ADMIN-RBAC")) {
        if (isset($args['id'])) {
            $GroupName = $data['name'] ?? null;
            $Description = $data['description'] ?? null;
            $Key = $data['key'] ?? null;
            $Value = $data['value'] ?? null;
            if (!$GroupName && !$Description && !$Key && !$Value) {
                $phpef->api->setAPIResponseMessage('Nothing to update');
            } else {
                $phpef->auth->updateRBACGroup($args['id'],$GroupName,$Description,$Key,$Value);
            }
        } else {
            $phpef->api->setAPIResponse('Error','id missing from request',400);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->delete('/rbac/group/{id}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-RBAC")) {
        if (isset($args['id'])) {
            $phpef->auth->deleteRBACGroup($args['id']);
        } else {
            $phpef->api->setAPIResponse('Error','id missing from request',400);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/rbac/roles', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-RBAC")) {
        $phpef->api->setAPIResponseData($phpef->auth->getRBACRoles());
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/rbac/roles', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-RBAC")) {
        $data = $phpef->api->getAPIRequestData($request);
        if (isset($data['name'])) {
            $Description = $data['description'] ?? null;
            $phpef->auth->newRBACRole($data['name'],$Description);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->patch('/rbac/role/{id}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-RBAC")) {
        $data = $phpef->api->getAPIRequestData($request);
        if (isset($args['id'])) {
            $RoleName = $data['name'] ?? null;
            $RoleDescription = $data['description'] ?? null;
            $phpef->auth->updateRBACRole($args['id'],$RoleName,$RoleDescription);
        } else {
            $phpef->api->setAPIResponse('Error','id missing from request',400);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->delete('/rbac/role/{id}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-RBAC")) {
        if (isset($args['id'])) {
            $phpef->auth->deleteRBACRole($args['id']);
        } else {
            $phpef->api->setAPIResponse('Error','id missing from request',400);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/rbac/checkAccess', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    $data = $request->getQueryParams();
    if (isset($data['node'])) {
        $Result = array(
            "node" => $_REQUEST['node']
        );
        if ($phpef->auth->checkAccess($data['node'])) {
            $Result['permitted'] = true;
        } else {
            $Result['permitted'] = false;
        }
        $phpef->api->setAPIResponseData($Result);
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});