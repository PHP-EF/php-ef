<?php
class api {
	public function setAPIResponse($result = null, $message = null, $responseCode = null, $data = null) {
		if ($result) {
			$GLOBALS['api']['result'] = $result;
		}
		if ($message) {
			$GLOBALS['api']['message'] = $message;
		}
		if ($responseCode) {
			$GLOBALS['responseCode'] = $responseCode;
		}
		if ($data) {
			$GLOBALS['api']['data'] = $data;
		}
	}

	public function setAPIResponseMessage($message = null) {
		if ($message) {
			$this->setAPIResponse(null,$message);
		}
	}

	public function setAPIResponseCode($responseCode = null) {
		if ($responseCode) {
			$this->setAPIResponse(null,null,$responseCode);
		}
	}

	public function setAPIResponseData($data = null) {
		if ($data) {
			$this->setAPIResponse(null,null,null,$data);
		}
	}

    public function getAPIRequestData($request, $decode = true) {
		switch ($request->getMethod()) {
			case 'POST':
				if (stripos($request->getHeaderLine('Content-Type'), 'application/json') !== false) {
					return $decode ? json_decode(file_get_contents('php://input', 'r'), true) : file_get_contents('php://input', 'r');
				} else {
					return $request->getParsedBody();
				}
			default:
				if (stripos($request->getHeaderLine('Content-Type'), 'application/json') !== false) {
					return $decode ? json_decode(file_get_contents('php://input', 'r'), true) : file_get_contents('php://input', 'r');
				} else {
					return null;
				}
		}
	}
}