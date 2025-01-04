<?php
class api {
	public $query;

	public function __construct($core = null) {
		if ($core) {
			$this->query = new query($core);
		}
	}

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

class query {
	private $config;
	private $logging;

	public function __construct($core) {
		// Set Config
		$this->config = $core->config;
		$this->logging = $core->logging;
	}

	private function buildOptions($Options = null) {
		$DefaultOptions = array(
			'timeout' => $this->config->get("System","CURL-Timeout"),
			'connect_timeout' => $this->config->get("System","CURL-ConnectTimeout"),
			'verify' => false
		);
		if ($Options) {
			return array_merge($DefaultOptions,$Options);
		} else {
			return $DefaultOptions;
		}
	}

	private function buildHeaders($Headers = null) {
		$DefaultHeaders = array(
			'Content-Type' => "application/json"
		);
		if ($Headers) {
			return array_merge($DefaultHeaders,$Headers);
		} else {
			return $DefaultHeaders;
		}
	}

	private function prepareData($Data, $HeadersArr) {
		if (isset($HeadersArr['Content-Type']) && $HeadersArr['Content-Type'] === 'application/json') {
			if (is_array($Data) || is_object($Data)) {
				return json_encode($Data);
			}
		}
		return $Data;
	}

	public function decodeResponse($response,$raw = false) {
		if ($raw) {
			return $response;
		}
		if (isset($response->status_code)) {
			if ($response->status_code >= 400 && $response->status_code < 600) {
				return $response;
			}
		}
		$contentType = $response->headers['content-type'] ?? '';
		if (strpos($contentType, 'application/json') !== false || strpos($contentType, 'text/plain') !== false) {
			$decoded = json_decode($response->body, true);
			if (json_last_error() === JSON_ERROR_NONE) {
				return $decoded;
			}
		}
		return $response;
	}

	public function get($Url,$Headers = null,$Options = null,$raw = false) {
		$OptionsArr = $this->buildOptions($Options);
		$HeadersArr = $this->buildHeaders($Headers);
		$Result = WpOrg\Requests\Requests::get($Url, $HeadersArr, $OptionsArr);
		return $this->decodeResponse($Result,$raw);
	}

	public function post($Url,$Data,$Headers = null,$Options = null,$raw = false) {
		$OptionsArr = $this->buildOptions($Options);
		$HeadersArr = $this->buildHeaders($Headers);
		$Data = $this->prepareData($Data, $HeadersArr);
		$Result = WpOrg\Requests\Requests::post($Url, $HeadersArr, $Data, $OptionsArr);
		return $this->decodeResponse($Result,$raw);
	}

	public function put($Url,$Data,$Headers = null,$Options = null,$raw = false) {
		$OptionsArr = $this->buildOptions($Options);
		$HeadersArr = $this->buildHeaders($Headers);
		$Data = $this->prepareData($Data, $HeadersArr);
		$Result = WpOrg\Requests\Requests::put($Url, $HeadersArr, $Data, $OptionsArr);
		return $this->decodeResponse($Result,$raw);
	}

	public function patch($Url,$Data,$Headers = null,$Options = null,$raw = false) {
		$OptionsArr = $this->buildOptions($Options);
		$HeadersArr = $this->buildHeaders($Headers);
		$Data = $this->prepareData($Data, $HeadersArr);
		$Result = WpOrg\Requests\Requests::patch($Url, $HeadersArr, $Data, $OptionsArr);
		return $this->decodeResponse($Result,$raw);
	}

	public function delete($Url,$Data,$Headers = null,$Options = null,$raw = false) {
		$OptionsArr = $this->buildOptions($Options);
		$HeadersArr = $this->buildHeaders($Headers);
		$Result = WpOrg\Requests\Requests::delete($Url, $HeadersArr, $OptionsArr);
		return $this->decodeResponse($Result,$raw);
	}
}