<?php
$app->get('/rbac/group/{id}', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->rbac->checkAccess("ADMIN-RBAC")) {
        if (isset($args['id'])) {
            $ib->api->setAPIData($ib->rbac->getRBACGroupById($args['id']));
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
    if ($ib->rbac->checkAccess("ADMIN-RBAC")) {
        $ib->api->setAPIData($ib->rbac->getRBACGroups());
    }

	$response->getBody()->write(jsonE($GLOBALS['api']['response']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/rbac/groups/protected', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->rbac->checkAccess("ADMIN-RBAC")) {
        $ib->api->setAPIData($ib->rbac->getRBACGroups(true));
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/rbac/groups/configurable', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    if ($ib->rbac->checkAccess("ADMIN-RBAC")) {
        $ib->api->setAPIData($ib->rbac->getRBACGroups(null,true));
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->put('/rbac/group/{id}', function ($request, $response, $args) {
	$ib = ($request->getAttribute('ib')) ?? new ib();
    $data = $ib->api->getAPIData($request);
    if ($ib->rbac->checkAccess("ADMIN-RBAC")) {
        if (isset($args['id'])) {
            if (isset($data['name'])) { $GroupName = $data['name']; } else { $GroupName = null; }
            if (isset($data['description'])) { $Description = $data['description']; } else { $Description = null; }
            if (isset($data['key'])) { $Key = $data['key']; } else { $Key = null; }
            if (isset($data['value'])) { $Value = $data['value']; } else { $Value = null; }
            if (!$GroupName && !$Description && !$Key && !$Value) {
                $ib->api->setAPIMessage('Nothing to update');
            } else {
                $ib->rbac->updateRBACGroup($args['id'],$GroupName,$Description,$Key,$Value);
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