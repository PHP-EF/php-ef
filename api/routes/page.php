<?php
$app->get('/page/{category}/{page}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	$category = sanitizePage($args['category']);
	$page = sanitizePage($args['page']);
	$pagePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . $category . DIRECTORY_SEPARATOR . $page . '.php';
	if (file_exists($pagePath)) {
		$Include = include_once($pagePath);
		if (!$Include === false) {
			$phpef->api->setAPIResponseData($Include);
			$response->getBody()->write($GLOBALS['api']['data']);
		} else {
			$response->getBody()->write(jsonE($GLOBALS['api']));
		}
	} else {
		$phpef->api->setAPIResponse('Error','Page not found',404);
		$response->getBody()->write(jsonE($GLOBALS['api']));
	}

	// Return the response
	return $response
		->withHeader('Content-Type', 'text/html')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/page/plugin/{plugin}/js', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	$plugin = sanitizePage($args['plugin']);
	$pluginDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $plugin;

	// Get Custom JS
	if (file_exists($pluginDir.'/main.js')) {
		$jsContent = file_get_contents($pluginDir.'/main.js');
		$phpef->api->setAPIResponseData($jsContent);
		$response->getBody()->write($GLOBALS['api']['data']);
	} else {
		$response->getBody()->write(jsonE($GLOBALS['api']));
	}

	// Return the response
	return $response
		->withHeader('Content-Type', 'text/javascript')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/page/plugin/{plugin}/css', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	$plugin = sanitizePage($args['plugin']);
	$pluginDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $plugin;

	// Get Custom JS
	if (file_exists($pluginDir.'/styles.css')) {
		$cssContent = file_get_contents($pluginDir.'/styles.css');
		$phpef->api->setAPIResponseData($cssContent);
		$response->getBody()->write($GLOBALS['api']['data']);
	} else {
		$response->getBody()->write(jsonE($GLOBALS['api']));
	}

	// Return the response
	return $response
		->withHeader('Content-Type', 'text/css')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/page/plugin/{plugin}/{page}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();

	$plugin = sanitizePage($args['plugin']);
	$page = sanitizePage($args['page']);
	
	$pluginDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $plugin;
	$pagePath = $pluginDir . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . $page . '.php';
	if (file_exists($pagePath)) {
		$html = '';
		$html .= '<link href="/api/page/plugin/'.$plugin.'/css" rel="stylesheet">';

		$html .= '
		<script>
			var pluginJSSrc = "/api/page/plugin/'.$plugin.'/js";
			appendScript({ src: pluginJSSrc });
		</script>
		';

		$Include = include_once($pagePath);
		if (!$Include === false) {
			$html .= $Include;
		} else {
			$response->getBody()->write(jsonE($GLOBALS['api']));
		}
		$phpef->api->setAPIResponseData($html);
		$response->getBody()->write($GLOBALS['api']['data']);
	} else {
		$phpef->api->setAPIResponse('Error','Page not found',404);
		$response->getBody()->write(jsonE($GLOBALS['api']));
	}

	// Return the response
	return $response
		->withHeader('Content-Type', 'text/html')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/pages', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	$data = $request->getQueryParams();
    if ($phpef->auth->checkAccess("ADMIN-PAGES")) {
		$Menu = $data['menu'] ?? null;
		$SubMenu = $data['submenu'] ?? null;
		if ($Menu || $SubMenu) {
			$phpef->api->setAPIResponseData($phpef->pages->getByMenu($Menu,$SubMenu));
		} else {
			$phpef->api->setAPIResponseData($phpef->pages->get());	
		}
	}

	// Return the response
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->post('/pages', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-PAGES")) {
        $data = $phpef->api->getAPIRequestData($request);
        $Name = $data['name'] ?? exit($phpef->api->setAPIResponse('Error','Name missing from request'));
        $Title = $data['title'] ?? null;
        $Type = $data['type'] ?? null;
		$Url = $data['url'] ?? null;
        $Menu = $data['menu'] ?? null;
        $Submenu = $data['submenu'] ?? null;
		$ACL = $data['acl'] ?? null;
		$Icon = $data['icon'] ?? null;
		$LinkType = $data['linktype'] ?? null;
		$phpef->pages->new($Name,$Title,$Type,$Url,$Menu,$Submenu,$ACL,$Icon,$LinkType);
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->patch('/page/{id}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-PAGES")) {
        if (isset($args['id'])) {
			$data = $phpef->api->getAPIRequestData($request);
			$Name = $data['name'] ?? null;
			$Title = $data['title'] ?? null;
			$Type = $data['type'] ?? null;
			$Url = $data['url'] ?? null;
			$Menu = $data['menu'] ?? null;
			$Submenu = $data['submenu'] ?? null;
			$ACL = $data['acl'] ?? null;
			$Icon = $data['icon'] ?? null;
			$LinkType = $data['linktype'] ?? null;
			$phpef->pages->set($args['id'],$Name,$Title,$Type,$Url,$Menu,$Submenu,$ACL,$Icon,$LinkType);
        } else {
            $phpef->api->setAPIResponse('Error','id missing from request',400);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Update CMDB Column Weight
$app->patch('/page/{id}/weight', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-PAGES")) {
		$data = $phpef->api->getAPIRequestData($request);
		if (isset($data['weight'])) {
			$phpef->pages->updatePageWeight($args['id'],$data['weight']);
		} else {
			$phpef->api->setAPIResponse('Error','Weight missing from request');
		}        
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});


$app->delete('/page/{id}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-PAGES")) {
        if (isset($args['id'])) {
            $phpef->pages->delete($args['id']);
        } else {
            $phpef->api->setAPIResponse('Error','id missing from request',400);
        }
    }
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/pages/hierarchy', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	if ($phpef->auth->checkAccess("ADMIN-PAGES")) {
		$config = $phpef->pages->get();
		$navigation = [];

		// First pass: Process Menus and Submenus
		foreach ($config as $item) {
			if ($item['Type'] === 'Menu') {
				$navigation[$item['Name']] = $item;
				$navigation[$item['Name']]['Items'] = [];
			} elseif ($item['Type'] === 'SubMenu') {
				if (!isset($navigation[$item['Menu']]['Items'])) {
					$navigation[$item['Menu']]['Items'] = [];
				}
				$navigation[$item['Menu']]['Items'][$item['Name']] = $item;
				$navigation[$item['Menu']]['Items'][$item['Name']]['Items'] = [];
			}
		}
		
		// Second pass: Process Links, MenuLinks, and SubMenuLinks
		foreach ($config as $item) {
			if ($item['Type'] === 'Link') {
				$navigation[] = $item;
			} elseif ($item['Type'] === 'MenuLink') {
				if (isset($navigation[$item['Menu']]['Items'])) {
					$navigation[$item['Menu']]['Items'][] = $item;
				}
			} elseif ($item['Type'] === 'SubMenuLink') {
				if (isset($navigation[$item['Menu']]['Items'][$item['Submenu']]['Items'])) {
					$navigation[$item['Menu']]['Items'][$item['Submenu']]['Items'][] = $item;
				}
			}
		}
	}

	$phpef->api->setAPIResponseData($navigation);

	// Return the response
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/pages/list', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	if ($phpef->auth->checkAccess("ADMIN-PAGES")) {
		$phpef->api->setAPIResponseData($phpef->pages->getAllAvailablePages());
	}

	// Return the response
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/pages/root', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-PAGES")) {
		$phpef->api->setAPIResponseData($phpef->pages->getMainLinksAndMenus());	
	}

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/pages/menus', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-PAGES")) {
		$phpef->api->setAPIResponseData($phpef->pages->getByType('Menu'));	
	}

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

$app->get('/pages/submenus', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	$data = $request->getQueryParams();
    if ($phpef->auth->checkAccess("ADMIN-PAGES")) {
		$Menu = $data['menu'] ?? null;
		$phpef->api->setAPIResponseData($phpef->pages->getByType('SubMenu',$Menu));	
	}

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});