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

require_once PHPRPC_ROOT . 'classes.php';
require_once PHPRPC_ROOT . 'rpcphptools.php';
require_once PHPRPC_ROOT . 'serialization.php';
require_once PHPRPC_ROOT . 'serializers.php';

require_once PHPRPC_ROOT . 'ClientOracle.php';
require_once PHPRPC_ROOT . 'SimplePayloadDecoder.php';
require_once PHPRPC_ROOT . 'SerializationStreamReader.php';

class RPC {
	
	private static $TYPE_NAMES = array();
	
	public static function init() {
		self::$TYPE_NAMES[' Z'] = Boolean::typeClass();
		self::$TYPE_NAMES[' B'] = Byte::typeClass();
		self::$TYPE_NAMES[' C'] = Character::typeClass();
		self::$TYPE_NAMES[' D'] = Double::typeClass();
		self::$TYPE_NAMES[' F'] = Float::typeClass();
		self::$TYPE_NAMES[' I'] = Integer::typeClass();
		self::$TYPE_NAMES[' J'] = Long::typeClass();
		self::$TYPE_NAMES[' S'] = Short::typeClass();
	}
	
	public static function getClassForSerializedName($serializedName, ClientOracle $clientOracle = null) {
		if (isset(self::$TYPE_NAMES[$serializedName])) {
			return self::$TYPE_NAMES[$serializedName];
		}
		
		if ($serializedName[0] == ' ') {
			$serializedName = substr($serializedName, 1);
		}
		else if ($clientOracle != null) {
			$serializedName = $clientOracle->getTypeName($serializedName);
		}
		assert($serializedName != null);
		
		return Classes::classOf($serializedName);
	}
	
	public static function decodeRequest($encodedRequest, ClientOracle $clientOracle) {
		if ($encodedRequest == null) {
			throw new NullPointerException('encodedRequest cannot be null');
		}
		
		if ($encodedRequest == '') {
			throw new IllegalArgumentException('encodedRequest cannot be empty');
		}
		try {
			$decoder = null;
			try {
				$decoder = new SimplePayloadDecoder($clientOracle, $encodedRequest);
			}
			catch (ClassNotFoundException $e) {
				throw new IncompatibleRemoteServiceException('Server does not have a type sent by the client', $e);
			}
			
			$streamReader = new CommandServerSerializationStreamReader();
			if ($decoder->getThrownValue() != null) {
				$streamReader->prepareToRead(array($decoder->getThrownValue()));
				throw new RemoteException($streamReader->readObject());
			}
			else {
				$streamReader->prepareToRead($decoder->getValues());
			}
			
			$serviceIntfName = $streamReader->readString();
			//$servicePhpName = self::serviceInterfaceToPhpService($serviceIntfName);
			$serviceIntf = self::getClassForSerializedName($serviceIntfName);
			
			if (!Classes::classOf('RemoteService')->isAssignableFrom($serviceIntf)) {
				throw new IncompatibleRemoteServiceException('Blocked attempt to access interface "' . 
					$serviceIntf->getName() . '" which does not extends RemoteService');
			}
			
			$servicePhpName = $serviceIntf->getName();
			$serviceObject = null;
			try {
				$serviceObject = new $servicePhpName();
			}
			catch (Exception $e) {
				throw new IncompatibleRemoteServiceException('Could not locate requested service "' . $serviceIntfName . '"', $e);
			}
			
			$serviceMethodName = $streamReader->readString();
			$paramCount = $streamReader->readInt();
			
			$parameterTypes = array();
			for ($i = 0; $i < $paramCount; $i++) {
				$paramClassName = $streamReader->readString();
				
				try {
					$parameterTypes[] = self::getClassForSerializedName($paramClassName, $clientOracle);
				}
				catch (ClassNotFoundException $e) {
					throw new IncompatibleRemoteServiceException('Parameter ' . $i . ' of is of an unknown type ' . $paramClassName, $e);
				}	
			}
			
			$method = $serviceIntf->getMethod($serviceMethodName);
			if ($method == null) {
				throw new IncompatibleRemoteServiceException('Cound not find the method ' . $serviceMethodName . ' in service ' . $serviceIntfName);
			}
			
			$parameterValues = array();
			for ($i=0; $i<$paramCount; $i++) {
				$parameterValues[] = Accessors::get($parameterTypes[$i])->readNext($streamReader);
			}
			
			return new RpcRequest($serviceObject, $method, $parameterValues);
			
		}
		catch (SerializationException $e) {
			throw new IncompatibleRemoteServiceException($e->getMessage(), $e);
		}
	}
	
