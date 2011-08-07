<?php

require_once PHPRPC_ROOT . 'collections.php';
require_once PHPRPC_ROOT . 'classes.php';

require_once PHPRPC_ROOT . 'rpc/ServerSerializationStream.php';

class SSSR_BoundedList extends LinkedList {
	private $componentType;
	private $expectedSize;
	
	public function __construct(Clazz $componentType, $expectedSize) {
		parent::__construct();
		$this->componentType = $componentType;
		$this->expectedSize = $expectedSize;
	}
	
	public function add($e) {
		assert($this->size() < $this->getExpectedSize());
		return parent::add($e);
	}
	
	public function getComponentType() {
		return $this->componentType;
	}
	
	public function getExpectedSize() {
		return $this->expectedSize;
	}
}

abstract class SSSR_ValueReader {
	public abstract function readValue(ServerSerializationStreamReader $stream);
}

class SSSR_ValueReader_Boolean extends SSSR_ValueReader {
	public function readValue(ServerSerializationStreamReader $stream) {
		return $stream->readBoolean();
	}
}

class SSSR_ValueReader_Byte extends SSSR_ValueReader {
	public function readValue(ServerSerializationStreamReader $stream) {
		return $stream->readByte();
	}
}

class SSSR_ValueReader_Char extends SSSR_ValueReader {
	public function readValue(ServerSerializationStreamReader $stream) {
		return $stream->readChar();
	}
}

class SSSR_ValueReader_Double extends SSSR_ValueReader {
	public function readValue(ServerSerializationStreamReader $stream) {
		return $stream->readDouble();
	}
}

class SSSR_ValueReader_Float extends SSSR_ValueReader {
	public function readValue(ServerSerializationStreamReader $stream) {
		return $stream->readFloat();
	}
}

class SSSR_ValueReader_Int extends SSSR_ValueReader {
	public function readValue(ServerSerializationStreamReader $stream) {
		return $stream->readInt();
	}
}

class SSSR_ValueReader_Long extends SSSR_ValueReader {
	public function readValue(ServerSerializationStreamReader $stream) {
		return $stream->readLong();
	}
}

class SSSR_ValueReader_Object extends SSSR_ValueReader {
	public function readValue(ServerSerializationStreamReader $stream) {
		return $stream->readObject();
	}
}

class SSSR_ValueReader_Short extends SSSR_ValueReader {
	public function readValue(ServerSerializationStreamReader $stream) {
		return $stream->readShort();
	}
}

class SSSR_ValueReader_String extends SSSR_ValueReader {
	public function readValue(ServerSerializationStreamReader $stream) {
		return $stream->readString();
	}
}

abstract class SSSR_VectorReader {
	public abstract function readSingleValue(ServerSerializationStreamReader $stream);
	
	public function setSingleValue(&$array, $index, $value) {
		$array[$index] = $value;
	}
	
	public function toArray(Clazz $componentType, SSSR_BoundedList $buffer) {
		if ($buffer->getExpectedSize() != $buffer->size()) {
			throw new SerializationException(
				'Inconsistent number of elements received. Received ' .
				$buffer->size() . ' but expecting ' . $buffer->getExpectedSize()
			);
		}
		
		$arr = ArrayType::newInstance(null, $buffer->size());
		for ($i=0, $n=$buffer->size(); $i < $n; $i++) {
			$this->setSingleValue($arr, $i, $buffer->removeFirst());
		}
		
		return $arr;
	}
	
	public function read(ServerSerializationStreamReader $stream, SSSR_BoundedList $instance) {
		for ($i=0, $n=$instance->getExpectedSize(); $i<$n; ++$i) {
			$instance->add($this->readSingleValue($stream));
		}
		return $this->toArray($instance->getComponentType(), $instance);
	}
}

class SSSR_VectorReader_Boolean extends SSSR_VectorReader {
	public function readSingleValue(ServerSerializationStreamReader $stream) {
		return $stream->readBoolean();
	}
}

class SSSR_VectorReader_Byte extends SSSR_VectorReader {
	public function readSingleValue(ServerSerializationStreamReader $stream) {
		return $stream->readByte();
	}
}

class SSSR_VectorReader_Char extends SSSR_VectorReader {
	public function readSingleValue(ServerSerializationStreamReader $stream) {
		return $stream->readChar();
	}
}

class SSSR_VectorReader_Double extends SSSR_VectorReader {
	public function readSingleValue(ServerSerializationStreamReader $stream) {
		return $stream->readDouble();
	}
}

