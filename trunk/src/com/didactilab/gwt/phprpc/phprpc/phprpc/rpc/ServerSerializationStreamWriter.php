<?php

require_once PHPRPC_ROOT . 'classes.php';
require_once PHPRPC_ROOT . 'rpc/Base64Utils.php';
require_once PHPRPC_ROOT . 'rpc/SerializabilityUtilEx.php';
require_once PHPRPC_ROOT . 'rpc/ServerSerializationStream.php';


class SSSW_LengthConstrainedArray {
	const MAXIMUM_ARRAY_LENGTH = 32768; // 1 << 15
	const POSTLUDE = '])';
	const PRELUDE = '].concat([';
	
	private $Buffer;
	private $count;
	private $needsComma = false;
	private $total = 0;
	
	public function _construct() {
		$this->buffer = '';
	}
	
	public function addToken($token) {
		$this->total++;
		if ($this->count++ == self::MAXIMUM_ARRAY_LENGTH) {
			if ($this->total == self::MAXIMUM_ARRAY_LENGTH + 1) {
				$this->buffer .= self::PRELUDE;
			}
			else {
				$this->buffer .= '],[';
			}
			$this->count = 0;
			$this->needsComma = false;
		}
		
		if ($this->needsComma) {
			$this->buffer .= ',';
		}
		else {
			$this->needsComma = true;
		}
		
		$this->buffer .= $token;
	}
	
	public function __toString() {
		if ($this->total > self::MAXIMUM_ARRAY_LENGTH) {
			return '[' . $this->buffer . self::POSTLUDE;
		}
		else {
			return '[' . $this->buffer . ']';
		}
	}
}

abstract class SSSW_ValueWriter {
	public static $BOOLEAN;
	public static $BYTE;
	public static $CHAR;
	public static $DOUBLE;
	public static $FLOAT;
	public static $INT;
	public static $LONG;
	public static $OBJECT;
	public static $SHORT;
	public static $STRING;
	
	public static function init() {
		self::$BOOLEAN = new SSSW_ValueWriter_Boolean();
		self::$BYTE = new SSSW_ValueWriter_Byte();
		self::$CHAR = new SSSW_ValueWriter_Char();
		self::$DOUBLE = new SSSW_ValueWriter_Double();
		self::$FLOAT = new SSSW_ValueWriter_Float();
		self::$INT = new SSSW_ValueWriter_Int();
		self::$LONG = new SSSW_ValueWriter_Long();
		self::$OBJECT = new SSSW_ValueWriter_Object();
		self::$SHORT = new SSSW_ValueWriter_Short();
		self::$STRING = new SSSW_ValueWriter_String();
	}
	
	public abstract function write(ServerSerializationStreamWriter $stream, $instance); 
}
SSSW_ValueWriter::init();

class SSSW_ValueWriter_Boolean extends SSSW_ValueWriter {
	public function write(ServerSerializationStreamWriter $stream, $instance) {
		$stream->writeBoolean($instance/*->value()*/);
	}
}

class SSSW_ValueWriter_Byte extends SSSW_ValueWriter {
	public function write(ServerSerializationStreamWriter $stream, $instance) {
		$stream->writeByte($instance/*->value()*/);
	}
}

class SSSW_ValueWriter_Char extends SSSW_ValueWriter {
	public function write(ServerSerializationStreamWriter $stream, $instance) {
		$stream->writeChar($instance/*->value()*/);
	}
}

class SSSW_ValueWriter_Double extends SSSW_ValueWriter {
	public function write(ServerSerializationStreamWriter $stream, $instance) {
		$stream->writeDouble($instance/*->value()*/);
	}
}

class SSSW_ValueWriter_Float extends SSSW_ValueWriter {
	public function write(ServerSerializationStreamWriter $stream, $instance) {
		$stream->writeFloat($instance/*->value()*/);
	}
}

class SSSW_ValueWriter_Int extends SSSW_ValueWriter {
	public function write(ServerSerializationStreamWriter $stream, $instance) {
		$stream->writeInt($instance/*->value()*/);
	}
}

class SSSW_ValueWriter_Long extends SSSW_ValueWriter {
	public function write(ServerSerializationStreamWriter $stream, $instance) {
		$stream->writeLong($instance/*->value()*/);
	}
}

