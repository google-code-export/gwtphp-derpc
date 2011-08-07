<?php

require_once PHPRPC_ROOT . 'derpc/SerializabilityUtil.php';

require_once PHPRPC_ROOT . 'rpc/CustomFieldSerializer.php';
require_once PHPRPC_ROOT . 'rpc/CRC32.php';
require_once PHPRPC_ROOT . 'rpc/SerializedInstanceReference.php';

class SerializabilityUtilEx_NoSuchSerializer extends CustomFieldSerializer {
	
	public function deserializeInstance(SerializationStreamReader $streamReader, $instance) {
		throw new AssertionError('This should never be called.');
	}
	
	public function serializeInstance(SerializationStreamWriter $streamWriter, $instance) {
		throw new AssertionError('This should never be called.');
	}
	
}

class SerializabilityUtilEx_SerializedInstanceReference implements SerializedInstanceReference {
	
	private $components;
	
	public function __construct($components) {
		$this->components = $components;
	}
	
	public function getName() {
		return count($this->components) > 0 ? $this->components[0] : '';
	}
	
	public function getSignature() {
		return count($this->components) > 1 ? $this->components[1] : '';
	}
}

class SerializabilityUtilEx extends SerializabilityUtil {
	
	const DEFAULT_ENCODING = 'UTF-8';
	
	private static $CLASS_TO_SERIALIZER_INSTANCE;
	private static $NO_SUCH_SERIALIZER;
	private static $SERIALIZED_PRIMITIVE_TYPE_NAMES;
	private static $TYPES_WHOSE_IMPLEMENTATION_IS_EXCLUDED_FROM_SIGNATURES;
	
	private static $classCRC32Cache;
	
	public static function init() {
		self::$classCRC32Cache = new IdentityHashMap();
		
		self::$CLASS_TO_SERIALIZER_INSTANCE = new IdentityHashMap();
		self::$NO_SUCH_SERIALIZER = new SerializabilityUtilEx_NoSuchSerializer();
		
		self::$SERIALIZED_PRIMITIVE_TYPE_NAMES = new HashMap();
		self::$TYPES_WHOSE_IMPLEMENTATION_IS_EXCLUDED_FROM_SIGNATURES = new HashSet();

		self::$SERIALIZED_PRIMITIVE_TYPE_NAMES->put(Boolean::typeClass()->getFullName(), "Z");
		self::$SERIALIZED_PRIMITIVE_TYPE_NAMES->put(Byte::typeClass()->getFullName(), "B");
		self::$SERIALIZED_PRIMITIVE_TYPE_NAMES->put(Character::typeClass()->getFullName(), "C");
		self::$SERIALIZED_PRIMITIVE_TYPE_NAMES->put(Double::typeClass()->getFullName(), "D");
		self::$SERIALIZED_PRIMITIVE_TYPE_NAMES->put(Float::typeClass()->getFullName(), "F");
		self::$SERIALIZED_PRIMITIVE_TYPE_NAMES->put(Integer::typeClass()->getFullName(), "I");
		self::$SERIALIZED_PRIMITIVE_TYPE_NAMES->put(Long::typeClass()->getFullName(), "J");
		self::$SERIALIZED_PRIMITIVE_TYPE_NAMES->put(Short::typeClass()->getFullName(), "S");

		self::$TYPES_WHOSE_IMPLEMENTATION_IS_EXCLUDED_FROM_SIGNATURES->add(Boolean::clazz());
		self::$TYPES_WHOSE_IMPLEMENTATION_IS_EXCLUDED_FROM_SIGNATURES->add(Byte::clazz());
		self::$TYPES_WHOSE_IMPLEMENTATION_IS_EXCLUDED_FROM_SIGNATURES->add(Character::clazz());
		self::$TYPES_WHOSE_IMPLEMENTATION_IS_EXCLUDED_FROM_SIGNATURES->add(Double::clazz());
		//TODO Exception class
		//self::$TYPES_WHOSE_IMPLEMENTATION_IS_EXCLUDED_FROM_SIGNATURES->add(Exception::clazz());
		self::$TYPES_WHOSE_IMPLEMENTATION_IS_EXCLUDED_FROM_SIGNATURES->add(Float::clazz());
		self::$TYPES_WHOSE_IMPLEMENTATION_IS_EXCLUDED_FROM_SIGNATURES->add(Integer::clazz());
		self::$TYPES_WHOSE_IMPLEMENTATION_IS_EXCLUDED_FROM_SIGNATURES->add(Long::clazz());
		self::$TYPES_WHOSE_IMPLEMENTATION_IS_EXCLUDED_FROM_SIGNATURES->add(Object::clazz());
		self::$TYPES_WHOSE_IMPLEMENTATION_IS_EXCLUDED_FROM_SIGNATURES->add(Short::clazz());
		self::$TYPES_WHOSE_IMPLEMENTATION_IS_EXCLUDED_FROM_SIGNATURES->add(String::clazz());
		self::$TYPES_WHOSE_IMPLEMENTATION_IS_EXCLUDED_FROM_SIGNATURES->add(Classes::classOf('Throwable'));
	}
	