class SSSR_VectorReader_Float extends SSSR_VectorReader {
	public function readSingleValue(ServerSerializationStreamReader $stream) {
		return $stream->readFloat();
	}
}

class SSSR_VectorReader_Int extends SSSR_VectorReader {
	public function readSingleValue(ServerSerializationStreamReader $stream) {
		return $stream->readInt();
	}
}

class SSSR_VectorReader_Long extends SSSR_VectorReader {
	public function readSingleValue(ServerSerializationStreamReader $stream) {
		return $stream->readLong();
	}
}

class SSSR_VectorReader_Object extends SSSR_VectorReader {
	public function readSingleValue(ServerSerializationStreamReader $stream) {
		return $stream->readObject();
	}
}

class SSSR_VectorReader_Short extends SSSR_VectorReader {
	public function readSingleValue(ServerSerializationStreamReader $stream) {
		return $stream->readShort();
	}
}

class SSSR_VectorReader_String extends SSSR_VectorReader {
	public function readSingleValue(ServerSerializationStreamReader $stream) {
		return $stream->readString();
	}
}

class ServerSerializationStreamReader extends AbstractSerializationStreamReader {
	
	private static $CLASS_TO_VALUE_READER;
	private static $CLASS_TO_VECTOR_READER;
	
	private $serializationPolicy;
	private $serializationPolicyProvider;
	
	private $settersByClass;
	private $stringTable;
	private $tokenList = array();
	private $tokenListIndex;
	
	public static function init() {
		self::$CLASS_TO_VALUE_READER = new IdentityHashMap();
		self::$CLASS_TO_VECTOR_READER = new IdentityHashMap();

		self::$CLASS_TO_VECTOR_READER->put(ArrayType::clazz(Boolean::typeClass()), new SSSR_VectorReader_Boolean());
		self::$CLASS_TO_VECTOR_READER->put(ArrayType::clazz(Byte::typeClass()), new SSSR_VectorReader_Byte());
		self::$CLASS_TO_VECTOR_READER->put(ArrayType::clazz(Character::typeClass()), new SSSR_VectorReader_Char());
		self::$CLASS_TO_VECTOR_READER->put(ArrayType::clazz(Double::typeClass()), new SSSR_VectorReader_Double());
		self::$CLASS_TO_VECTOR_READER->put(ArrayType::clazz(Float::typeClass()), new SSSR_VectorReader_Float());
		self::$CLASS_TO_VECTOR_READER->put(ArrayType::clazz(Integer::typeClass()), new SSSR_VectorReader_Int());
		self::$CLASS_TO_VECTOR_READER->put(ArrayType::clazz(Long::typeClass()), new SSSR_VectorReader_Long());
		self::$CLASS_TO_VECTOR_READER->put(ArrayType::clazz(Object::clazz()), new SSSR_VectorReader_Object());
		self::$CLASS_TO_VECTOR_READER->put(ArrayType::clazz(Short::typeClass()), new SSSR_VectorReader_Short());
		self::$CLASS_TO_VECTOR_READER->put(ArrayType::clazz(String::clazz()), new SSSR_VectorReader_String());
		
		self::$CLASS_TO_VALUE_READER->put(Boolean::typeClass(), new SSSR_ValueReader_Boolean());
		self::$CLASS_TO_VALUE_READER->put(Byte::typeClass(), new SSSR_ValueReader_Byte());
		self::$CLASS_TO_VALUE_READER->put(Character::typeClass(), new SSSR_ValueReader_Char());
		self::$CLASS_TO_VALUE_READER->put(Double::typeClass(), new SSSR_ValueReader_Double());
		self::$CLASS_TO_VALUE_READER->put(Float::typeClass(), new SSSR_ValueReader_Float());
		self::$CLASS_TO_VALUE_READER->put(Integer::typeClass(), new SSSR_ValueReader_Int());
		self::$CLASS_TO_VALUE_READER->put(Long::typeClass(), new SSSR_ValueReader_Long());
		self::$CLASS_TO_VALUE_READER->put(Object::clazz(), new SSSR_ValueReader_Object());
		self::$CLASS_TO_VALUE_READER->put(Short::typeClass(), new SSSR_ValueReader_Short());
		self::$CLASS_TO_VALUE_READER->put(String::clazz(), new SSSR_ValueReader_String());
	}
	
