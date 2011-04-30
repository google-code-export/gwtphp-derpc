<?php
/*
 * Copyright 2011 DidactiLab SAS
 * 
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 * 
 * Date: 30 avr. 2011
 * Author: Mathieu LIGOCKI
 */

require_once PHPRPC_ROOT . 'config.php';

require_once PHPRPC_ROOT . 'GWT.php';
require_once PHPRPC_ROOT . 'cache.php';

require_once PHPRPC_ROOT . 'stream.php';
require_once PHPRPC_ROOT . 'serialization.php';

require_once PHPRPC_ROOT . 'ClientOracle.php';
require_once PHPRPC_ROOT . 'InetAddress.php';
require_once PHPRPC_ROOT . 'RPC.php';

require_once PHPRPC_ROOT . 'WebModeClientOracle.php';

class RpcServlet {

	const CLIENT_ORACLE_EXTENSION = '.gwtphp.rpc';
	const DUMP_PAYLOAD = false;
	const MODULE_BASE_HEADER = 'HTTP_X_GWT_MODULE_BASE';
	const STRONG_NAME_HEADER = 'HTTP_X_GWT_PERMUTATION';
	
	const SESSION_ENABLED = false;
	const CLIENT_ORACLE_CACHE = 'clientOracleCache';

	private $clientOracleCache = array();

	public function __construct() {
		if (Cache::enabled()) {
			//$m = microtime(true);
			//session_start();
			if (Cache::exists(self::CLIENT_ORACLE_CACHE)) {
				$this->clientOracleCache = Cache::get(self::CLIENT_ORACLE_CACHE);
			}
			//$t = (microtime(true) - $m) * 1000;
			//echo '[session ' . $t .']';
		}
	}

	public function getClientOracle() {
		$permutationStrongName = $this->getPermutationStrongName();
		if ($permutationStrongName == null) {
			throw new SecurityException('Blocked request without GWT permutation header (XSRF attack ?)');
		}

		$basePath = $this->getRequestModuleBasePath();
		if ($basePath == null) {
			throw new SecurityException('Blocked request without GWT base path header (XSRF attack ?)');
		}

		$toReturn = null;
		if (isset($this->clientOracleCache[$permutationStrongName])) {
			$toReturn = $this->clientOracleCache[$permutationStrongName];
			if ($toReturn != null) {
				return $toReturn;
			}
		}

		if ($permutationStrongName == 'HostedMode') {
			if (!$this->allowHostedModeConnections()) {
				throw new SecurityException('Blocked Development Mode request');
			}
			$toReturn = new HostedModeClientOracle();
		}
		else {
			$in = $this->findClientOracleData($basePath, $permutationStrongName);
				
			$toReturn = WebModeClientOracle::load($in);
		}
		$this->clientOracleCache[$permutationStrongName] = $toReturn;
		return $toReturn;
	}
	
	protected function getPermutationStrongName() {
		return $_SERVER[self::STRONG_NAME_HEADER];
	}

	protected function allowHostedModeConnections() {
		return $this->isRequestFromLocalAddress();
	}

	protected function findClientOracleData($requestModuleBasePath, $permutationStrongName) {
		//$resourcePath = $requestModuleBasePath . $permutationStrongName . self::CLIENT_ORACLE_EXTENSION;
		$resourcePath = GWT::getModuleBasePath() . $permutationStrongName . self::CLIENT_ORACLE_EXTENSION;
		return file_get_contents($resourcePath);
	}

	protected function getRequestModuleBasePath() {
		if (!isset($_SERVER[self::MODULE_BASE_HEADER])) {
			return null;
		}
		$header = $_SERVER[self::MODULE_BASE_HEADER];
		//echo 'header = ' . $header . '<br />';
		$path = parse_url($header, PHP_URL_PATH);
		//echo 'path = ' . $path . '<br />';
		$contextPath = parse_url($_SERVER['SCRIPT_NAME'], PHP_URL_PATH);
		//echo 'script = ' . $contextPath . '<br />pos = ' . strpos($path, $contextPath) . '<br />';
		if (strpos($path, $contextPath) != 0) {
			return null;
		}
		//echo 'substr = ' . substr($path, strlen($contextPath)) . '<br />';
		//TODO
		//return substr($path, strlen($contextPath));
		return $path;
	}

	protected function isRequestFromLocalAddress() {
		$addr = InetAddress::getRemoteAddress();
		
		return InetAddress::isClientLocalHost() 
			|| $addr->isLoopbackAddress() 
			|| $addr->isSiteLocalAddress()
			|| $addr->isLinkLocalAddress();
	}
	
	public function processPost() {
		$clientOracle = $this->getClientOracle();
		
		$requestPayload = file_get_contents('php://input');
		if (self::DUMP_PAYLOAD) {
			echo $requestPayload;
		}
		
		header('Content-Type: application/json; charset=utf-8');
		
		$this->processCall($clientOracle, $requestPayload, new EchoOutputStream());
	}
	
	public function processCall(ClientOracle $clientOracle, $payload, OutputStream $stream) {
		assert($payload != null);
		assert(mb_strlen($payload) != 0);
		
		try {
			$rpcRequest = RPC::decodeRequest($payload, $clientOracle);
			$this->onAfterRequestDeserialized($rpcRequest);
			RPC::invokeAndStreamResponse($rpcRequest, $clientOracle, $stream);
		}
		catch (RemoteException $e) {
			throw new SerializationException('An exception was sent from the client : ' . $e);
		}
		catch (IncompatibleRemoteServiceException $e) {
			error_log('A incompatibleRemoteServiceException was thrown while processing this call : ' . $e);
			RPC::streamResponseForFailure($clientOracle, $stream, $e);
		}
	}
	
	public function onAfterRequestDeserialized(RpcRequest $rpcRequest) {
		// NOTHING
	}
	
	public function saveCache() {
		if (Cache::enabled()) {
			if (!Cache::exists(self::CLIENT_ORACLE_CACHE)) {
				Cache::set(self::CLIENT_ORACLE_CACHE, $this->clientOracleCache);
			}
		}
	}
	
	public static function run() {
		$servlet = new self();
		$servlet->processPost();
		$servlet->saveCache();
	}
}

class DebugRpcServlet extends RpcServlet {
	
	public function getClientOracle() {
		return new HostedModeClientOracle();
	}
	
	public function processPost($post) {
		$clientOracle = $this->getClientOracle();
		
		$requestPayload = $post;
		if (self::DUMP_PAYLOAD) {
			echo $requestPayload;
		}
		
		//header('Content-Type: application/json; charset=utf-8');
		
		$this->processCall($clientOracle, $requestPayload, new EchoOutputStream());
	}
	
}

interface RemoteService {
}