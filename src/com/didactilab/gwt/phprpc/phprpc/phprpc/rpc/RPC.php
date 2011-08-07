<?php

require_once PHPRPC_ROOT . 'classes.php';
require_once PHPRPC_ROOT . 'primitives.php';
require_once PHPRPC_ROOT . 'primitives_serializers.php';
require_once PHPRPC_ROOT . 'collections.php';
require_once PHPRPC_ROOT . 'collections_serializers.php';
require_once PHPRPC_ROOT . 'datetime.php';
require_once PHPRPC_ROOT . 'datetime_serializers.php';
require_once PHPRPC_ROOT . 'serialization.php';

require_once PHPRPC_ROOT . 'rpc/SerializationPolicy.php';
require_once PHPRPC_ROOT . 'rpc/ServerSerializationStreamReader.php';
require_once PHPRPC_ROOT . 'rpc/ServerSerializationStreamWriter.php';
require_once PHPRPC_ROOT . 'rpc/RpcToken.php';
require_once PHPRPC_ROOT . 'rpc/RPCServletUtils.php';
require_once PHPRPC_ROOT . 'rpc/javaclasses.php';

class RPC {
	
	private static $PRIMITIVE_WRAPPER_CLASS_TO_PRIMITIVE_CLASS;
	
	private static $serviceToImplementedInterfacesMap;
	
	private static $TYPE_NAMES = array();
	
	public static function init() {
		self::$PRIMITIVE_WRAPPER_CLASS_TO_PRIMITIVE_CLASS = new HashMap();
		self::$PRIMITIVE_WRAPPER_CLASS_TO_PRIMITIVE_CLASS->put(Boolean::clazz(), Boolean::typeClass());
		self::$PRIMITIVE_WRAPPER_CLASS_TO_PRIMITIVE_CLASS->put(Byte::clazz(), Byte::typeClass());
		self::$PRIMITIVE_WRAPPER_CLASS_TO_PRIMITIVE_CLASS->put(Character::clazz(), Character::typeClass());
		self::$PRIMITIVE_WRAPPER_CLASS_TO_PRIMITIVE_CLASS->put(Float::clazz(), Float::typeClass());
		self::$PRIMITIVE_WRAPPER_CLASS_TO_PRIMITIVE_CLASS->put(Integer::clazz(), Integer::typeClass());
		self::$PRIMITIVE_WRAPPER_CLASS_TO_PRIMITIVE_CLASS->put(Long::clazz(), Long::typeClass());
		self::$PRIMITIVE_WRAPPER_CLASS_TO_PRIMITIVE_CLASS->put(Short::clazz(), Short::typeClass());
		
		self::$TYPE_NAMES['Z'] = Boolean::typeClass();
		self::$TYPE_NAMES['B'] = Byte::typeClass();
		self::$TYPE_NAMES['C'] = Character::typeClass();
		self::$TYPE_NAMES['D'] = Double::typeClass();
		self::$TYPE_NAMES['F'] = Float::typeClass();
		self::$TYPE_NAMES['I'] = Integer::typeClass();
		self::$TYPE_NAMES['J'] = Long::typeClass();
		self::$TYPE_NAMES['S'] = Short::typeClass();
		
		self::$serviceToImplementedInterfacesMap = new HashMap();
	}
	