	public function __construct(SerializationPolicyProvider $serializationPolicyProvider) {
		//parent::__construct();
		$this->serializationPolicy = RPC::getDefaultSerializationPolicy();
		$this->settersByClass = new HashMap();
		$this->serializationPolicyProvider = $serializationPolicyProvider;
	}
	
	public function deserializeValue(Clazz $type) {
		$valueReader = self::$CLASS_TO_VALUE_READER->get($type);
		if (!is_null($valueReader)) {
			return $valueReader->readValue($this);
		}
		else {
			return self::$CLASS_TO_VALUE_READER->get(Object::clazz())->readValue($this);
		}
	}
	
	public function getNumberOfTokens() {
		return count($this->tokenList);
	}
	
	public function getSerializationPolicy() {
		return $this->serializationPolicy;
	}
	
	/** @Override */
	public function prepareToRead($encodedTokens) {
		$this->tokenList = array();
		$this->tokenListIndex = 0;
		$this->stringTable = null;
		
		$idx = 0;
		while (false !== ($nextIdx = mb_strpos($encodedTokens, self::RPC_SEPARATOR_CHAR, $idx))) {
			$current = mb_substr($encodedTokens, $idx, $nextIdx - $idx);
			$this->tokenList[] = $current;
			$idx = $nextIdx + 1;
		}
		if ($idx == 0) {
			// Didn't find any separator, assume an older version with different
			// separators and get the version as the sequence of digits at the
			// beginning of the encoded string.
			while ($idx < mb_strlen($encodedTokens)
					&& Character::isDigit(mb_substr($encodedTokens, $idx, 1))) {
				++$idx;
			}
			if ($idx == 0) {
				throw new IncompatibleRemoteException(
					'Malformed or old RPC message received - expecting version between ' 
						. self::SERIALIZATION_STREAM_MIN_VERSION . ' and '
						. self::SERIALIZATION_STREAM_VERSION
				);
			}
			else {
				$version = Integer::valueOf(mb_substr($encodedTokens, 0, $idx));
				throw new IncompatibleRemoteServiceException(
					'Expecting version between '
						. self::SERIALIZATION_STREAM_MIN_VERSION . ' and ' 
						. self::SERIALIZATION_STREAM_VERSION . " from client, got $version"
						. '.'
				);
			}
		}
		
		parent::prepareToRead($encodedTokens);
		
		// Check the RPC version number sent by the client
		if ($this->getVersion() < self::SERIALIZATION_STREAM_MIN_VERSION
				|| $this->getVersion() > self::SERIALIZATION_STREAM_VERSION) {
			throw new IncompatibleRemoteServiceException('Expecting version between ' 
				. self::SERIALIZATION_STREAM_MIN_VERSION . ' and '
				. self::SERIALIZATION_STREAM_VERSION . ' from client, got '
				. $this->getVersion() . '.'
			);
		}
		
		// Check the flags
		if (!$this->areFlagsValid()) {
			throw new IncompatibleRemoteServiceException('Got an unknown flag from ' 
				. 'client: ' . $this->getFlags());
		}
		
		// Read the type name table
		$this->deserializeStringTable();
		
		// Write the serialization policy info
		$moduleBaseURL = $this->readString();
		$strongName = $this->readString();
		if (!is_null($this->serializationPolicyProvider)) {
			$this->serializationPolicy = $this->serializationPolicyProvider->getSerializationPolicy(
				$moduleBaseURL, $strongName);
				
			if (is_null($this->serializationPolicy)) {
				throw new NullPointerException('serializationPolicyProvider.getSerializationPolicy()');
			}
		}
	}
	
	public function readBoolean() {
		return $this->extract() != '0';
	}
	
	public function readByte() {
		$value = $this->extract();
		try {
			return Byte::parseByte($value);
		}
		catch (NumberFormatException $e) {
			throw $this->getNumberFormatException($value, 'byte', Byte::MIN_VALUE,
				Byte::MAX_VALUE);
		}
	}
	
	public function readChar() {
		// just use an int, it's more foolproof
		$value = $this->readInt();
		return Character::chr($value);
	}
	
	public function readDouble() {
		return Double::parseDouble($this->extract());
	}
	
	public function readFloat() {
		//return Float::valueOf(Double::parseDouble($this->extract()));
		return Float::parseFloat($this->extract());
	}
	
	public function readInt() {
		$value = $this->extract();
		try {
			return Integer::parseInt($value);
		}
		catch (NumberFormatException $e) {
			throw $this->getNumberFormatException($value, 'int',
				Integer::MIN_VALUE, Integer::MAX_VALUE);
		}
	}
	
