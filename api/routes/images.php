<?php
// Get a list of custom images
$app->get('/images', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
    	$phpef->api->setAPIResponseData($phpef->getAllImages());
	}
	// Return the response
    $response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

// Upload Custom Image
$app->post('/images', function ($request, $response, $args) {
    $phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
        $uploadedFiles = $request->getUploadedFiles();
        $postData = $request->getParsedBody();
        $uploadDir = $phpef->getImagesDir();
        // Handle image upload
        if (isset($uploadedFiles['file']) && $uploadedFiles['file']->getError() == UPLOAD_ERR_OK) {
            if (isset($postData['fileName'])) {
                $fileName = basename($uploadedFiles['file']->getClientFilename());
                $filePath = $uploadDir . urldecode($postData['fileName']);

                if (isValidFileType($fileName, ['svg','png','jpeg','.gif'])) {
                    // Move the uploaded file to the designated directory
                    $uploadedFiles['file']->moveTo($filePath);
                    $phpef->api->setAPIResponseMessage("Image uploaded successfully: $fileName");
                } else {
					$phpef->api->setAPIResponse("Error","Invalid File Type: $fileName");
                }
            } else {
				$phpef->api->setAPIResponse("Error","Image File Name Missing");
            }
        }
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Delete Custom Image
$app->delete('/images', function ($request, $response, $args) {
    $phpef = ($request->getAttribute('phpef')) ?? new phpef();
    if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
        $data = $request->getQueryParams();
        if (isset($data['fileName'])) {
            $ImagesDir = $phpef->getImagesDir();
            $fileName = $data['fileName'];
            $filePath = $ImagesDir . DIRECTORY_SEPARATOR . $fileName;
            if (file_exists($filePath)) {
                if (is_writable($filePath)) {
                    unlink($filePath);
                    $phpef->api->setAPIResponseMessage("Image deleted successfully: $fileName");
                } else {
                    $phpef->api->setAPIResponse("Error","Insufficient permissions to delete this: $fileName");
                }
            } else {
                $phpef->api->setAPIResponse("Error","Image file does not exist: $fileName");
            }
        } else {
            $phpef->api->setAPIResponse("Error","Image File Name Missing");
        }
    }

	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});

// Get a custom image from a plugin
$app->get('/image/plugin/{plugin}/{image}/{ext}', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
    $plugin = sanitizePage($args['plugin']);
    $image = sanitizePage($args['image']);
    $ext = sanitizePage($args['ext']);
    $pluginDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $plugin;
    // Check if plugin custom image exists
    if (file_exists($pluginDir.'/images/'.$image.'.'.$ext)) {
        // Return the image
        $imagePath = $pluginDir.'/images/'.$image.'.'.$ext;
        $imageContent = file_get_contents($imagePath);
        $response->getBody()->write($imageContent);
        return $response->withHeader('Content-Type', mime_content_type($imagePath))->withStatus(200);
    } else {
        // Return a JSON response if the image does not exist
        $response->getBody()->write(json_encode(['error' => 'Image not found']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
});