	public static function decodeRequest($encodedRequest, Clazz $type = null, 
			SerializationPolicyProvider $serializationPolicyPolicyProvider = null) {
		if (is_null($encodedRequest)) {
			throw new NullPointerException('encodedRequest cannot be null');
		}
		
		if (empty($encodedRequest)) {
			throw new IllegalArgumentException('encodedRequest cannot be empty');
		}
		
		try {
			$streamReader = new ServerSerializationStreamReader($serializationPolicyPolicyProvider);
			$streamReader->prepareToRead($encodedRequest);

			$rpcToken = null;
			if ($streamReader->hasFlags(AbstractSerializationStream::FLAG_RPC_TOKEN_INCLUDED)) {
				// Read the RPC token
				$rpcToken = $streamReader->deserializeValue(Classes::classOf(RpcToken));
			}
			
			// Read the name of the RemoteService interface
			$serviceIntfName = self::maybeDeobfuscate($streamReader, $streamReader->readString());
			
			//TODO: implements model or is class
			/*if (!is_null($type)) {
				if (!self::implementsInterface($type, $serviceIntfName)) {
					$printedType = self::printTypeName($type);
					throw new IncompatibleRemoteServiceException(
						"Blocked attempt to access interface '$serviceIntfName', " .
						"which is not implemented by '$printedType'; " .
						"this is either misconfiguration or a hack attempt"
					);
				}
			}*/
			
			$serializationPolicy = $streamReader->getSerializationPolicy();
			try {
				$serviceIntf = self::getClassFromSerializedName($serviceIntfName);
				if (!Classes::classOf(RemoteService)->isAssignableFrom($serviceIntf)) {
					// The requested interface is not a RemoteService interface
					$printedType = $this->printTypeName($serviceIntf);
					throw new IncompatibleRemoteServiceException(
						"Blocked attempt to access interface '$printedType', " .
						"which doesn't extend RemoteService; this is either misconfiguration or a hack attempt"
					);
				}
			}
			catch (ClassNotFoundException $e) {
				throw new IncompatibleRemoteServiceException(
					"Could not locate requested interface '$serviceIntfName' : $e"
				);
			}
			
			$serviceMethodName = $streamReader->readString();
			$paramCount =$streamReader->readInt();
			if ($paramCount > $streamReader->getNumberOfTokens()) {
				throw new IncompatibleRemoteServiceException('Invalid number of parameters');
			}
			$parameterTypes = array();
			for ($i=0; $i<$paramCount; $i++) {
				$paramClassName = self::maybeDeobfuscate($streamReader, $streamReader->readString());
				
				try {
					$parameterTypes[] = self::getClassFromSerializedName($paramClassName);
				}
				catch (ClassNotFoundException $e) {
					throw new IncompatibleRemoteServiceException("Paramter $i is unknown type '$paramClassName' : $e");
				}
			}
			
			try {
				$method = $serviceIntf->getMethod($serviceMethodName);
				
				$parameterValues = array();
				for ($i=0; $i<$paramCount; $i++) {
					$parameterValues[] = $streamReader->deserializeValue($parameterTypes[$i]);
				}
				
				return new RPCRequest($method, $parameterValues, $serializationPolicy, 
					$streamReader->getFlags(), $rpcToken);
			}
			catch (NoSuchMethodException $e) {
				throw new IncompatibleRemoteServiceException(
					self::formatMethodNotFoundErrorMessage($serviceIntf, $serviceMethodName, $parameterTypes)
				);
			}
			
		}
		catch (SerizalizationException $ex) {
			throw new IncompatibleRemoteServiceException($ex->getMessage() . ' : ' . $ex);
		}
	}
	
	public static function encodeResponseForFailure(Method $serviceMethod, Throwable $cause, 
			SerializationPolicy $serializationPolicy = null,
			$flags = AbstractSerializationStream::DEFAULT_FLAGS) {
		if (is_null($cause)) {
			throw new NullPointerException('cause cannot be null');
		}
		
		if (is_null($serializationPolicy)) {
			//throw new NullPointerException('serializationPolicy cannot be null');
			$serializationPolicy = self::getDefaultSerializationPolicy();
		}
		
		if (!is_null($serviceMethod) && !RPCServletUtils::isExpectedException($serviceMethod, $cause)) {
			$cause = (string) $cause;
			$source = self::getSourceRepresentation($serviceMethod);
			throw new UnexpectedException("Service method '$source' threw an unexpected excetion: $cause");
		}
		
		return self::encodeResponse($cause->getClass(), $cause, true, $flags, $serializationPolicy);
	}
	
	public static function encodeResponseForSuccess(Method $serviceMethod, $object, 
			SerializationPolicy $serializationPolicy = null,
			$flags = AbstractSerializationStream::DEFAULT_FLAGS) {
		if (is_null($serviceMethod)) {
			throw new NullPointerException('serviceMethod cannot be null');
		}
		
		if (is_null($serializationPolicy)) {
			$serializationPolicy = self::getDefaultSerializationPolicy();
			throw new NullPointerException('serializationPolicy cannot be null');
		}
		
		$methodReturnTypeName = $serviceMethod->getReturnType();
		//TODO see if must be an object
		if (empty($methodReturnTypeName)) {
			$methodReturnType =  Void::typeClass();
		}
		else {
			$methodReturnType = Classes::classOf($methodReturnTypeName);
		}
		
		if ($methodReturnType !== Void::typeClass() && !is_null($object)) {
			// TODO : Verify
			/*if ($methodReturnType->isPrimitive()) {
				$actualReturnType = self::getPrimitiveClassFromWrapper(Classes::classOf($object));
			}
			else {
				$actualReturnType = Classes::classOf($object);
			}*/
			$actualReturnType = Classes::classOfValue($object);
			
			//var_dump($methodReturnType);
			
			// TODO : Enum manage
			if ($methodReturnType->isEnum() && $actualReturnType === Integer::typeClass()) {
				return self::encodeResponse($methodReturnType, $object, false, $flags, $serializationPolicy);
			}
			
			
			if (is_null($actualReturnType) || !$methodReturnType->isAssignableFrom($actualReturnType)) {
				$printedType = self::printTypeName(Classes::classOfValue($object));
				$source = self::getSourceRepresentation($serviceMethod);
				throw new IllegalArgumentException(
					"Type '$printedType' does not match the return type in the method's signature : '$source'"
				);
			}
		}
		
		return self::encodeResponse($methodReturnType, $object, false, $flags, $serializationPolicy);
	}
	