class SSSW_ValueWriter_Object extends SSSW_ValueWriter {
	public function write(ServerSerializationStreamWriter $stream, $instance) {
		$stream->writeObject($instance);
	}
	
	public function writeTyped(ServerSerializationStreamWriter $stream, $instance, Clazz $instanceClass) {
		$stream->writeTypedObject($instance, $instanceClass);
	}
}

class SSSW_ValueWriter_Short extends SSSW_ValueWriter {
	public function write(ServerSerializationStreamWriter $stream, $instance) {
		$stream->writeShort($instance/*->value()*/);
	}
}

class SSSW_ValueWriter_String extends SSSW_ValueWriter {
	public function write(ServerSerializationStreamWriter $stream, $instance) {
		$stream->writeString($instance);
	}
}

abstract class SSSW_VectorWriter {
	public static $BOOLEAN_VECTOR;
	public static $BYTE_VECTOR;
	public static $CHAR_VECTOR;
	public static $DOUBLE_VECTOR;
	public static $FLOAT_VECTOR;
	public static $INT_VECTOR;
	public static $LONG_VECTOR;
	public static $OBJECT_VECTOR;
	public static $SHORT_VECTOR;
	public static $STRING_VECTOR;
	
	public static function init() {
		self::$BOOLEAN_VECTOR = new SSSW_VectorWriter_Boolean();
		self::$BYTE_VECTOR = new SSSW_VectorWriter_Byte();
		self::$CHAR_VECTOR = new SSSW_VectorWriter_Char();
		self::$DOUBLE_VECTOR = new SSSW_VectorWriter_Double();
		self::$FLOAT_VECTOR = new SSSW_VectorWriter_Float();
		self::$INT_VECTOR = new SSSW_VectorWriter_Int();
		self::$LONG_VECTOR = new SSSW_VectorWriter_Long();
		self::$OBJECT_VECTOR = new SSSW_VectorWriter_Object();
		self::$SHORT_VECTOR = new SSSW_VectorWriter_Short();
		self::$STRING_VECTOR = new SSSW_VectorWriter_String();
	}
	
	public abstract function write(ServerSerializationStreamWriter $stream, $instance);
}
SSSW_VectorWriter::init();

class SSSW_VectorWriter_Boolean extends SSSW_VectorWriter {
	public function write(ServerSerializationStreamWriter $stream, $instance) {
		$stream->writeInt(count($instance));
		for ($i=0, $n=count($instance); $i<$n; ++$i) {
			$stream->writeBoolean($instance[$i]);
		}
	}
}

class SSSW_VectorWriter_Byte extends SSSW_VectorWriter {
	public function write(ServerSerializationStreamWriter $stream, $instance) {
		$stream->writeInt(count($instance));
		for ($i=0, $n=count($instance); $i<$n; ++$i) {
			$stream->writeByte($instance[$i]);
		}
	}
}

class SSSW_VectorWriter_Char extends SSSW_VectorWriter {
	public function write(ServerSerializationStreamWriter $stream, $instance) {
		$stream->writeInt(count($instance));
		for ($i=0, $n=count($instance); $i<$n; ++$i) {
			$stream->writeChar($instance[$i]);
		}
	}
}

class SSSW_VectorWriter_Double extends SSSW_VectorWriter {
	public function write(ServerSerializationStreamWriter $stream, $instance) {
		$stream->writeInt(count($instance));
		for ($i=0, $n=count($instance); $i<$n; ++$i) {
			$stream->writeDouble($instance[$i]);
		}
	}
}

class SSSW_VectorWriter_Float extends SSSW_VectorWriter {
	public function write(ServerSerializationStreamWriter $stream, $instance) {
		$stream->writeInt(count($instance));
		for ($i=0, $n=count($instance); $i<$n; ++$i) {
			$stream->writeFloat($instance[$i]);
		}
	}
}

class SSSW_VectorWriter_Int extends SSSW_VectorWriter {
	public function write(ServerSerializationStreamWriter $stream, $instance) {
		$stream->writeInt(count($instance));
		for ($i=0, $n=count($instance); $i<$n; ++$i) {
			$stream->writeInt($instance[$i]);
		}
	}
}