	public function readLong() {
		if ($this->getVersion() == self::SERIALIZATION_STREAM_MIN_VERSION) {
			return Long::valueOf($this->readDouble() + $this->readDouble());
		}
		else {
			return Base64Utils::longFromBase64($this->extract());
		}
	}
	
	public function readShort() {
		$value = $this->extract();
		try {
			return Short::parseShort($value);
		}
		catch (NumberFormatException $e) {
			throw $this->getNumberFormatException($value, 'short',
				Short::MIN_VALUE, Short::MAX_VALUE);
		}
	}
	
	public function readString() {
		return $this->getString($this->readInt());
	}
	
	protected function deserialize($typeSignature) {
		$instance = null;
		try {
			$instanceClass = null;
			if ($this->hasFlags(self::FLAG_ELIDE_TYPE_NAMES)) {
				if ($this->getSerializationPolicy() instanceof TypeNameObfuscator) {
					$obfuscator = $this->getSerializationPolicy();
					$instanceClassName = $obfuscator->getClassNameForTypeId($typeSignature);
					$instanceClass = Classes::classOf($instanceClassName);
				}
				else {
					throw new SerializationException(
						'The GWT module was compiled with RPC type name elision enabled, but '
						. Classes::classOf($this->getSerializationPolicy())->getFullName()
						. ' does not implement ' + Classes::classOf('TypeNameObfuscator')->getFullName()
					);
				}
			}
			else {
				$serializedInstRef = SerializabilityUtilEx::decodeSerializedInstanceReference($typeSignature);
				$instanceClass = Classes::classOf($serializedInstRef->getName());
				$this->validateTypeVersions($instanceClass, $serializedInstRef);
			}
			
			assert(!is_null($this->serializationPolicy));
			
			$this->serializationPolicy->validateDeserialize($instanceClass);
			
			$customSerializer = SerializabilityUtilEx::hasCustomFieldSerializer($instanceClass);
			
			$index = $this->reserveDecodedObjectIndex();
			
			//TODO to test
			$transform = $this->getCustomSerializerTransform($customSerializer);
			if (!is_null($transform)) {
				$instance = $transform->invoke($this);
				$this->rememberDecodedObject($index, $instance);
			}
			else {
				$instance = $this->instantiate($customSerializer, $instanceClass);
				
				$this->rememberDecodedObject($index, $instance);
				
				$replacement = $this->deserializeImpl($customSerializer, $instanceClass, $instance);
				
				// It's possible that deserializing an object requires the original proxy
				// object to be replaced.
				if (instance !== $replacement) {
					$this->rememberDecodedObject($index, $replacement);
					$instance = $replacement;
				}
			}
			
			return $instance;
		}
		catch (Exception $e) {
			throw new SerializationException($e);
		}
	}
	
	private function getCustomSerializerTransform(Clazz $customSerializer = null) {
		if (is_null($customSerializer)) {
			return null;
		}
		
		return $customSerializer->getMethod('transform');
	}
	
	protected function getString($index) {
		if ($index == 0) {
			return null;
		}
		// index is 1-based
		assert($index > 0);
		assert($index <= count($this->stringTable));
		return $this->stringTable[$index - 1];
	}
	
	/**
	 * Deserialize an instance that is an array. Will default to deserializing as 
	 * an Object vector if the instance is not a primitive vector.
	 */
	private function deserializeArray(Clazz $instanceClass, $instance) {
		assert($instanceClass->isArray());
		
		$buffer = $instance;
		$instanceReader = self::$CLASS_TO_VECTOR_READER->get($instanceClass);
		if (!is_null($instanceReader)) {
			return $instanceReader->read($this, $buffer);
		}
		else {
			$reader = new SSSR_VectorReader_Object();
			return $reader->read($this, $buffer);
		}
	}
	
