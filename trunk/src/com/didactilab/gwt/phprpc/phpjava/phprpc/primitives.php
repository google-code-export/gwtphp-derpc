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
 * Date: 17 juil. 2011
 * Author: Mathieu LIGOCKI
 */

require_once PHPRPC_ROOT . 'classes.php';

abstract class JavaType {

	public static function clazz() {
		return Classes::classOf(get_called_class());
	}

}

/** @gwtname java.lang.Object */
class Object extends JavaType {
	const CLASSNAME = 'java.lang.Object';
	const SIGNATURE = 'L';
}

class JavaPrimitiveType extends JavaType {

	private $value;

	public function __construct($value) {
		$clazz = get_called_class();
		$this->value = $clazz::valueOf($value);
	}

	public function getValue() {
		return $this->value;
	}

	public function value() {
		return $this->value;
	}

	public function __toString() {
		return (string) $value;
	}
	
	public static function typeClass() {
		$type = get_called_class();
		return Classes::classOf($type::TYPE);
	}

}

/** @gwtname java.lang.Number */
interface Number {

}

/** @gwtname java.lang.Boolean */
class Boolean extends JavaPrimitiveType {

	const TYPE = 'boolean';
	const CLASSNAME = 'java.lang.Boolean';
	const SIGNATURE = 'Z';

	public static function valueOf($value) {
		if (is_string($value)) {
			return mb_strtolower($value) === 'true';
		}
		else {
			return (bool) $value;
		}
	}

}

/** @gwtname java.lang.Byte */
class Byte extends JavaPrimitiveType implements Number {

	const TYPE = 'byte';
	const CLASSNAME = 'java.lang.Byte';
	const SIGNATURE = 'B';

	const MIN_VALUE = -128;
	const MAX_VALUE = 127;

	public static function valueOf($value) {
		return ((int) $value) & 0xFF;
	}

	public static function parseByte($value) {
		return ((int) $value) & 0xFF;
	}

}

/** @gwtname java.lang.Character */
class Character extends JavaPrimitiveType {

	const TYPE = 'char';
	const CLASSNAME = 'java.lang.Character';
	const SIGNATURE = 'C';

	const MAX_RADIX = 36;
	const MIN_RADIX = 2;

	const MIN_CODE_POINT = 0x000000;
	const MAX_CODE_POINT = 0x10ffff;


	public static function valueOf(&$value) {
		return mb_substr($value, 0, 1);
	}

	public static function chr($code) {
		$val = chr(($code >> 24) & 0xFF) . chr(($code >> 16) & 0xFF) . chr(($code >> 8) & 0xFF) . chr(($code) & 0xFF);
		return mb_convert_encoding($val, mb_internal_encoding(), 'UTF-32');
	}

	public static function ord($char) {
		$val = mb_convert_encoding(mb_strimwidth($char, 0, 1), 'UTF-32');
		$ord = 0;
		for ($i=0; $i<strlen($val); $i++) {
			$ord = $ord << 8;
			$ord |= (ord($val[$i]) & 0xFF);
		}
		return $ord;
	}

	public static function isDigit($char) {
		return ctype_digit($char);
	}

}

/** @gwtname java.lang.Double */
class Double extends JavaPrimitiveType implements Number {

	const TYPE = 'double';
	const CLASSNAME = 'java.lang.Double';
	const SIGNATURE = 'D';

	public static function valueOf($value) {
		return (double) $value;
	}

	public static function parseDouble($value) {
		return (double) $value;
	}

}

/** @gwtname java.lang.Float */
class Float extends JavaPrimitiveType implements Number {

	const TYPE = 'float';
	const CLASSNAME = 'java.lang.Float';
	const SIGNATURE = 'F';

	public static function valueOf($value) {
		return (float) $value;
	}

	public static function parseFloat($value) {
		return floatval((string) $value);
	}

}

/** @gwtname java.lang.Integer */
class Integer extends JavaPrimitiveType implements Number {

	const TYPE = 'int';
	const CLASSNAME = 'java.lang.Integer';
	const SIGNATURE = 'I';

	const MIN_VALUE = -2147483648;
	const MAX_VALUE = 2147483647;

	public static function valueOf($value) {
		return ((int) $value) & 0xFFFFFFFF;
	}

	public static function parseInt($value) {
		return ((int) $value) & 0xFFFFFFFF;
	}

	public static function parseHex($value) {
		return hexdec($value);
	}

	public static function toString($value, $radix) {
		return base_convert($value, 10, $radix);
	}

}

/** @gwtname java.lang.Long */
class Long extends JavaPrimitiveType implements Number {

	const TYPE = 'long';
	const CLASSNAME = 'java.lang.Long';
	const SIGNATURE = 'J';

	const MIN_VALUE = 0x8000000000000000;
	const MAX_VALUE = 0x7fffffffffffffff;