class SSSW_VectorWriter_Long extends SSSW_VectorWriter {
	public function write(ServerSerializationStreamWriter $stream, $instance) {
		$stream->writeInt(count($instance));
		for ($i=0, $n=count($instance); $i<$n; ++$i) {
			$stream->writeLong($instance[$i]);
		}
	}
}

class SSSW_VectorWriter_Object extends SSSW_VectorWriter {
	public function write(ServerSerializationStreamWriter $stream, $instance) {
		$stream->writeInt(count($instance));
		for ($i=0, $n=count($instance); $i<$n; ++$i) {
			$stream->writeObject($instance[$i]);
		}
	}
}

class SSSW_VectorWriter_Short extends SSSW_VectorWriter {
	public function write(ServerSerializationStreamWriter $stream, $instance) {
		$stream->writeInt(count($instance));
		for ($i=0, $n=count($instance); $i<$n; ++$i) {
			$stream->writeShort($instance[$i]);
		}
	}
}

class SSSW_VectorWriter_String extends SSSW_VectorWriter {
	public function write(ServerSerializationStreamWriter $stream, $instance) {
		$stream->writeInt(count($instance));
		for ($i=0, $n=count($instance); $i<$n; ++$i) {
			$stream->writeString($instance[$i]);
		}
	}
}

class ServerSerializationStreamWriter extends AbstractSerializationStreamWriter {
	
	const NUMBER_OF_JS_ESCAPED_CHARS = 128;
	const JS_ESCAPE_CHAR = '\\';
	const JS_QUOTE_CHAR = '"';
	const NON_BREAKING_HYPHEN = "\x20\x11";
	
	private static $CLASS_TO_VALUE_WRITER;
	private static $CLASS_TO_VECTOR_WRITER;
	private static $JS_CHARS_ESCAPED = array();
	
	private static $NIBBLE_TO_HEX_CHAR = array(
		'0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D',
		'E', 'F'
	);
	
	private $serializationPolicy;
	private $tokenList = array();
	private $tokenListCharCount;
	
	public static function init() {
		self::$JS_CHARS_ESCAPED[chr(0)] = '0';
		self::$JS_CHARS_ESCAPED["\x08"] = 'b';
		self::$JS_CHARS_ESCAPED["\t"] = 't';
		self::$JS_CHARS_ESCAPED["\n"] = 'n';
		self::$JS_CHARS_ESCAPED["\f"] = 'f';
		self::$JS_CHARS_ESCAPED["\r"] = 'r';
		self::$JS_CHARS_ESCAPED[self::JS_ESCAPE_CHAR] = self::JS_ESCAPE_CHAR;
		self::$JS_CHARS_ESCAPED[self::JS_QUOTE_CHAR] = self::JS_QUOTE_CHAR;
		
		self::$CLASS_TO_VALUE_WRITER = new IdentityHashMap();
		self::$CLASS_TO_VECTOR_WRITER = new IdentityHashMap();
		
		self::$CLASS_TO_VECTOR_WRITER->put(Classes::classOf('boolean[]'), SSSW_VectorWriter::$BOOLEAN_VECTOR);
		self::$CLASS_TO_VECTOR_WRITER->put(Classes::classOf('byte[]'), SSSW_VectorWriter::$BYTE_VECTOR);
		self::$CLASS_TO_VECTOR_WRITER->put(Classes::classOf('char[]'), SSSW_VectorWriter::$CHAR_VECTOR);
		self::$CLASS_TO_VECTOR_WRITER->put(Classes::classOf('double[]'), SSSW_VectorWriter::$DOUBLE_VECTOR);
		self::$CLASS_TO_VECTOR_WRITER->put(Classes::classOf('float[]'), SSSW_VectorWriter::$FLOAT_VECTOR);
		self::$CLASS_TO_VECTOR_WRITER->put(Classes::classOf('int[]'), SSSW_VectorWriter::$INT_VECTOR);
		self::$CLASS_TO_VECTOR_WRITER->put(Classes::classOf('long[]'), SSSW_VectorWriter::$LONG_VECTOR);
		self::$CLASS_TO_VECTOR_WRITER->put(Classes::classOf('Object[]'), SSSW_VectorWriter::$OBJECT_VECTOR);
		self::$CLASS_TO_VECTOR_WRITER->put(Classes::classOf('short[]'), SSSW_VectorWriter::$SHORT_VECTOR);
		self::$CLASS_TO_VECTOR_WRITER->put(Classes::classOf('String[]'), SSSW_VectorWriter::$STRING_VECTOR);
		
		self::$CLASS_TO_VALUE_WRITER->put(Boolean::typeClass(), SSSW_ValueWriter::$BOOLEAN);
		self::$CLASS_TO_VALUE_WRITER->put(Byte::typeClass(), SSSW_ValueWriter::$BYTE);
		self::$CLASS_TO_VALUE_WRITER->put(Character::typeClass(), SSSW_ValueWriter::$CHAR);
		self::$CLASS_TO_VALUE_WRITER->put(Double::typeClass(), SSSW_ValueWriter::$DOUBLE);
		self::$CLASS_TO_VALUE_WRITER->put(Float::typeClass(), SSSW_ValueWriter::$FLOAT);
		self::$CLASS_TO_VALUE_WRITER->put(Integer::typeClass(), SSSW_ValueWriter::$INT);
		self::$CLASS_TO_VALUE_WRITER->put(Long::typeClass(), SSSW_ValueWriter::$LONG);
		self::$CLASS_TO_VALUE_WRITER->put(Object::clazz(), SSSW_ValueWriter::$OBJECT);
		self::$CLASS_TO_VALUE_WRITER->put(Short::typeClass(), SSSW_ValueWriter::$SHORT);
		self::$CLASS_TO_VALUE_WRITER->put(String::clazz(), SSSW_ValueWriter::$STRING);
	}
	