	private function deserializeClass(Clazz $instanceClass, $instance) {
		/**
		 * A map from field names to corresponding setter methods. The reference 
		 * will be null for classes that do not require special handling for
		 * server-only fields.
		 */
		$setters = null;
		
		/**
		 * A list of fields of this class known to the client. If null, assume the class is not
		 * enhanced and don't attempt to deal with server-only fields.
		 */
		$clientFieldNames = $this->serializationPolicy->getClientFieldNamesForEnhancedClass($instanceClass);
		if (!is_null($clientFieldNames)) {
			// Read and set server-only instance fields encoded in RPC data
			try {
				$encodedData = $this->readString();
				if (!is_null($encodedData)) {
					$serializedData = Base64Utils::fromBase6($encodedData);
					$ois = new ObjectInputStream($serializedData);
					
					$count = $ois->readInt();
					for ($i=0; $i<$count; $i++) {
						$fieldName = $ois->readString();
						$fieldValue = $ois->readObject();
						$field = $instanceClass->getField($fieldName);
						$field->setAccessible(true);
						$field->setValue($instance, $fieldValue);
					}
				}
			}
			catch (Exception $e) {
				throw new SerializationException($e);
			}
			
			$setters = $this->getSetters($instanceClass);
		}
		
		$serializableFields = SerializabilityUtilEx::applyFieldSerializationPolicy($instanceClass);
		foreach ($serializableFields as $declField) {
			assert(!is_null($declField));
			if ((!is_null($clientFieldNames) 
					&& !$clientFieldNames->contains($declField->getName()))) {
				continue;	
			}
			assert($declField->hasType());
			$value = $this->deserializeValue($declField->getType());
			
			$fieldName = $declField->getName();
			$setter = null;
			/*
			 * If setters is non-null and there is a setter method for the given
			 * field, call the setter. Otherwise, set the field value directly. For
			 * persistence APIs such as JDO, the setter methods have been enhanced to
			 * manipulate additional object state, causing direct field writes to fail
			 * to update the object state properly.
			 */
			if (!is_null($setters) && (!is_null($setters = $setters->get($fieldName)))) {
				$setter->invoke($instance, $value);
			}
			else {
				$isAccessible = $declField->isAccessible();
				$needsAccessOverride = !$isAccessible
					&& !$declField->isPublic();
				if ($needsAccessOverride) {
					// Override access restrictions
					$declField->setAccessible(true);
				}
				$declField->setValue($instance, $value);
			}
		}
		
		$superClass = $instanceClass->getSuperClass();
		if ($this->serializationPolicy->shouldDeserializeFields($superClass)) {
			$this->deserializeImpl(SerializabilityUtilEx::hasCustomFieldSerializer($superClass),
				$superClass, $instance);
		}
	}
	
	private function deserializeImpl(Clazz $customSerializer = null, Clazz $instanceClass, $instance) {
		if (!is_null($customSerializer)) {
			$customFieldSerializer = SerializabilityUtilEx::loadCustomFieldSerializer($customSerializer);
			if (is_null($customFieldSerializer)) {
				$this->deserializeWithCustomFieldDeserializer($customSerializer, $instanceClass, $instance);
			}
			else {
				$customFieldSerializer->deserializeInstance($this, $instance);
			}
		}
		else if ($instanceClass->isArray()) {
			$instance = $this->deserializeArray($instanceClass, $instance);
		}
		else if ($instanceClass->isEnum()) {
			// Enums are deserialized when they are instantiated
		}
		else {
			$this->deserializeClass($instanceClass, $instance);
		}
		
		return $instance;
	}
	
	private function deserializeStringTable() {
		$typeNameCount = $this->readInt();
		$buffer = new SSSR_BoundedList(String::clazz(), $typeNameCount);
		for ($typeNameIndex=0; $typeNameIndex < $typeNameCount; ++$typeNameIndex) {
			$str = $this->extract();
			// Change quoted characters back.
			$idx = mb_strpos($str, '\\');
			if ($idx !== false) {
				$buf = '';
				$pos = 0;
				while ($idx >= 0) {
					$buf .= mb_substr($str, $pos, $idx - $pos);
					if (++$idx == mb_strlen($str)) {
						throw new SerializationException("Unmatched backslash: \"$str\"");
					}
					$ch = mb_substr($str, $idx, 1);
					$pos = $idx + 1;
					switch (Character::ord($ch)) {
						case Character::ord('0'): 
							$buf .= '\u0000';
							break;
						case Character::ord('!'):
							$buf .= self::RPC_SEPARATOR_CHAR;
							break;
						case Character::ord('\\'):
							$buf .= $ch;
							break;
						case Character::ord('u'): 
							try {
								$ch = Character::chr(Integer::parseHex(mb_substr($str, $idx + 1, 4)));
							}
							catch (NumberFormatException $e) {
								throw new SerializationException("Invalid Unicode escape sequence \"$str\"");	
							}
							$buf .= $ch;
							$pos += 4;
							break;
						default:
							throw new SerializationException("Unexpected escape character $ch after backslash: \"$str\"");
					}
					$idx = mb_strpos($str, '\\', $pos);
				}
				$buf .= mb_substr($str, $pos);
				$str = buf;
			}
			$buffer->add($str);
		}
		
		if ($buffer->size() != $buffer->getExpectedSize()) {
			throw new SerializationException('Expected ' . $buffer->getExpectedSize()
				. ' string table elements; received ' . $buffer->size());
		}
		
		$this->stringTable = $buffer->toArray();
	}
	