	public static function getDefaultSerializationPolicy() {
		return LegacySerializationPolicy::getInstance();
	}
	
	public static function invokeAndEncodeResponse($target, Method $serviceMethod, $args,
			SerializationPolicy $serializationPolicy = null,
			$flags = AbstractSerializationStream::DEFAULT_FLAGS) {
		if (is_null($serviceMethod)) {
			throw new NullPointerException('serviceMethod cannot be null');
		}
		
		if (is_null($serializationPolicy)) {
			$serializationPolicy = self::getDefaultSerializationPolicy();
			throw new NullPointerException('serializationPolicy cannot be null');
		}
		
		try {
			$result = $serviceMethod->invokeArgs($target, $args);
			$responsePayload = self::encodeResponseForSuccess($serviceMethod, $result, $serializationPolicy, $flags);
		}
		//cannot be happen in Php
		catch (IllegalAccessException $e) {
			$securityException = new SecurityException(self::formatIllegalAccessErrorMessage($target, $serviceMethod), $e);
			throw $securityException;
		}
		//cannot be happen in Php
		catch (IllegalArgumentException $e) {
			$securityException = new SecurityException(self::formatIllegalArgumentErrorMessage($target, $serviceMethod, $args), $e);
			throw $securityException;
		}
		catch (Exception $e) {
			$cause = new Throwable($e, $e->getMessage());
			$responsePayload = self::encodeResponseForFailure($serviceMethod, $cause, $serializationPolicy, $flags);
		}
		
		return $responsePayload;
	}
	
	private static function encodeResponse(Clazz $responseClass, $object, $wasThrown, $flags, $serializationPolicy) {
		$stream = new ServerSerializationStreamWriter($serializationPolicy);
		$stream->setFlags($flags);
		
		$stream->prepareToWrite();
		if ($responseClass !== Void::typeClass()) {
			$stream->serializeValue($object, $responseClass);
		}
		
		$bufferStr = ($wasThrown ? '//EX' : '//OK') . (string) $stream;
		return $bufferStr;
	}

	private static function formatIllegalAccessErrorMessage($target, Method $serviceMethod) {
		$source = self::getSourceRepresentation($serviceMethod);
		$sb = "Blocked attempt to access inaccessible method '$source'";
		
		if (!is_null($target)) {
			$printedType = self::printTypeName(Classes::classOf($target));
			$sb .= " on target '$printedType'";
		}
		
		$sb .= '; this is either misconfiguration or a hack attempt';
		
		return $sb;
	}
	
	private static function formatIllegalArgumentErrorMessage($target, Method $serviceMethod, $args) {
		$source = self::getSourceRepresentation($serviceMethod);
		$sb = "Blocked attempt to invoke method '$source'";
		
		if (!is_null($target)) {
			$printedType = self::printTypeName(Classes::classOf($target));
			$sb .= " on target '$printedType'";
		}
		
		$sb .= ' with invalid arguments';
		
		if (!is_null($args) && !empty($args)) {
			$sb .= Arrays::asList($args);
		}

		return $sb;
	}
	
	private static function formatMethodNotFoundErrorMessage(Clazz $serviceIntf, $serviceMethodName, $parameterTypes) {
		$sb = "Could not locate requested method '$serviceMethodName(";
		for ($i=0; $i<count($parameterTypes); $i++) {
			if ($i > 0) {
				$sb .= ', ';
			}
			$sb .= self::printTypeName($parameterTypes[$i]);
		}
		$sb .= ")'";
		
		$printedType = self::printTypeName($serviceIntf);
		$sb .= " in interface '$printedType'";
		
		return $sb;
	}
	
	private static function getClassFromSerializedName($serializedName) {
		if (isset(self::$TYPE_NAMES[$serializedName])) {
			return self::$TYPE_NAMES[$serializedName];
		}
		
		return Classes::classOf($serializedName);
	}
	
	private static function getPrimitiveClassFromWrapper(Clazz $wrapperClass) {
		return self::$PRIMITIVE_WRAPPER_CLASS_TO_PRIMITIVE_CLASS->get($wrapperClass);
	}
	
	private static function getSourceRepresentation(Method $method) {
		return str_replace('$', '.', (string) $method);
	}
	