	private static function serviceInterfaceToPhpService($serviceIntfName) {
		try {
			$clazz = Classes::classOf($serviceIntfName);
			return $clazz->getName();
		}
		catch (ClassNotFoundException $e) {
			throw new IncompatibleRemoteServiceException('Service [' . $serviceIntfName . '] has not been found', $e);
		}
	}
	
	public static function invokeAndStreamResponse(RpcRequest $request, 
			ClientOracle $clientOracle, OutputStream $stream) {
		if ($request->method == null) {
			throw new NullPointerException('serviceMethod');
		}
		
		if ($clientOracle == null) {
			throw new NullPointerException('clientOracle');
		}
		
		$sink = $clientOracle->createCommandSink($stream);
		
		try {
			$result = $request->method->invokeArgs($request->service, $request->parameters);
			try {
				//self::streamResponse($clientOracle, $result, $sink, false);
				self::streamResponse2($clientOracle, $result, $request->method->getReturnType(), $sink, false);
			}
			catch (SerializationException $e) {
				self::streamResponse($clientOracle, $e, $sink, true);
			}
		}
		catch (Exception $e) {
			self::streamResponse($clientOracle, $e, $sink, true);
		}
		$sink->finish();
	}
	
	public static function streamResponse(ClientOracle $clientOracle, $payload, CommandSink $sink, $asThrow) {
		$command = null;
		if ($asThrow) {
			$command = new ThrowCommand();
			assert($payload instanceof Exception);
			$payload = new Throwable(get_class($payload), (string) $payload);
		}
		else {
			$command = new ReturnCommand();
		}
		
		$out = new CommandServerSerializationStreamWriter(new HasValuesCommandSink($command), $clientOracle);
		$out->writeObject($payload);
		
		$sink->accept($command);
	}
	
	public static function streamResponse2(ClientOracle $clientOracle, $payload, $payloadType, CommandSink $sink, $asThrow) {
		$command = null;
		if ($asThrow) {
			$command = new ThrowCommand();
			assert($payload instanceof Exception);
			$payload = new Throwable(get_class($payload), (string) $payload);
		}
		else {
			$command = new ReturnCommand();
		}
		
		$out = new CommandServerSerializationStreamWriter(new HasValuesCommandSink($command), $clientOracle);
		if (empty($payloadType)) {
			$out->writeObject($payload);
		}
		else {
			$clazz = Classes::classOf($payloadType);
			$out->writeValue($clazz, $payload);
		}
		
		$sink->accept($command);
	}
	
	public static function streamResponseForFailure(ClientOracle $clientOracle, OutputStream $out, Exception $payload) {
		$sink = $clientOracle->createCommandSink($out);
		self::streamResponse($clientOracle, $payload, $sink, true);
		$sink->finish();
	}
	
}
RPC::init();

class RpcRequest {
	public $service;
	public $method;
	public $parameters;
	
	public function __construct($service, $method, $parameters) {
		$this->service = $service;
		$this->method = $method;
		$this->parameters = $parameters;
	}
}

/** @gwtname com.google.gwt.user.client.rpc.IncompatibleRemoteServiceException */
class IncompatibleRemoteServiceException extends Exception {
	
	public function __construct($message, Exception $previous = null) {
		$msg = $message;
		if ($previous  != null) {
			$msg .= ' : ' . $previous->getMessage();
		}
		parent::__construct($msg, 0, $previous);
	}
	
}