	private function deserializeWithCustomFieldDeserializer(Clazz $customSerializer, Clazz $instanceClass, $instance) {
		assert(!$instanceClass->isArray());

		foreach ($customSerializer->getMethods() as $method) {
			if ($method->getName() === 'deserialize') {
				$method->invoke($this, $instance);
				return;
			}
		}
		throw new NoSuchMethodException('deserialize');
	}
	
	private function extract() {
		$index = $this->tokenListIndex++;
		if (!isset($this->tokenList[$index])) {
			throw new SerializationException('Too few tokens in RPC request', $e);
		}
		else {
			return $this->tokenList[$index];
		}
	}
	
	private function getNumberFormatException($value, $type, $minValue, $maxValue) {
		$message = 'a non-numerical value';
		try {
			// Check the field contents in order to produce a more comprehensible
			// error message
			$d = Double::parseDouble($value);
			if ($d < $minValue || $d > $maxValue) {
				$message = 'an out-of-range value';
			}
			else if ($d != floor($d)) {
				$message = 'a fractional value';	
			}
		}
		catch (NumberFormatException $e) {	
		}
		return new NumberFormatException("Expected type '$type' but received $message : $value");
	}
	
	private function getSetters(Clazz $instanceClass) {
		$setters = $this->settersByClass->get($instanceClass);
		if (is_null($setters)) {
			$setters = new HashMap();
			
			// Iterate over each field and locate a suitable setter method
			$fields = $instanceClass->getFields();
			foreach ($fields as $field) {
				// Consider non-final, non-static, non-transient (or @GwtTransient) fields only
				if (SerialiabilityUtil::isNotStaticTransientOrFinal($field)) {
					$fieldName = $field->getName();
					$setterName = 'set' . mb_strtoupper(mb_substr($fieldName, 0, 1)) . mb_substr($fieldName, 1);
					try {
						$setter = $instanceClass->getMethod($setterName);
						$setters->put($fieldName, $setter);
					}
					catch (NoSuchMethodException $e) {
						//Just leave this field out of the map	
					}
				}
			}
			
			$this->settersByClass->put($instanceClass, $setters);
		}
		return $setters;
	}
	
	private function instantiate(Clazz $customSerializer = null, Clazz $instanceClass) {
		if (!is_null($customSerializer)) {
			$customFieldSerialize = SerializabilityUtilEx::loadCustomFieldSerializer($customSerializer);
			if (is_null($customFieldSerialize)) {
				foreach ($customSerializer->getMethods() as $method) {
					if ($method->getName() === 'instantiate') {
						return $method->invoke($this);
					}
				}
				// Ok to not have one
			}
			else if ($customFieldSerialize->hasCustomInstantiateInstance()) {
				return $customFieldSerialize->instantiateInstance($this);
			}
		}
		
		if ($instanceClass->isArray()) {
			$length = $this->readInt();
			// We don't pre-allocate the array; this prevents an allocation attack
			return new SSSR_BoundedList($instanceClass->getComponentType(), $length);
		}
		else if ($instanceClass->isEnum()) {
			// Bypass enum transformation
			$ordinal = $this->readInt();
			$values = $instanceClass->getEnumValues();
			assert(in_array($ordinal, $values, true));
			return $ordinal;
		}
		else {
			$constructor = $instanceClass->getConstructor();
			$constructor->setAccessible(true);
			return $constructor->newInstance();
		}
	}
	
	private function validateTypeVersions(Clazz $instanceClass, SerializedInstanceReference $serializedInstRef) {
		$clientTypeSignature = $serializedInstRef->getSignature();
		if (empty($clientTypeSignature)) {
			throw new SerializationException('Missin type signature for "' . $instanceClass->getFullName() . '"');
		}
		
		$serverTypeSignature = SerializabilityUtilEx::getSerializationSignature($instanceClass, $this->serializationPolicy);
		
		if ($clientTypeSignature !== $serverTypeSignature) {
			throw new SerializationException('Invalid type signature for "' . $instanceClass->getFullName() . '"');
		}
	}
	
}
ServerSerializationStreamReader::init();
