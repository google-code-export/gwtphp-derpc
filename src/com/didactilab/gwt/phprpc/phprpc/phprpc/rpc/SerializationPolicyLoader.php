<?php

require_once PHPRPC_ROOT . 'classes.php';
require_once PHPRPC_ROOT . 'stream.php';
require_once PHPRPC_ROOT . 'exception.php';

require_once PHPRPC_ROOT . 'rpc/SerializationPolicy.php';


class SerializationPolicyLoader {
	
	const CLIENT_FIELDS_KEYWORD = '@ClientFields';
	
	const SERIALIZATION_POLICY_FILE_ENCODING = 'UTF-8';
	
	const FORMAT_ERROR_MESSAGE = 'Expected: className, [true | false], [true | false], [true | false], [true | false], typeId, signature';
	
	public static function getSerializationPolicyFileName($serializationPolicyStrongName) {
		return $serializationPolicyStrongName . '.gwt.rpc';
	}
	
	/**
	 * @deprecated
	 */
	public static function loadFromFile($filename) {
		$classNotFoundExceptions = new ArrayList();
		$serializationPolicy = self::loadFromStream($filename, $classNotFoundExceptions);
		if (!$classNotFoundExceptions->isEmpty()) {
			throw $classNotFoundExceptions[0];
		}
		return $serializationPolicy;
	}
	
	public static function loadFromStream($filename, GenericList $classNotFoundExceptions) {
		if (is_null($filename)) {
			throw new NullPointerException('InputStream');
		}
		
		$whitelistSer = new HashMap();
		$whitelistDeser = new HashMap();
		$typeIds = new HashMap();
		$clientFields = new HashMap();
		
		$br = new BufferedReader($filename);
		$line = $br->readLine();
		$lineNum = 1;
		while (!is_null($line)) {
			$line = trim($line);
			if (mb_strlen($line) > 0) {
				$components = explode(',', $line);
				if ($components[0] === self::CLIENT_FIELDS_KEYWORD) {
					/*
					 * Lines starting with '@ClientFields' list potentially serializable fields known to
					 * client code for classes that may be enhanced with additional fields on the server.
					 * If additional server  fields are found, they will be serizalized separately from the
					 * normal RPC process and transmitted to the client as an opaque blob of data stored
					 * in a WeakMapping associated with the object instance.
					 */
					$binaryTypeName = trim($components[1]);
					try {
						$clazz = Classes::classOf($binaryTypeName);
						$fieldNames = new HashSet();
						for ($i=2; $i<count($components); $i++) {
							$fieldNames->add($components[$i]);	
						}
						$clientFields->put($clazz, $fieldNames);
					}
					catch (ClassNotFoundException $e) {
						// Ignore the error, but add it to the list of errors if one was
            			// provided.
            			if (!is_null($classNotFoundExceptions)) {
            				$classNotFoundExceptions->add($e);
            			}
					}
				}
				else {
					if (count($components) != 2 && count($components) != 7) {
						throw new ParseException(self::FORMAT_ERROR_MESSAGE, $lineNum);
					}
					
					for ($i=0; $i<count($components); $i++) {
						$components[$i] = trim($components[$i]);
						if (mb_strlen($components[$i]) == 0) {
							throw new ParseException(self::FORMAT_ERROR_MESSAGE, $lineNum);
						}
					}
					
					$binaryTypeName = trim($components[0]);
					if (count($components) == 2) {
						$fieldSer = $fieldDeser = true;
						$instantSer = $instantDeser = Boolean::valueOf($components[1]);
						$typeId = $binaryTypeName;
					}
					else {
						$idx = 1;
						// TODO: Validate the instantiable string better
						$fieldSer = Boolean::valueOf($components[$idx++]);
						$instantSer = Boolean::valueOf($components[$idx++]);
						$fieldDeser = Boolean::valueOf($components[$idx++]);
						$instantDeser = Boolean::valueOf($components[$idx++]);
						$typeId = $components[$idx++];
						
						if (!$fieldSer && !$fieldDeser && TypeNameObfuscator::SERVICE_INTERFACE_ID != $typeId) {
							throw new ParseException('Type ' . $binaryTypeName 
								. ' is neither field serializable, field deserializable '
								. 'nor the service interface : ', $lineNum);
						}
					}
					
					try {
						$clazz = Classes::classOf($binaryTypeName);
						if ($fieldSer) {
							$whitelistSer->put($clazz, $instantSer);
						}
						if ($fieldDeser) {
							$whitelistDeser->put($clazz, $instantDeser);
						}
						$typeIds->put($clazz, $typeId);
					}
					catch (ClassNotFoundException $e) {
						// Ignore the error, but add it to the list of errors if one was
						// provided.
						if (!is_null($classNotFoundExceptions)) {
							$classNotFoundExceptions->add($e);
						}
					}
				}
			}
			$line = $br->readLine();
			$lineNum++;
		}
		
		return new StandardSerializationPolicy($whitelistSer, $whitelistDeser, $typeIds, $clientFields);
	}
	
}

class ParseException extends Exception {
	
	protected $line;
	public function __construct($message, $line) {
		parent::__construct($message);
		$this->line = $line;
	}
}


