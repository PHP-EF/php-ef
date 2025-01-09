<?php
$app->get('/images', function ($request, $response, $args) {
	$phpef = ($request->getAttribute('phpef')) ?? new phpef();
	if ($phpef->auth->checkAccess("ADMIN-CONFIG")) {
    	$phpef->api->setAPIResponseData($phpef->getImages());
	}
	// Return the response
    $response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json')
		->withStatus($GLOBALS['responseCode']);
});

// Upload Image(s) For Configured Threat Actor
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
