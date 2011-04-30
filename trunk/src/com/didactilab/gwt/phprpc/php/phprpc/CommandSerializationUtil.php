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
require_once PHPRPC_ROOT . 'ast.php';
require_once PHPRPC_ROOT . 'rpcphptools.php';

interface Accessor {
	
	function canMakeValueCommand();
	function get($instance, Field $f);
	function getTargetType();
	function makeValueCommand($value);
	function readNext(SerializationStreamReader $reader);
	function set($instance, Field $f, $value);
	function getDefaultValue();
	
}

abstract class BaseAccessor implements Accessor {
	
	public function canMakeValueCommand() {
		return true;
	}
	
	public function get($instance, Field $f) {
		return $f->getValue($instance);
	}
	
	public function set($instance, Field $f, $value) {
		$f->setValue($instance, $value);
	}
	
}

class BoolAccessor extends BaseAccessor {
	
	public function getTargetType() {
		return Boolean::typeClass();
	}
	
	public function makeValueCommand($value) {
		return new BooleanValueCommand($value);
	}
	
	public function readNext(SerializationStreamReader $reader) {
		return $reader->readBoolean();
	}
	
	public function getDefaultValue() {
		return false;
	}
	
}

class ByteAccessor extends BaseAccessor {
	
	public function getTargetType() {
		return Byte::typeClass();
	}
	
	public function makeValueCommand($value) {
		return new ByteValueCommand($value);
	}
	
	public function readNext(SerializationStreamReader $reader) {
		return $reader->readByte();
	}
	
	public function getDefaultValue() {
		return 0;
	}
	
}

class CharAccessor extends BaseAccessor {
	
	public function getTargetType() {
		return Character::typeClass();
	}
	
	public function makeValueCommand($value) {
		return new CharValueCommand($value);
	}
	
	public function readNext(SerializationStreamReader $reader) {
		return $reader->readChar();
	}
	
	public function getDefaultValue() {
		return '\0';
	}
	
}

class DoubleAccessor extends BaseAccessor {
	
	public function getTargetType() {
		return Double::typeClass();
	}
	
	public function makeValueCommand($value) {
		return new DoubleValueCommand($value);
	}
	
	public function readNext(SerializationStreamReader $reader) {
		return $reader->readDouble();
	}
	
	public function getDefaultValue() {
		return 0.0;
	}
	
}

class FloatAccessor extends BaseAccessor {
	
	public function getTargetType() {
		return Float::typeClass();
	}
	
	public function makeValueCommand($value) {
		return new FloatValueCommand($value);
	}
	
	public function readNext(SerializationStreamReader $reader) {
		return $reader->readFloat();
	}
	
	public function getDefaultValue() {
		return 0.0;
	}

}

class IntAccessor extends BaseAccessor {
	
	public function getTargetType() {
		return Integer::typeClass();
	}
	
	public function makeValueCommand($value) {
		return new IntValueCommand($value);
	}
	
	public function readNext(SerializationStreamReader $reader) {
		return $reader->readInt();
	}
	
	public function getDefaultValue() {
		return 0;
	}
	
}

class LongAccessor extends BaseAccessor {
	
	public function getTargetType() {
		return Long::typeClass();
	}
	
	public function makeValueCommand($value) {
		return new LongValueCommand($value);
	}
	
	public function readNext(SerializationStreamReader $reader) {
		return $reader->readLong();
	}
	
	public function getDefaultValue() {
		return 0;
	}
	
}

class ObjectAccessor extends BaseAccessor {
	
	public function canMakeValueCommand() {
		return false;
	}
	
	public function getTargetType() {
		return Object::clazz();
	}
	
	public function makeValueCommand($value) {
		throw new RuntimeException('Cannot call makeValueCommand for Objects');
	}
	
	public function readNext(SerializationStreamReader $reader) {
		return $reader->readObject();
	}
	
	public function getDefaultValue() {
		return null;
	}
	
}

class ShortAccessor extends BaseAccessor {
	
	public function getTargetType() {
		return Short::typeClass();
	}
	
	public function makeValueCommand($value) {
		return new ShortValueCommand($value);
	}
	
	public function readNext(SerializationStreamReader $reader) {
		return $reader->readShort();
	}
	
	public function getDefaultValue() {
		return 0;
	}
	
}

class StringAccessor extends BaseAccessor {
	
	public function getTargetType() {
		return String::clazz();
	}
	
	public function makeValueCommand($value) {
		return new StringValueCommand($value);
	}
	
	public function readNext(SerializationStreamReader $reader) {
		return $reader->readString();
	}
	
	public function getDefaultValue() {
		return null;
	}
	
}

class Accessors {
	private static $ACCESSORS = array();

	private static function add(Accessor $accessor) {
		self::$ACCESSORS[Hasher::hashObject($accessor->getTargetType())] = $accessor;
	}
	
	public static function init() {
		self::add(new BoolAccessor());
		self::add(new ByteAccessor());
		self::add(new CharAccessor());
		self::add(new DoubleAccessor());
		self::add(new FloatAccessor());
		self::add(new IntAccessor());
		self::add(new LongAccessor());
		self::add(new ObjectAccessor());
		self::add(new ShortAccessor());
		self::add(new StringAccessor());
	}
	
	public static function get(Clazz $clazz) {
		$hash = Hasher::hashObject($clazz);
		if (isset(self::$ACCESSORS[$hash]))
			return self::$ACCESSORS[$hash];
		else
			return self::$ACCESSORS[Hasher::hashObject(Object::clazz())];
	}
	
}

Accessors::init();