	public static function escapeString($toEscape) {
		// make output big enough to escape every character (plus the quotes)
		$charVector = self::JS_QUOTE_CHAR;
		for ($i=0, $n=mb_strlen($toEscape); $i<$n; ++$i) {
			$c = mb_substr($toEscape, $i, 1);
			if (self::needsUnicodeEscape($c)) {
				$charVector = self::unicodeEscape($c, $charVector);
			}
			else {
				$charVector .= $c;
			}
		}
		
		$charVector .= self::JS_QUOTE_CHAR;
		return $charVector;
	}
	
	private static function getClassForSerialization($instance, Clazz $instanceClass = null) {
		assert(!is_null($instance));
		
		/*if ($instance instanceof SerializedEnum) {
			return $instance->getEnumClass();
		}*/
		
		// TODO if enum
		/*if (instance instanceof Enum<?>) {
	      Enum<?> e = (Enum<?>) instance;
	      return e.getDeclaringClass();
	    } else {
	      return instance.getClass();
	    }*/
		if (is_null($instanceClass)) {
			return Classes::classOfValue($instance);
		}
		else {
			return $instanceClass;
		}
	}
	
	private static function needsUnicodeEscape($ch) {
		switch ($ch) {
			case ' ':
				// ASCII space gets caught in SPACE_SEPARATOR below, but does not
        		// need to be escaped
				return false;
			case self::JS_QUOTE_CHAR:
			case self::JS_ESCAPE_CHAR:
				// these must be quoted or they will break the protocol
				return true;
			case self::NON_BREAKING_HYPHEN:
				// This can be expanded into a break followed by a hyphen
				return true;
			default:
				//TODO unicode character type
				return false;
		}
	}
	
	private static function unicodeEscape($ch, $charVector) {
		$charVector .= self::JS_ESCAPE_CHAR;
		$ich = Charactor::ord($ch);
		if ($ich < self::NUMBER_OF_JS_ESCAPED_CHARS && isset(self::$JS_CHARS_ESCAPED[$ch])) {
			$charVector .= self::$JS_CHARS_ESCAPED[$ch];
		}
		else if ($ich < 256) {
			$charVector .= 'x' 
				. self::$NIBBLE_TO_HEX_CHAR[($ich >> 4) & 0x0F] 
				. self::$NIBBLE_TO_HEX_CHAR[$ich & 0x0F];
		}
		else {
			$charVector .= 'u'
				. self::$NIBBLE_TO_HEX_CHAR[($ich >> 12) & 0x0F]
				. self::$NIBBLE_TO_HEX_CHAR[($ich >> 8) & 0x0F]
				. self::$NIBBLE_TO_HEX_CHAR[($ich >> 4) & 0x0F]
				. self::$NIBBLE_TO_HEX_CHAR[$ich & 0x0F];
		}
	}
	
