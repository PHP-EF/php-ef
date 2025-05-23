<?php
$app->get('/config', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();

    if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
        $config = $phpef->config->get();
        if (!empty($config['Security']['salt'])) {
            $config['Security']['salt'] = "********";
        }
        if (!empty($config['LDAP']['service_password'])) {
            $config['LDAP']['service_password'] = "********";
        }
        if (!empty($config['SAML']['sp']['privateKey'])) {
            $config['SAML']['sp']['privateKey'] = "********";
        }
        $config['SAML']['idp']['x509cert'] = substr($config['SAML']['idp']['x509cert'],0,24).'...';
        $phpef->api->setAPIResponseData($config);
        $phpef->logging->writeLog("Config","Queried Configuration","info",$_REQUEST);
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->patch('/config', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
        $data = $phpef->api->getAPIRequestData($request);
        $config = $phpef->config->get();
        // Update the config values with the submitted data
        $phpef->config->set($config, $data);
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/config/plugins', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
        $phpef->api->setAPIResponseData($phpef->plugins->getInstalledPlugins());
    }
	// Return the response
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/config/plugins/{plugin}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
        $phpef->api->setAPIResponseData($phpef->config->get('Plugins',$args['plugin']));
    }
	// Return the response
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->patch('/config/plugins/{plugin}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
        $data = $phpef->api->getAPIRequestData($request);
        // Update the config values with the submitted data
        $phpef->config->setPlugin($data, $args['plugin']);
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/config/widgets/{widget}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
        $phpef->api->setAPIResponseData($phpef->config->get('Widgets',$args['widget']) ?? []);
    }
	// Return the response
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->patch('/config/widgets/{widget}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
        $data = $phpef->api->getAPIRequestData($request);
        // Update the config values with the submitted data
        $phpef->config->setWidget($data, $args['widget']);
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/config/dashboards/{dashboard}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
        $phpef->api->setAPIResponseData($phpef->config->get('Dashboards',$args['dashboard']) ?? []);
    }
	// Return the response
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/config/dashboards', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
        $data = $phpef->api->getAPIRequestData($request);
        if (isset($data['Name'])) {
            $config = $phpef->config->get();
            // Update the config values with the submitted data
            $phpef->config->setDashboard($config, $data, $data['Name']);
            $phpef->api->setAPIResponseMessage('Successfully created dashboard');
        } else {
            $phpef->api->setAPIResponse('Error','Name missing from request');
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->delete('/config/dashboards/{dashboard}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
        $config = $phpef->config->get();
        $data = $config['Dashboards'] ?? [];
        if ($data[$args['dashboard']]) {
            $phpef->config->removeConfig($config,'Dashboards',$args['dashboard']);
            $phpef->api->setAPIResponseMessage('Successfully deleted dashboard');
        } else {
            $phpef->api->setAPIResponse('Error','Dashboard not found');
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->patch('/config/dashboards/{dashboard}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
        $data = $phpef->api->getAPIRequestData($request);
        $config = $phpef->config->get();
        // Update the config values with the submitted data
        $phpef->config->setDashboard($config, $data, $args['dashboard']);
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// ** BACKUPS ** //
$app->get('/config/backups', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
        $phpef->getBackups();
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/config/backup/{filename}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
        $filePath = $phpef->downloadBackup($args['filename']);

        if ($filePath) {
            $phpef->logging->writeLog('Backup', 'Backup file downloaded: ' . $filePath, 'Info');
            $response->getBody()->write(file_get_contents($filePath));
            return $response
                ->withHeader('Content-Type', 'application/zip')
                ->withHeader('Cache-Control', 'must-revalidate')
                ->withHeader('Content-Description', 'File Transfer')
                ->withHeader('Content-Disposition', 'attachment; filename="' . basename($filePath) . '"')
                ->withHeader('Content-Length', filesize($filePath))
                ->withStatus($GLOBALS['responseCode']);
        } else {
            $phpef->api->setAPIResponse('Error','Failed to locate backup file');
        }
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/config/backup', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
        $phpef->backup(true);
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->delete('/config/backup/{filename}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
        $phpef->deleteBackup($args['filename']);
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});