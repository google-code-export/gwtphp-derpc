<?php

require_once PHPRPC_ROOT . 'config.php';

require_once PHPRPC_ROOT . 'classes.php';
require_once PHPRPC_ROOT . 'collections.php';
require_once PHPRPC_ROOT . 'serialization.php';
require_once PHPRPC_ROOT . 'GWT.php';

require_once PHPRPC_ROOT . 'rpc/RPC.php';
require_once PHPRPC_ROOT . 'rpc/SerializationPolicyLoader.php';

class RemoteServiceServlet implements SerializationPolicyProvider {
	
	const STRONG_NAME_HEADER = 'HTTP_X_GWT_PERMUTATION';
	
	private static function loadSerializationPolicy($moduleBaseURL, $strongName) {
		// The request can tell you the path of the web app relative to the
		// container root
		$contextPath = parse_url($_SERVER['SCRIPT_NAME'], PHP_URL_PATH);
		
		if (!is_null($moduleBaseURL)) {
			$modulePath = parse_url($moduleBaseURL, PHP_URL_PATH);
		}
		/*
		 * Check that the module path must be in the same web app as the servlet
		 * itself. If you need to implement a scheme different than this, override
		 * this method.
		 */
		if (empty($modulePath) || mb_strpos($modulePath, $contextPath) != 0) {
			$message = 'ERROR: The module path requested, '
				. $modulePath
				. ', is not in the same web application as this servlet, '
				. $contextPath
				. '. Your module may not be properly configured or your client and server code maybe out of date.';
			echo $message;
		}
		else {
			// Strip off the context path from module base URL. It should be
			// strict prefix
			//$contextRelativePath = mb_substr($modulePath, mb_strlen($contextPath));
			$contextRelativePath = GWT::getModuleBasePath();
			
			$serializationPolicyFilePath = SerializationPolicyLoader::getSerializationPolicyFileName($contextRelativePath . $strongName);
			
			// Open the RPC resource file and read its content
			try {
				$serializationPolicy = SerializationPolicyLoader::loadFromFile($serializationPolicyFilePath);
			}
			catch (ParseException $e) {
				echo "ERROR: Failed to parse the policy file '$serializationPolicyFilePath'", $e;
			}
			return $serializationPolicy;
		}
	}
	
	public static function run($delegate) {
		$servlet = new self($delegate);
		$servlet->processPost();
	}
	
	private $serializationPolicyCache = array();
	
	private $delegate;
	
	public function __construct($delegate = null) {
		$this->delegate = $delegate;
		if (is_null($this->delegate)) {
			$this->delegate = $this;
		}
	}
	
	public function getSerializationPolicy($moduleBaseURL, $strongName) {
		$serializationPolicy = $this->getCachedSerializationPolicy($moduleBaseURL, $strongName);
		if (!is_null($serializationPolicy)) {
			return $serializationPolicy;
		}
		
		$serializationPolicy = $this->doGetSerializationPolicy($moduleBaseURL, $strongName);
		if (is_null($serializationPolicy)) {
			// Failed to get the requested serialization policy; use the default
			/*log(
          	"WARNING: Failed to get the SerializationPolicy '"
              + strongName
              + "' for module '"
              + moduleBaseURL
              + "'; a legacy, 1.3.3 compatible, serialization policy will be used.  You may experience SerializationExceptions as a result.");
			*/
			$serializationPolicy = RPC::getDefaultSerializationPolicy();
		}
		
		// This could cache null or an actual instance. Either way we will not
		// attempt to lookup the policy again.
		$this->putCachedSerializationPolicy($moduleBaseURL, $strongName, $serializationPolicy);
		
		return $serializationPolicy;
	}
	
	public function processCall($payload) {
		$this->checkPermutationStrongName();
		
		try {
			$rpcRequest = RPC::decodeRequest($payload, Classes::classOf($this->delegate), $this);
			$this->onAfterRequestDeserialized($rpcRequest);
			return RPC::invokeAndEncodeResponse($this->delegate, $rpcRequest->getMethod(), 
				$rpcRequest->getParameters(), $rpcRequest->getSerializationPolicy(),
				$rpcRequest->getFlags());
		}
		catch (IncompatibleRemoteServiceException $ex) {
			echo $ex;
			/*log(
			 "An IncompatibleRemoteServiceException was thrown while processing this call.",
			 ex);
			 */
			return RPC::encodeResponseForFailure(null, $ex);
		}
		catch (RpcTokenException $ex) {
			//log("An RpcTokenException was thrown while processing this call.",
			//tokenException);
			return RPC::encodeResponseForFailure(null, $ex);
		}
	}
	
	public function processPost() {
		$requestPayload = file_get_contents('php://input');
		$this->onBeforeRequestDeserialized($requestPayload);
		$responsePayload = $this->processCall($requestPayload);
		$this->onAfterResponseSerialized($responsePayload);
		$this->writeResponse($responsePayload);
	}
	
	protected function checkPermutationStrongName() {
		if (is_null($this->getPermutationStrongName())) {
			throw new SecurityException('Blocked request without GWT permutation header (XSRF attack?)');
		}
	}
	
	protected function doGetSerializationPolicy($moduleBaseURL, $strongName) {
		return self::loadSerializationPolicy($moduleBaseURL, $strongName);
	}
	
	protected function onAfterRequestDeserialized($deserializedRequest) {
	}

	protected function onAfterResponseSerialized($serializedResponse) {
	}
	
	protected function onBeforeRequestDeserialized($serializedRequest) {
	}

	protected function shouldCompressResponse(&$responsePayload) {
		return RPCServletUtils::exceedsUncompressedContentLengthLimit($responsePayload);
	}
	
	protected function getPermutationStrongName() {
		return $_SERVER[self::STRONG_NAME_HEADER];
	}
	
	private function getCachedSerializationPolicy($moduleBaseURL, $strongName) {
		if (isset($this->serializationPolicyCache[$moduleBaseURL . $strongName])) {
			return $this->serializationPolicyCache[$moduleBaseURL . $strongName];
		}
		else {
			return null;
		}
	}

	private function putCachedSerializationPolicy($moduleBaseURL, $strongName, 
			SerializationPolicy $serializationPolicy) {
		$this->serializationPolicyCache[$moduleBaseURL . $strongName] = $serializationPolicy;
	}

	private function writeResponse(&$responsePayload) {
		$gzipEncode = RPCServletUtils::acceptsGzipEncoding() && $this->shouldCompressResponse($responsePayload);
		
		RPCServletUtils::writeResponse($responsePayload, $gzipEncode);
	}

}

interface RemoteService {
}