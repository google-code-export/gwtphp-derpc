<?php

require_once PHPRPC_ROOT . 'collections.php';

require_once PHPRPC_ROOT . 'rpc/TypeNameObfuscator.php';

abstract class SerializationPolicy {
	
	public function getClientFieldNamesForEnhancedClass(Clazz $clazz) {
		return null;
	}
	
	public abstract function shouldDeserializeFields(Clazz $clazz);
	
	public abstract function shouldSerializeFields(Clazz $clazz);
	
	public abstract function validateDeserialize(Clazz $clazz);
	
	public abstract function validateSerialize(Clazz $clazz);
}

interface SerializationPolicyProvider {
	function getSerializationPolicy($moduleBaseURL, $serializationPolicyStrongName);
}

class StandardSerializationPolicy extends SerializationPolicy implements TypeNameObfuscator {
	
	private static function isFieldSerializable(Clazz $clazz, Map $whitelist) {
		if ($clazz->isPrimitive()) {
			return true;
		}
		return $whitelist->containsKey($clazz);
	}
	
	private static function isInstantiable(Clazz $clazz, Map $whitelist) {
		if ($clazz->isPrimitive()) {
			return true;
		}
		$instantiable = $whitelist->get($clazz);
		return ($instantiable != null && $instantiable);
	}
	
	private $clientFields;
	private $deserializationWhitelist;
	private $serializationWhitelist;
	private $typeIds;
	private $typeIdsToClasses = array();
	
	public function __construct(Map $serializationWhitelist, Map $deserializationWhitelist, 
			Map $obfuscatedTypeIds, Map $clientFields = null) {
		if (is_null($serializationWhitelist) || is_null($deserializationWhitelist)) {
			throw new NullPointerException('whitelist');
		}
		
		$this->serializationWhitelist = $serializationWhitelist;
		$this->deserializationWhitelist = $deserializationWhitelist;
		$this->typeIds = $obfuscatedTypeIds;
		$this->clientFields = $clientFields;
		
		foreach ($obfuscatedTypeIds->entryIterator() as $entry) {
			$key = $entry->getKey();
			$value = $entry->getValue();
			assert(!is_null($key)); // null key
			assert(!is_null($value)); // null value for key->getName();
			assert(!isset($this->typeIdsToClasses[$value])); // Duplicate type id value
			$this->typeIdsToClasses[$value] = $key;
		}
	}
	
	// Override
	public final function getClassNameForTypeId($id) {
		if (!isset($this->typeIdsToClasses[$id])) {
			return null;
		}
		return $this->typeIdsToClasses[$id]->getName();
	} 
	
	// Override
	public final function getClientFieldNamesForEnhancedClass(Clazz $clazz) {
		if (is_null($this->clientFields)) {
			return null;
		}
		$fieldNames = $this->clientFields->get($clazz);
		return is_null($fieldNames) ? null : Collections::unmodifiableSet($fieldNames);
	} 
	
	public function getTypeIdForClass(Clazz $clazz) {
		return $this->typeIds->get($clazz);
	}
	
	public function getIdForClass(Clazz $clazz) {
		$res = $this->typeIds->get($clazz);
		if (is_null($res)) {
			return null;
		}
		list($name, $id) = explode('/', $res);
		return $id;
	}
	
	// Override
	public function shouldDeserializeFields(Clazz $clazz) {
		return self::isFieldSerializable($clazz, $this->deserializationWhitelist);
	}
	
	// Override
	public function shouldSerializeFields(Clazz $clazz) {
		return self::isFieldSerializable($clazz, $this->serializationWhitelist);
	}
	
	// Override
	public function validateDeserialize(Clazz $clazz) {
		if (!self::isInstantiable($clazz, $this->deserializationWhitelist)) {
			throw new SerializationException(
				'Type \'' . $clazz->getFullName() . '\' was not included in the set of types which can be deserialized by SerizalizationPolicy or its Class object could not be loaded. For security purposes, this type will not be deserialized.'
			);
		}
	}
	
	// Override
	public function validateSerialize(Clazz $clazz) {
		if (!self::isInstantiable($clazz, $this->serializationWhitelist)) {
			throw new SerializationException(
				'Type \'' . $clazz->getFullName() . '\' was not included in the set of types which can be serialized by SerizalizationPolicy or its Class object could not be loaded. For security purposes, this type will not be serialized.'
			);
		}
	}
	
}

