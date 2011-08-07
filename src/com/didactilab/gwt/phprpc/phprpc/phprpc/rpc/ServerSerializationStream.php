<?php

require_once PHPRPC_ROOT . 'classes.php';
require_once PHPRPC_ROOT . 'collections.php';
require_once PHPRPC_ROOT . 'SerializationStream.php';

abstract class AbstractSerializationStream {
	
	const DEFAULT_FLAGS = 0;
	const RPC_SEPARATOR_CHAR = '|';
	const SERIALIZATION_STREAM_VERSION = 7;
	const SERIALIZATION_STREAM_MIN_VERSION = 5;
	const FLAG_ELIDE_TYPE_NAMES = 0x01;
	const FLAG_RPC_TOKEN_INCLUDED = 0x02;
	
	const VALID_FLAGS_MASK = 0x03;
	
	private $flags = self::DEFAULT_FLAGS;
	private $version = self::SERIALIZATION_STREAM_VERSION;
	
	public final function addFlags($flags) {
		$this->flags |= $flags;
	}
	
	public final function areFlagsValid() {
		return ((($this->flags | self::VALID_FLAGS_MASK) ^ self::VALID_FLAGS_MASK) == 0);
	}
	
	public final function getFlags() {
		return $this->flags;
	}
	
	public final function getVersion() {
		return $this->version;
	}
	
	public final function hasFlags($flags) {
		return ($this->getFlags() & $flags) == $flags;
	}
	
	public final function setFlags($flags) {
		$this->flags = $flags;
	}
	
	protected final function setVersion($version) {
		$this->version = $version;
	}
	
}

abstract class AbstractSerializationStreamReader extends AbstractSerializationStream implements SerializationStreamReader {
	const TWO_PWR_15_DBL = 0x8000;				// float 32768
	const TWO_PWR_16_DBL = 0x10000;				// float 65536
	const TWO_PWR_22_DBL = 0x400000;			// float 4194304
	const TWO_PWR_31_DBL = 2147483648;			// self::TWO_PWR_16_DBL * self::TWO_PWR_15_DBL;
	const TWO_PWR_32_DBL = 4294967296;			// self::TWO_PWR_16_DBL * self::TWO_PWR_16_DBL;
	const TWO_PWR_44_DBL = 17592186044416;		// self::TWO_PWR_22_DBL * self::TWO_PWR_22_DBL;
	const TWO_PWR_63_DBL = 9223372036854776000;	// self::TWO_PWR_32_DBL * self::TWO_PWR_31_DBL;
	
	public static function fromDoubles($lowDouble, $highDouble) {
		$high = self::fromDouble($highDouble);
		$low = self::fromDouble($lowDouble);
		return Long::valueOf($high + $low);
	}
	
	private static function fromDouble($value) {
		if (is_nan($value)) {
			return 0;
		}
		if ($value < -self::TWO_PWR_63_DBL) {
			return Long::MIN_VALUE;
		}
		if ($value >= self::TWO_PWR_63_DBL) {
			return Long::MAX_VALUE;
		}
		
		$negative = false;
		if ($value < 0) {
			$negative = true;
			$value = -$value;
		}
		$a2 = 0;
		if ($value >= self::TWO_PWR_44_DBL) {
			$a2 = (int) ($value  / self::TWO_PWR_44_DBL);
			$value -= $a2 * self::TWO_PWR_44_DBL;
		}
		$a1 = 0;
		if ($value >= self::TWO_PWR_22_DBL) {
			$a1 = (int) ($value  / self::TWO_PWR_22_DBL);
			$value -= $a1 * self::TWO_PWR_22_DBL;
		}
		$a0 = (int) $value;
		
		$result = ($a2 << 44) | ($a1 << 22) | $a0;
		if ($negative) {
			$result = -$result;
		}
		return $result;
	}
	
	private $seenArray = array();
	
	public function prepareToRead($encoded) {
		$this->seenArray = array();
		$this->setVersion($this->readInt());
		$this->setFlags($this->readInt());
	}
	
	public final function readObject() {
		$token = $this->readInt();
		if ($token < 0) {
			return $this->seenArray[-($token + 1)];
		}
		
		$typeSignature = $this->getString($token);
		if (is_null($typeSignature)) {
			return null;
		}
		
		return $this->deserialize($typeSignature);
	}
	
	protected abstract function deserialize($typeSignature);
	
	protected abstract function getString($index);
	
	protected final function rememberDecodedObject($index, $o) {
		$this->seenArray[$index - 1] = $o;
	}
	
	protected final function reserveDecodedObjectIndex() {
		$this->seenArray[] = null;
		return count($this->seenArray);
	}
}

abstract class AbstractSerializationStreamWriter extends AbstractSerializationStream implements SerializationStreamWriter {
	const TWO_PWR_16_DBL = 0x1000; 			// float
	const TWO_PWR_32_DBL = 16777216;		// self::TWO_PWR_16_DBL * self::TWO_PWR_16_DBL;
	
	public static function getAsDoubleArray($value) {
		$lowBits = (int) ($value & 0xFFFFFFFF);
		$highBits = (int) ($value >> 32);
		return self::makeLongComponents($lowBits, $highBits);
	}
	
	protected static function makeLongComponents($lowBits, $highBits) {
		$high = $highBits * self::TWO_PWR_32_DBL;
		$low = $lowBits;
		if ($lowBits < 0) {
			$low += self::TWO_PWR_32_DBL;
		}
		return array($low, $high);
	}
	
	private $objectCount;
	private $objectMap;
	private $stringMap = array();
	private $stringTable = array();
	
	protected function __construct() {
		$this->objectMap = new IdentityHashMap();
	}
	