	private static function implementsInterface(Clazz $service, $intfName) {
		$interfaceSet = self::$serviceToImplementedInterfacesMap->get($service);
		if (!is_null($interfaceSet)) {
			if ($interfaceSet->contains($intfName)) {
				return true;
			}
		}
		else {
			$interfaceSet = new HashSet();
			self::$serviceToImplementedInterfacesMap->put($service, $interfaceSet);
		}
		
		if (!$service->isInterface()) {
			while ((!is_null($service)) && Classes::classOf('RemoteServiceServlet') !== $service) {
				$intfs = $service->getInterfaces();
				foreach ($intfs as $intf) {
					if (self::implementsInterfaceRecursive($intf, $intfName)) {
						$interfaceSet->add($intfName);
						return true;
					}
				}
			
				// did not find the interface in this class so we look in the
				// superclass
				//
				$service = $service->getSuperClass();
			}
		}
		else {
			if (self::implementsInterfaceRecursive($service, $intfName)) {
				$interfaceSet->add($intfName);
				return true;
			}
		}
		
		return false;
	}
	
	private static function implementsInterfaceRecursive(Clazz $clazz, $intfName) {
		assert($clazz->isInterface());
		
		if ($clazz->getName() === $intfName) {
			return true;
		}
		
		$intfs = $clazz->getInterfaces();
		foreach ($intfs as $intf) {
			if (self::implementsInterfaceRecursive($intf, $intfName)) {
				return true;
			}
		}
		
		return false;
	}
	
	private static function maybeDeobfuscate(ServerSerializationStreamReader $streamReader, $name) {
		if ($streamReader->hasFlags(AbstractSerializationStream::FLAG_ELIDE_TYPE_NAMES)) {
			$serializationPolicy = $streamReader->getSerializationPolicy();
			if (!(serializationPolicy instanceof TypeNameObfuscator)) {
				throw new IncompatibleRemoteServiceException(
					'RPC request was encoded with obfuscated type names, ' .
					'but the SerializationPolicy in use does not implement ' .
					Classes::classOf(TypeNameObfuscator)->getName()
				);
			}
			
			$maybe = $serializationPolicy->getClassNameForTypeId($name);
			if (!is_null($maybe)) {
				return $maybe;
			}
		}
		else if (($index = mb_strpos($name, '/')) !== false) {
			return mb_substr($name, 0, $index);
		}
		return $name;
	}
	
	private static function printTypeName(Clazz $type) {
		// Primitives
		//
		if ($type === Integer::typeClass()) {
			return 'int';
		}
		else if ($type === Long::typeClass()) {
			return 'long';
		}
		else if ($type === Short::typeClass()) {
			return 'short';
		}
		else if ($type === Byte::typeClass()) {
			return 'byte';
		}
		else if ($type === Character::typeClass()) {
			return 'char';
		}
		else if ($type === Boolean::typeClass()) {
			return 'boolean';
		}
		else if ($type === Float::typeClass()) {
			return 'float';
		}
		else if ($type === Double::typeClass()) {
			return 'double';
		}
		
		// Arrays
		//
		if ($type->isArray()) {
			$componentType = $type->getComponentType();
			return self::printTypeName($componentType) . '[]';
		}
		
		// Everything else
		//
		return str_replace('$', '.', $type->getName());
	}
	
}
RPC::init();

class RPCRequest {
	private $flags;
	private $method;
	private $parameters;
	private $rpcToken;
	private $serializationPolicy;
	
	public function __construct(Method $method, $parameters, SerializationPolicy $serializationPolicy, $flags, RpcToken $rpcToken = null) {
		$this->method = $method;
		$this->parameters = $parameters;
		$this->rpcToken = $rpcToken;
		$this->serializationPolicy = $serializationPolicy;
		$this->flags = $flags;
	}
	
	public function getMethod() {
		return $this->method;
	}
	
	public function getParameters() {
		return $this->parameters;
	}
	
	public function getRpcToken() {
		return $this->rpcToken;
	}
	
	public function getSerializationPolicy() {
		return $this->serializationPolicy;
	}
	
	public function getFlags() {
		return $this->flags;
	}
	
	public function __toString() {
		$sb = $this->method->getDeclaringClass()->getName();
		$sb .= '.';
		$sb .= $this->method->getName();
		
		$sb .= '(';
		for ($i=0, $c=count($this->parameters); $i<$c; $i++) {
			$param = $this->parameters[$i];
			if ($i < $c - 1) {
				$sb .= ', ';
			}
			if (is_string($param)) {
				$escaped = str_replace('\\\"', '\\\\\"', $param);
				$sb .= "\"$escaped\"";
			}
			else if (is_null($param)) {
				$sb .= 'null';
			}
			else {
				$sb .= (string) $param;
			}
		}
		$sb .= ')';
		
		return $sb;
	}
	
}

/** @gwtname com.google.gwt.user.client.rpc.IncompatibleRemoteServiceException */
class IncompatibleRemoteServiceException extends Exception {
}

/** @gwtname com.google.gwt.user.client.rpc.RpcTokenException */
class RpcTokenException extends Exception {
}