	public function __construct(SerializationPolicy $serializationPolicy) {
		parent::__construct();
		$this->serializationPolicy = $serializationPolicy;
	}
	
	public function prepareToWrite() {
		parent::prepareToWrite();
		$this->tokenList = array();
		$this->tokenListCharCount = 0;
	}
	
	public function serializeValue($value, Clazz $type) {
		$valueWriter = self::$CLASS_TO_VALUE_WRITER->get($type);
		if (!is_null($valueWriter)) {
			$valueWriter->write($this, $value);
		}
		else {
			if ($type->isEnum()) {
				$this->writeEnum($type, $value);
				//$this->writeEnum(new SerializedEnum($value, $type));
			}
			else {
				// arrays of primitive or reference types need to go through writeObject
				SSSW_ValueWriter::$OBJECT->writeTyped($this, $value, $type);
			}
		}
	}
	
	public function __toString() {
		// Build a JavaScript string (with escaping, of course).
		// We take a guess at how big to make to buffer to avoid numerous resizes.
		//
		//$capacityGuess = 2 * $this->tokenListCharCount + 2 * count($tokenList);
		$stream = new SSSW_LengthConstrainedArray(/*$capacityGuess*/);
		$this->writePayload($stream);
		$this->writeStringTable($stream);
		$this->writeHeader($stream);
		
		return (string) $stream;
	}
	
	public function writeLong($value) {
		if ($this->getVersion() == self::SERIALIZATION_STREAM_MIN_VERSION) {
			// Write longs as a pair of doubles for backwards compatibility
			list($low, $high) = $this->getAsDoubleArray($value);
			$this->writeDouble($low);
			$this->writeDouble($high);
		}
		else {
			$this->append('\'' . Base64Utils::toBase64($value) . '\'');
		}
	}
	
	protected function append($token) {
		$this->tokenList[] = $token;
		if (!is_null($token)) {
			$this->tokenListCharCount += mb_strlen($token);
		}
	}

	protected function getObjectTypeSignature($instance, Clazz $instanceClass = null) {
		assert(!is_null($instance));
		
		$clazz = self::getClassForSerialization($instance, $instanceClass);
		
		if ($this->hasFlags(self::FLAG_ELIDE_TYPE_NAMES)) {
			if ($this->serializationPolicy instanceof TypeNameObfuscator) {
				return $this->serializationPolicy->getTypeIdForClass($clazz);
			}
			
			throw new SerializationException(
				'The GWT module was compiled with RPC '
				. 'type name elision enabled, but '
				. Classes::classOf($this->serializationPolicy)->getFullName() . ' does not implement '
				. Classes::classOf('TypeNameObfuscator')->getFullName()
			);
		}
		else {
			return SerializabilityUtilEx::encodeSerializedInstanceReference($clazz, $this->serializationPolicy);
		}
	}
	
	protected function serialize($instance, $typeSignature, Clazz $instanceClass = null) {
		assert(!is_null($instance));
		
		$clazz = self::getClassForSerialization($instance, $instanceClass);
		try {
			$this->serializationPolicy->validateSerialize($clazz);
		}
		catch (SerializationException $e) {
			throw new SerializationException($e->getMessage() . ': instance = ' . $instance);
		}
		
		$this->serializeImpl($instance, $clazz);
	}
	
	private function serializeArray(Clazz $instanceClass, $instance) {
		assert($instanceClass->isArray());
		
		$instanceWriter = self::$CLASS_TO_VECTOR_WRITER->get($instanceClass);
		if (!is_null($instanceWriter)) {
			$instanceWriter->write($this, $instance);
		}
		else {
			SSSW_VectorWriter::$OBJECT_VECTOR->write($this, $instance);
		}
	}
	