	public static function decodeSerializedInstanceReference($encodedSerializedInstanceReference) {
		$components = explode(SerializedInstanceReference::SERIALIZED_REFERENCE_SEPARATOR, $encodedSerializedInstanceReference);
		return new SerializabilityUtilEx_SerializedInstanceReference($components);
	}
	
	public static function encodeSerializedInstanceReference(Clazz $instanceType, SerializationPolicy $policy) {
		return $instanceType->getFullName()
			. SerializedInstanceReference::SERIALIZED_REFERENCE_SEPARATOR
			. self::getSerializationSignature($instanceType, $policy);
	}
	
	public static function getSerializationSignature(Clazz $instanceType, SerializationPolicy $policy) {
		//TODO improvement for php
		if ($policy instanceof StandardSerializationPolicy) {
			return $policy->getIdForClass($instanceType);
		}
		else {
			$result = self::$classCRC32Cache->get($instanceType);
			if (is_null($result)) {
				$crc = new CRC32();
				self::generateSerializationSignature($instanceType, $crc, $policy);
				$result = Long::toString($crc->getValue());
				self::$classCRC32Cache->put($instanceType, $result);
			}
			return $result;
		}
	}
	
	public static function getSerializedTypeName(Clazz $instanceType) {
		if ($instanceType->isPrimitive()) {
			return self::$SERIALIZED_PRIMITIVE_TYPE_NAMES->get($instanceType->getFullName());
		}
		return $instanceType->getFullName();
	}
	
	public static function loadCustomFieldSerializer(Clazz $customSerializerClass) {
		$customFieldSerializer = self::$CLASS_TO_SERIALIZER_INSTANCE->get($customSerializerClass);
		if (is_null($customFieldSerializer)) {
			if (Classes::classOf('CustomFieldSerializer')->isAssignableFrom($customSerializerClass)) {
				$customFieldSerializer = $customSerializerClass->newInstance();
			}
			else {
				$customFieldSerializer = self::$NO_SUCH_SERIALIZER;
			}
			self::$CLASS_TO_SERIALIZER_INSTANCE->put($customSerializerClass, $customFieldSerializer);
		}
		if ($customFieldSerializer == self::$NO_SUCH_SERIALIZER) {
			return null;
		}
		else {
			return $customFieldSerializer;
		}
	}
	
	private static function excludeImplementationFromSerializationSignature(Clazz $instanceType) {
		if (self::$TYPES_WHOSE_IMPLEMENTATION_IS_EXCLUDED_FROM_SIGNATURES->contains($instanceType)) {
			return true;
		}
		return false;
	}
	
	private static function generateSerializationSignature(Clazz $instanceType, CRC32 $crc, 
			SerializationPolicy $policy) {
		$crc->update(self::getSerializedTypeName($instanceType));
		
		if (self::excludeImplementationFromSerializationSignature($instanceType)) {
			return;
		}
		
		$customSerializer = self::hasCustomFieldSerializer($instanceType);
		if (!is_null($customSerializer)) {
			self::generateSerializationSignature($customSerializer, $crc, $policy);
		}
		else if ($instanceType->isArray()) {
			self::generateSerializationSignature($instanceType->getComponentType(), $crc, $policy);
		}
		else if (!$instanceType->isPrimitive()) {
			$fields = self::applyFieldSerializationPolicy($instanceType);
			$clientFieldNames = $policy->getClientFieldNamesForEnhancedClass($instanceType);
			foreach ($fields as $field) {
				assert(!is_null($field));
				/**
				 * If clientFieldNames is non-null, use only the fields listed there
				 * to generate the signature.  Otherwise, use all known fields.
				 */
				if (is_null($clientFieldNames) || $clientFieldNames->contains($field->getName())) {
					$crc->update($field->getName());
					$crc->update(self::getSerializedTypeName($field->getType()));
				}
			}
			
			$superClass = $instanceType->getSuperClass();
			if (!is_null($superClass)) {
				self::generateSerializationSignature($superClass, $crc, $policy);
			}
		}
	}
	
}
SerializabilityUtilEx::init();