	public function prepareToWrite() {
		$this->objectCount = 0;
		$this->objectMap->clear();
		$this->stringMap = array();
		$this->stringTable = array();
	}
	
	public function writeBoolean($fieldValue) {
		$this->append($fieldValue ? '1' : '0');
	}
	
	public function writeByte($fieldValue) {
		$this->append(String::valueOf($fieldValue));
	}
	
	public function writeChar($fieldValue) {
		// just use an int, it's more foolproof
		$this->append(String::valueOf(Character::ord($fieldValue)));
	}
	
	public function writeDouble($fieldValue) {
		$this->append(String::valueOf($fieldValue));
	}
	
	public function writeFloat($fieldValue) {
		$this->writeDouble($fieldValue);
	}
	
	public function writeInt($fieldValue) {
		$this->append(String::valueOf($fieldValue));
	}
	
	public function writeObject($instance) {
		if (is_null($instance)) {
			$this->writeString(null);
			return;
		}
		
		$objIndex = $this->getIndexForObject($instance);
		if ($objIndex >= 0) {
			// We've already encoded this object, make a backref
			// Transform 0-based to negative 1-based
			$this->writeInt(-($objIndex + 1));
			return;
		}
		
		$this->saveIndexForObject($instance);
		
		// Serialize the type signature
		$typeSignature = $this->getObjectTypeSignature($instance);
		$this->writeString($typeSignature);
		// Now serialize the rest of the object
		$this->serialize($instance, $typeSignature);
	}
	
	public function writeTypedObject($instance, Clazz $instanceClass) {
		if (is_null($instance)) {
			$this->writeString(null);
			return;
		}
	
		$objIndex = $this->getIndexForObject($instance);
		if ($objIndex >= 0) {
			// We've already encoded this object, make a backref
			// Transform 0-based to negative 1-based
			$this->writeInt(-($objIndex + 1));
			return;
		}
	
		$this->saveIndexForObject($instance);
	
		// Serialize the type signature
		$typeSignature = $this->getObjectTypeSignature($instance, $instanceClass);
		$this->writeString($typeSignature);
		// Now serialize the rest of the object
		$this->serialize($instance, $typeSignature, $instanceClass);
	}
	
	/*public function writeEnum2(SerializedEnum $instance) {
		$objIndex = $this->getIndexForObject($instance);
		if ($objIndex >= 0) {
			// We've already encoded this object, make a backref
			// Transform 0-based to negative 1-based
			$this->writeInt(-($objIndex + 1));
			return;
		}
		
		$this->saveIndexForObject($instance);
		
		// Serialize the type signature
		$typeSignature = $this->getObjectTypeSignature($instance);
		$this->writeString($typeSignature);
		// Now serialize the rest of the object
		$this->serialize($instance, $typeSignature);
	}*/
	
	public function writeEnum(Clazz $clazz, $instance) {
		$objIndex = $this->getIndexForObject($instance);
		if ($objIndex >= 0) {
			// We've already encoded this object, make a backref
			// Transform 0-based to negative 1-based
			$this->writeInt(-($objIndex + 1));
			return;
		}
	
		$this->saveIndexForObject($instance);
	
		// Serialize the type signature
		$typeSignature = $this->getObjectTypeSignature($instance, $clazz);
		$this->writeString($typeSignature);
		// Now serialize the rest of the object
		$this->serialize($instance, $typeSignature, $clazz);
	}
	
	public function writeShort($fieldValue) {
		$this->append(String::valueOf($fieldValue));
	}
	
	public function writeString($value) {
		$this->writeInt($this->addString($value));
	}
	
	public function writeValue(Clazz $clazz, $instance) {
		if ($clazz === Boolean::typeClass()) {
			$this->writeObject(new Boolean($instance));
		}
		else if ($clazz === Byte::typeClass()) {
			$this->writeObject(new Byte($instance));
		}
		else if ($clazz === Character::typeClass()) {
			$this->writeObject(new Character($instance));
		}
		else if ($clazz === Double::typeClass()) {
			$this->writeObject(new Double($instance));
		}
		else if ($clazz === Float::typeClass()) {
			$this->writeObject(new Float($instance));
		}
		else if ($clazz === Integer::typeClass()) {
			$this->writeObject(new Integer($instance));
		}
		else if ($clazz === Long::typeClass()) {
			$this->writeObject(new Long($instance));
		}
		else if ($clazz === Short::typeClass()) {
			$this->writeObject(new Short($instance));
		}
		else if ($clazz === String::clazz()) {
			$this->writeString($instance);
		}
		else {
			if ($clazz->isEnum()) {
				$this->writeEnum($clazz, $instance);
			}
			else {
				$this->writeObject($instance);
			}
		}
	}
	
	protected function addString($string) {
		if (is_null($string)) {
			return 0;
		}
		if (isset($this->stringMap[$string])) {
			return $this->stringMap[$string];
		}
		$this->stringTable[] = $string;
		// index is 1-based
		$index = count($this->stringTable);
		$this->stringMap[$string] = $index;
		return $index;
	}
	
	protected abstract function append($token);
	
	protected function getIndexForObject($instance) {
		return $this->objectMap->containsKey($instance) ? $this->objectMap->get($instance) : -1;
	}
	
	protected abstract function getObjectTypeSignature($instance, Clazz $instanceClass = null);
	
	protected function getStringTable() {
		return $this->stringTable;
	}
	
	protected function saveIndexForObject($instance) {
		$this->objectMap->put($instance, $this->objectCount++);
	}
	
	protected abstract function serialize($instance, $typeSignature, Clazz $instanceClass = null);
}