	public static function valueOf($value) {
		return (float) $value;
	}

	public static function toLongString($value) {
		return sprintf('%.0f', $value);
	}

	public static function toString($value) {
		return sprintf('%.0f', $value);
	}

	public function __toString() {
		return self::toLongString($this->value());
	}

}

/** @gwtname java.lang.Short */
class Short extends JavaPrimitiveType implements Number {

	const TYPE = 'short';
	const CLASSNAME = 'java.lang.Short';
	const SIGNATURE = 'S';

	const MIN_SHORT = -32768;
	const MAX_SHORT = 32767;

	public static function valueOf($value) {
		return ((int) $value) & 0xFFFF;
	}

	public static function parseShort($value) {
		return ((int) $value) & 0xFFFF;
	}

}

/** @gwtname java.lang.Void */
class Void extends JavaType {

	const TYPE = 'void';
	const CLASSNAME = 'java.lang.Void';
	const SIGNATURE = 'V';

	const INSTANCE = null;
	
	public static function typeClass() {
		return Classes::classOf(self::TYPE);
	}

}

/** @gwtname java.lang.String */
class String extends JavaType {

	const CLASSNAME = 'java.lang.String';

	public static function valueOf($value) {
		return (string) $value;
	}

}

//////////////// INIT ////////////////////

Classes::register('Object', new JavaClazz('Object'));

Classes::register('boolean', new JavaPrimitiveClazz(Boolean::TYPE, Boolean::SIGNATURE));
Classes::register('byte', new JavaPrimitiveClazz(Byte::TYPE, Byte::SIGNATURE));
Classes::register('long', new JavaPrimitiveClazz(Long::TYPE, Long::SIGNATURE));
Classes::register('short', new JavaPrimitiveClazz(Short::TYPE, Short::SIGNATURE));
Classes::register('int', new JavaPrimitiveClazz(Integer::TYPE, Integer::SIGNATURE));
Classes::register('char', new JavaPrimitiveClazz(Character::TYPE, Character::SIGNATURE));
Classes::register('float', new JavaPrimitiveClazz(Float::TYPE, Float::SIGNATURE));
Classes::register('double', new JavaPrimitiveClazz(Double::TYPE, Double::SIGNATURE));
Classes::register('void', new JavaPrimitiveClazz(Void::TYPE, Void::SIGNATURE));

$objClass = Classes::classOf('Object');
Classes::register('Boolean', new JavaClazz('Boolean', $objClass));
Classes::register('Byte', new JavaClazz('Byte', $objClass));
Classes::register('Long', new JavaClazz('Long', $objClass));
Classes::register('Short', new JavaClazz('Short', $objClass));
Classes::register('Integer', new JavaClazz('Integer', $objClass));
Classes::register('Character', new JavaClazz('Character', $objClass));
Classes::register('String', new JavaClazz('String', $objClass));
Classes::register('Float', new JavaClazz('Float', $objClass));
Classes::register('Double', new JavaClazz('Double', $objClass));
Classes::register('Void', new JavaClazz('Void', $objClass));

// Aliases
Classes::registerAlias('string', String::clazz());
Classes::registerAlias('bool', Boolean::typeClass());

// Signatures
Classes::registerSignature(Boolean::SIGNATURE, Boolean::typeClass());
Classes::registerSignature(Byte::SIGNATURE, Byte::typeClass());
Classes::registerSignature(Character::SIGNATURE, Character::typeClass());
Classes::registerSignature(Double::SIGNATURE, Double::typeClass());
Classes::registerSignature(Float::SIGNATURE, Float::typeClass());
Classes::registerSignature(Integer::SIGNATURE, Integer::typeClass());
Classes::registerSignature(Long::SIGNATURE, Long::typeClass());
Classes::registerSignature(Short::SIGNATURE, Short::typeClass());

// Assignable From
Long::typeClass()->registerAssignableFrom(array(
	Double::typeClass(),
	Integer::typeClass(), 
	Character::typeClass(), 
	Short::typeClass(), 
	Byte::typeClass()
));
Integer::typeClass()->registerAssignableFrom(array(
	Character::typeClass(), 
	Short::typeClass(), 
	Byte::typeClass()
));
Character::typeClass()->registerAssignableFrom(array(
	Integer::typeClass(),
	Short::typeClass(),
	Byte::typeClass()
));
Short::typeClass()->registerAssignableFrom(array(
	Integer::typeClass(),
	Byte::typeClass()
));
Byte::typeClass()->registerAssignableFrom(array(
	Integer::typeClass()
));

Double::typeClass()->registerAssignableFrom(array(
	Float::typeClass()
));
Float::typeClass()->registerAssignableFrom(array(
	Double::typeClass()
));

Character::typeClass()->registerAssignableFrom(array(
	String::clazz()
));
String::clazz()->registerAssignableFrom(array(
	Character::typeClass()
));