	private function serializeClass($instance, Clazz $instanceClass) {
		assert(!is_null($instance));

		$serializableFields = SerializabilityUtilEx::applyFieldSerializationPolicy($instanceClass);

		/*
		 * If clientFieldNames is non-null, identify any additional server-only fields and serialize
		 * them separately.  Java serialization is used to construct a byte array, which is encoded
		 * as a String and written prior to the rest of the field data.
		 */
		$clientFieldNames = $this->serializationPolicy->getClientFieldNamesForEnhancedClass($instanceClass);
		if (!is_null($clientFieldNames)) {
			$serverFields = array();
			foreach ($serializableFields as $declField) {
				assert(!is_null($declField));

				// Identity server-only fields
				if (!$clientFieldNames->contains($declField->getName())) {
					$serverFields[] = $declField;
					continue;
				}
			}
				
			// Serialize the server-only fields into a byte array and encode as a String
			$oos = new ObjectOutputStream();
			$oos->writeInt(count($serverFields));
			foreach ($serverFields as $f) {
				$oos->writeObject($f->getName());
				$f->setAccessible(true);
				$fieldData = $f->get($instance);
				$oos->writeObject($fieldData);
			}
			$oos->close();
			$encodedData = Base64Utils::toBase64((string) $oos);
			$this->writeString($encodedData);
		}
		
		// Write the client-visible field data
		foreach ($serializableFields as $declField) {
			if (!is_null($clientFieldNames) && !$clientFieldNames->contains($decl->getName())) {
				// Skip server-only fields
				continue;
			}
			
			$isAccessible = $declField->isAccessible();
			$needsAccessOverride = !$isAccessible && !$declField->isPublic();
			if ($needsAccessOverride) {
				// Override the access restrictions
				$declField->setAccessible(true);
			}
			
			$value = $declField->getValue($instance);
			$this->serializeValue($value, $declField->getType());
		}
		
		$superClass = $instanceClass->getSuperClass();
		if ($this->serializationPolicy->shouldSerializeFields($superClass)) {
			$this->serializeImpl($instance, $superClass);
		}
	}
	
	private function serializeImpl($instance, Clazz $instanceClass) {
		assert(!is_null($instance));
		
		$customSerializer = SerializabilityUtilEx::hasCustomFieldSerializer($instanceClass);
		if (!is_null($customSerializer)) {
			// Use custom field serializer
			$customFieldSerializer = SerializabilityUtilEx::loadCustomFieldSerializer($customSerializer);
			if (is_null($customFieldSerializer)) {
				$this->serializeWithCustomSerializer($customSerializer, $instance, $instanceClass);
			}
			else {
				$customFieldSerializer->serializeInstance($this, $instance);
			}
		}
		else if ($instanceClass->isArray()) {
			$this->serializeArray($instanceClass, $instance);
		}
		else if ($instanceClass->isEnum()) {
			$this->writeInt($instance);
		}
		else {
			// Regular class instance
			$this->serializeClass($instance, $instanceClass);
		}
	}
	
	private function serializeWithCustomSerializer(Clazz $customSerializer, $instance, Clazz $instanceClass) {
		assert(!$instanceClass->isArray());
		
		foreach ($customSerializer->getMethods() as $method) {
			if ($method->getName() === 'serialize') {
				$method->invoke($this, $instance);
				return;
			}
		}
		throw new NoSuchMethodException('serialize');
	}
	
	private function writeHeader(SSSW_LengthConstrainedArray $stream) {
		$stream->addToken($this->getFlags());
		$stream->addToken($this->getVersion());
	}
	
	private function writePayload(SSSW_LengthConstrainedArray $stream) {
		for ($i=count($this->tokenList)-1; $i>=0; $i--) {
			$stream->addToken($this->tokenList[$i]);
		}
	}
	
	private function writeStringTable(SSSW_LengthConstrainedArray $stream) {
		$tableStream = new SSSW_LengthConstrainedArray();
		foreach ($this->getStringTable() as $s) {
			$tableStream->addToken(self::escapeString($s));
		}
		$stream->addToken((string) $tableStream);
	}

}
ServerSerializationStreamWriter::init();