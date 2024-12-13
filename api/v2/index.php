<?php
$SkipCSS = true;
require_once(__DIR__.'/../../inc/inc.php');

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\StreamInterface;
use Slim\Factory\AppFactory;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

$GLOBALS['api'] = array(
	'response' => array(
		'result' => 'Success',
		'message' => null,
		'data' => null
	)
);
$GLOBALS['responseCode'] = 200;

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);
$app->setBasePath(getBasePath());
$app->add(function ($request, $handler) {
	// add the ib class to the request as read-only
	$ib = new ib();
	$request = $request->withAttribute('ib', $ib);
	// set custom error handler
	// set_error_handler([$ib, 'setAPIErrorResponse']);
	return $handler->handle($request);
});

/*
 * Include all routes
 */
foreach (glob(__DIR__ . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . '*.php') as $filename) {
	require_once $filename;
}

/*
 * Include all Plugin routes
 */
if (file_exists(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'plugins')) {
	$folder = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'plugins';
	$directoryIterator = new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS);
	$iteratorIterator = new RecursiveIteratorIterator($directoryIterator);
	foreach ($iteratorIterator as $info) {
		if ($info->getFilename() == 'api.php') {
			require_once $info->getPathname();
		}
	}
}

/*
 *
 *  This is the last defined api endpoint to catch all undefined endpoints
 *
 */
$app->any('{route:.*}', function ($request, $response) {
	$GLOBALS['api']['response']['data'] = array(
		'endpoint' => $request->getUri()->getPath(),
		'method' => $request->getMethod(),
	);
	$GLOBALS['api']['response']['result'] = 'error';
	$GLOBALS['api']['response']['message'] = 'Endpoint Not Found or Defined';
	$GLOBALS['responseCode'] = 404;
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
$app->run();