class LegacySerializationPolicy extends SerializationPolicy implements TypeNameObfuscator {
	
	const ELISION_ERROR = 'Type name elision in RPC payloads is only supported if the RPC whitelist file is used.';
	
	private static $JRE_BLACKLIST;
	private static $JRE_BLACKSET;
	private static $sInstance;
	
	public static function init() {
		$JRE_BLACKLIST = array(
			// java.lang.ArrayStoreException.class, 
			// java.lang.AssertionError.class,
			Boolean::clazz(),
			Byte::clazz(),
			Clazz::clazz(), 
			// java.lang.ClassCastException.class,
			Double::clazz(),
			// java.lang.Error.class,
			Float::clazz(),
			// java.lang.IllegalArgumentException.class,
     		// java.lang.IllegalStateException.class,
     		// java.lang.IndexOutOfBoundsException.class,
			Integer::clazz(),
			Long::clazz(),
			// java.lang.NegativeArraySizeException.class,
     		// java.lang.NullPointerException.class,
			// java.lang.Number.class,
			// java.lang.NumberFormatException.class, 
			Short::clazz(), 
     		// java.lang.StackTraceElement.class, 
     		String::clazz(),
     		// java.lang.StringBuffer.class,
     		// java.lang.StringIndexOutOfBoundsException.class,
     		// java.lang.UnsupportedOperationException.class, 
     		Classes::classOf('ArrayList'),
     		// java.util.ConcurrentModificationException.class, 
     		Classes::classOf('Date'), 
     		// java.util.EmptyStackException.class, 
     		// java.util.EventObject.class,
     		Classes::classOf('HashMap'),
     		Classes::classOf('HashSet'),
     		// java.util.MissingResourceException.class,
     		// java.util.NoSuchElementException.class, 
     		Classes::classOf('Stack'),
     		// java.util.TooManyListenersException.class, 
     		Classes::classOf('Vector')
     	);
     	
     	self::$JRE_BLACKSET = new HashSet(self::$JRE_BLACKLIST);
     	
     	self::$sInstance = new LegacySerializationPolicy();
	}
	
	public static function getInstance() {
		return self::$sInstance;
	}
	
	public function getClassNameForTypeId($id) {
		throw new SerializationException(self::ELISION_ERROR);
	}
	
	public function getTypeIdForClass(Clazz $clazz) {
		throw new SerializationException(self::ELISION_ERROR);
	}
	
	public function shouldDeserializeFields(Clazz $clazz) {
		return self::isFieldSerializable($clazz);
	}
	
	public function shouldSerializeFields(Clazz $clazz) {
		return self::isFieldSerializable($clazz);
	}
	
	public function validateDeserialize(Clazz $clazz) {
		if (!$this->isInstantiable($clazz)) {
			throw new SerializationException('Type \'' . $clazz->getFullName()
          . '\' was not assignable to \'' . Classes::classOf('IsSerializable')->getFullName()
          . '\' and did not have a custom field serializer. '
          . 'For security purposes, this type will not be deserialized.');
		}
	}
	
	public function validateSerialize(Clazz $clazz) {
		throw new SerializationException('Type \'' . $clazz->getFullName()
          . '\' was not assignable to \'' . Classes::classOf('IsSerializable')->getFullName()
          . '\' and did not have a custom field serializer.'
          . 'For security purposes, this type will not be serialized.');
	}
	
	private function isFieldSerializable(Clazz $clazz) {
		if ($this->isInstantiable($clazz)) {
			return true;
		}
		if (Classes::classOf('IsSerializable')->isAssignableFrom($clazz)) {
			return !self::$JRE_BLACKSET->contains($clazz);
		}
		return false;
	}
	
	private function isInstantiable(Clazz $clazz) {
		if ($clazz->isPrimitive()) {
			return true;
		}
		if ($clazz->isArray()) {
			return $this->isInstantiable($clazz->getComponentType());
		}
		if (Classes::classOf('IsSerializable')->isAssignableFrom($clazz)) {
			return true;
		}
		return Serializability::hasCustomFieldSerializer($clazz) != null;
	}
}
