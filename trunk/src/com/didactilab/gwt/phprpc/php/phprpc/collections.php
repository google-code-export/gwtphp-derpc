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

interface HasHashCode {
	function hashCode();
}

/** @gwtname java.util.Collection */
interface Collection extends Traversable, IteratorAggregate, Countable {
	
	function add($e);
	function addArray(array $array);
	function addAll(Collection $c);
	function removeElement($e);
	function removeAll(Collection $c);
	function removeArray(array $array);
	function retainAll(Collection $c);
	function retainArray(array $array);
	function clear();
	
	function contains($o);
	function containsAll(Collection $c);
	function containsArray(array $array);
	function isEmpty();
	function size();
	
	function &toArray();
}

/** @gwtname java.util.List */
interface GenericList extends Collection, ArrayAccess {
	function insert($index, $e);
	function insertAll($index, Collection $c);
	function insertArray($index, array $array);
	
	function remove($index);
	
	function get($index);
	function set($index, $value);
	function indexOf($o);
}

/** @gwtname java.util.Set */
interface Set extends Collection {
}

/** @gwtname java.util.Map */
interface Map extends IteratorAggregate, ArrayAccess, Countable {
	function clear();
	function containsKey($key);
	function containsValue($value);
	function isEmpty();
	
	function get($key);
	function put($key, $value);
	function putAll(Map $m);
	function putArray(array $a);
	function remove($key);
	function size();
	
	function values();
	function keys();
}

abstract class AbstractCollection implements Collection {
	
	public function addArray(array $array) {
		foreach ($array as &$element) {
			$this->add($element);
		}
	}
	
	public function addAll(Collection $c) {
		foreach ($c as $element) {
			$this->add($element);
		}
	}
	
	public function removeAll(Collection $c) {
		foreach ($c as $element) {
			$this->removeElement($element);
		}
	}
	
	public function removeArray(array $array) {
		foreach ($array as &$element) {
			$this->removeElement($element);
		}
	}
	
	public function retainAll(Collection $c) {
		$toRemove = array();
		foreach ($this as $element) {
			if (!$c->contains($element)) {
				$toRemove[] = $element;
			}
		}
		$this->removeArray($toRemove);
	}
	
	public function retainArray(array $array) {
		$toRemove = array();
		foreach ($this as $element) {
			if (!in_array($element, $array, true)) {
				$toRemove[] = $element;
			}
		}
		$this->removeArray($toRemove);
	}
	
	public function containsAll(Collection $c) {
		foreach ($c as $element) {
			if (!$this->contains($element)) {
				return false;
			}
		}
		return true;
	}
	
	public function containsArray(array $array) {
		foreach ($array as $element) {
			if (!$this->contains($element)) {
				return false;
			}
		}
		return true;
	}
	
	public function isEmpty() {
		return $this->size() == 0;
	}
	
	public function size() {
		return $this->count();
	}
	
}

class ListIterator implements Iterator {
	private $array;
	
	public function __construct(&$array) {
		$this->array = &$array;
	}
	
	public function current() {
		return current($this->array);
	}

	public function next() {
		return next($this->array);
	}

	public function key() {
		return key($this->array);
	}

	public function valid() {
		return key($this->array) !== null;
	}

	public function rewind() {
		return reset($this->array);
	}
}

abstract class AbstractList extends AbstractCollection implements GenericList {
	
	public function insertAll($index, Collection $c) {
		foreach ($c as $e) {
			$this->insert($index++, $e);
		}
	}
	
	public function insertArray($index, array $array) {
		foreach ($array as $e) {
			$this->insert($index++, $e);
		}
	}
	
	public function getIterator() {
		return new ListIterator($this->toArray());
	}
	
}

/** @gwtname java.util.ArrayList */
class ArrayList extends AbstractList {
	private $array;
	
	public function __construct($from = null) {
		if (is_null($from)) {
			$this->array = array();
		}
		else {
			if (is_array($from)) {
				$this->array = $from;
			}
			else if (is_object($from) && ($from instanceOf Collection)) {
				$this->array = $from->toArray();
			}
			else {
				throw Exception('the constructor parameter is unknown type');
			}
		}
	}
	
	private function rebuild() {
		$this->array = array_values($this->array);
	}
	
	public function add($e) {
		$this->array[] = $e;
	}
	
	public function removeElement($e) {
		$key = array_search($e, $this->array, true);
		if ($key !== false) {
			unset($this->array[$key]);
			$this->rebuild();
		}
	}
	
	public function remove($index) {
		if (isset($this->array[$index])) {
			unset($this->array[$index]);
			$this->rebuild();
		}
	}

	public function clear() {
		$this->array = array();
	}
	
	public function contains($o) {
		return array_search($o, $this->array, true) !== false;
	}
	
	public function isEmpty() {
		return empty($this->array);
	}
	
	public function count() {
		return count($this->array);
	}
	
	public function &toArray() {
		return $this->array;
	}
	
	public function insert($index, $e) {
		array_splice($this->array, $index, 0, $e);
		$this->rebuild();
	}
	
	public function indexOf($o) {
		$index = array_search($o, $this->array, true);
		if ($index === FALSE) {
			return -1;
		}
		else {
			return $index;
		}
	}
	
	public function get($index) {
		return $this->array[$index];
	}
	
	public function set($index, $value) {
		if (is_null($index)) {
			$this->array[] = $value;
		}
		else {
			if ($index >= 0 && $index < count($this->array)) {
				$this->array[$index] = $value;
			}
			else {
				throw new OutOfRangeException('the list has no index ' . $index);
			}
		}
	}
	
	public function offsetExists($offset) {
		return isset($this->array[$offset]);
	}
	
	public function offsetSet($offset, $value) {
		$this->set($offset, $value);
	}
	
	public function offsetGet($offset) {
		return $this->array[$offset];
	}
	
	public function offsetUnset($offset) {
		$this->remove($offset);
	}

}

/** @gwtname java.util.Vector */
class Vector extends ArrayList {
}

class SetIterator implements Iterator {
	private $array;
	private $pos = 0;
	
	public function __construct(&$array) {
		$this->array = &$array;
	}
	
	public function current() {
		return current($this->array);
	}

	public function next() {
		$pos++;
		return next($this->array);
	}

	public function key() {
		return $pos;
	}

	public function valid() {
		return key($this->array) !== null;
	}

	public function rewind() {
		$pos = 0;
		return reset($this->array);
	}
}

/** @gwtname java.util.HashSet */
class HashSet extends AbstractCollection {
	private $array = array();
	
	public function __construct($from = null) {
		if (!is_null($from)) {
			if (is_array($from)) {
				$this->addArray($from);
			}
			else if (is_object($from) && $from instanceof Collection) {
				$this->addAll($from);
			}
			else {
				throw Exception('the constructor parameter is unknown type');
			}
		}
	}
	
	public function add($e) {
		$hash = Hasher::hash($e);
		if (isset($this->array[$hash])) {
			return false;
		}
		else {
			$this->array[$hash] = $e;
			return true;
		}
	}
	
	public function removeElement($e) {
		$hash = Hasher::hash($e);
		if (isset($this->array[$hash])) {
			unset($this->array[$hash]);
		}
	}

	public function clear() {
		$this->array = array();
	}
	
	public function contains($o) {
		$hash = Hasher::hash($o);
		return isset($this->array[$hash]);
	}
	
	public function isEmpty() {
		return empty($this->array);
	}
	
	public function count() {
		return count($this->array);
	}
	
	public function &toArray() {
		return array_values($this->array);
	}
	
	public function getIterator() {
		return new SetIterator($this->array);
	}

}

/** @gwtname java.util.TreeSet */
class TreeSet extends HashSet {
}

class MapIterator implements Iterator {
	private $keys;
	private $values;
	
	public function __construct(&$keys, &$values) {
		$this->keys = &$keys;
		$this->values = &$values;
	}
	
	public function current() {
		return current($this->values);
	}

	public function next() {
		next($this->keys);
		return next($this->values);
	}

	public function key() {
		return current($this->keys);
	}

	public function valid() {
		return key($this->values) !== null;
	}

	public function rewind() {
		reset($this->keys);
		return reset($this->values);
	}
}

abstract class AbstractMap implements Map {
	public function putAll(Map $m) {
		foreach ($m as $key => $value) {
			$this->put($key, $value);
		}
	}
	
	public function putArray(array $a) {
		foreach ($a as $key => $value) {
			$this->put($key, $value);
		}
	}
	
	public function size() {
		return $this->count();
	}
}

/** @gwtname java.util.HashMap */
class HashMap extends AbstractMap {
	private $values = array();
	private $keys = array();
	private $hashFunc;
	
	public function __construct($from = null, $hashFunc = null) {
		if (is_null($hashFunc)) {
			$this->hashFunc = 'hash';
		}
		else {
			$this->hashFunc = $hashFunc;
		}
		
		if (!is_null($from)) {
			if (is_array($from)) {
				$this->putArray($from);
			}
			else if (is_object($from) && $from instanceof Map) {
				$this->putAll($from);
			}
			else {
				throw Exception('the constructor parameter is unknown type');
			}
		}
	}
	
	private function hash($x) {
		$func = $this->hashFunc;
		return Hasher::$func($x);
	}
	
	public function clear() {
		$this->values = array();
		$this->keys = array();
	}
	
	public function containsKey($key) {
		$hash = $this->hash($key);
		return isset($this->keys[$hash]);
	}
	
	public function containsValue($value) {
		return array_search($value, $this->values, true) !== false;
	}
	
	public function isEmpty() {
		return empty($this->keys);
	}
	
	public function get($key) {
		$hash = $this->hash($key);
		if (isset($this->values[$hash])) {
			return $this->values[$hash];
		}
		else {
			return null;
		}
	}
	
	public function put($key, $value) {
		$hash = $this->hash($key);
		if (isset($this->values[$hash])) {
			$old = $this->values[$hash];
		}
		else {
			$old = null;
		}
		$this->keys[$hash] = $key;
		$this->values[$hash] = $value;
		return $old;
	}
	
	public function remove($key) {
		$hash = $this->hash($key);
		if (isset($this->values[$hash])) {
			$old = $this->values[$hash];
			unset($this->values[$hash]);
			unset($this->keys[$hash]);
			return $old;
		}
	}
	
	public function values() {
		return array_values($this->values);
	}
	
	public function keys() {
		return array_values($this->keys);
	}
	
	public function count() {
		return count($this->keys);
	}
	
	public function offsetExists($offset) {
		return $this->containsKey($offset);
	}
	
	public function offsetSet($offset, $value) {
		$this->put($offset, $value);
	}
	
	public function offsetGet($offset) {
		return $this->get($offset);
	}
	
	public function offsetUnset($offset) {
		$this->remove($offset);
	}
	
	public function getIterator() {
		return new MapIterator($this->keys, $this->values);
	}
}

/** @gwtname java.util.TreeMap */
class TreeMap extends HashMap {}

/** @gwtname java.util.IdentityMap */
class IdentityMap extends HashMap {}

class ObjectMap extends HashMap {
	public function __construct($from = null) {
		parent::__construct($from, 'hashObject');
	}
}

class HybridMap extends HashMap {
}

class Hasher {
	public function hash($x) {
		if (is_array($x)) {
			return self::hashArray($x);
		}
		else if (is_object($x)) {
			return self::hashObject($x);
		}
		else {
			return self::hashString($x);
		}
	}
	
	public function hashArray($x) {
		return md5(serialize($x));
	}
	
	public function hashObject($x) {
		if ($x instanceof HasHashCode) {
			return $x->hashCode();
		}
		else {
			return spl_object_hash($x);
		}
	}
	
	public function hashString($x) {
		return md5($x);
	}
}

interface SimpleIterator {
	function hasNext();
	function next();
}

class ArraySimpleIterator implements SimpleIterator {
	
	private $array;
	private $count;
	private $cursor = 0;
	
	public function __construct(&$array) {
		$this->array = &$array;
		$this->count = count($array);
	}
	
	public function hasNext() {
		return $this->cursor != $this->count;
	}
	
	public function next() {
		$value = &$this->array[$this->cursor];
		$this->cursor++;
		return $value;
	}
	
}

class Collections {
	
	public static function sort(GenericList $list, $func = '') {
		if (empty($func)) {
			return sort($list->toArray());
		}
		else {
			return usort($list->toArray(), $func);
		}
	}
	
}

/** @gwtname java.util.Date */
class Date {
	private $time;
	
	public function __construct($timestamp = 0) {
		$this->time = (float) $timestamp;
	}
	
	public function toMicroTime() {
		return $this->time / 1000;
	}
	
	public function toTime() {
		return (int) ($this->time / 1000);
	}
	
	public function getValue() {
		return $this->time;
	}
	
	public function __toString() {
		return date('YY "/" MM "/" DD', $this->toTime());
	}
	
	public static function create($microtime) {
		return new Date(((float) $microtime) * 1000);
	}

	public static function time($timestamp) {
		return (((float) $timestamp) / 1000);
	}
}

/** @gwtname com.google.gwt.user.client.rpc.core.java.util.Date_CustomFieldSerializer */
final class Date_CustomFieldSerializer {
	
	public static function instanciate(SerializationStreamReader $streamReader) {
		return new Date($streamReader->readLong());
	}
	
	public static function deserialize(SerializationStreamReader $streamReader, $instance) {
		// Nothing
	}
	
	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		$streamWriter->writeLong($instance->getValue());
	}
	
}

/** @gwtname com.google.gwt.user.client.rpc.core.java.util.ArrayList_CustomFieldSerializer */
final class ArrayList_CustomFieldSerializer {
	
	public static function transform(SerializationStreamReader $streamReader) {
		return Collection_CustomFieldSerializerBase::transform($streamReader);
	}
	
	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		Collection_CustomFieldSerializerBase::serialize($streamWriter, $instance);
	}
	
}

/** @gwtname com.google.gwt.user.client.rpc.core.java.util.Vector_CustomFieldSerializer */
final class Vector_CustomFieldSerializer {
	
	public static function transform(SerializationStreamReader $streamReader) {
		return Collection_CustomFieldSerializerBase::transform($streamReader);
	}
	
	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		Collection_CustomFieldSerializerBase::serialize($streamWriter, $instance);
	}
	
}

/** @gwtname com.google.gwt.user.client.rpc.core.java.util.HashSet_CustomFieldSerializer */
final class HashSet_CustomFieldSerializer {
	
	public static function transform(SerializationStreamReader $streamReader) {
		return Collection_CustomFieldSerializerBase::transform($streamReader);
	}
	
	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		Collection_CustomFieldSerializerBase::serialize($streamWriter, $instance);
	}
	
}

/** @gwtname com.google.gwt.user.client.rpc.core.java.util.TreeSet_CustomFieldSerializer */
final class TreeSet_CustomFieldSerializer {
	
	public static function transform(SerializationStreamReader $streamReader) {
		return Collection_CustomFieldSerializerBase::transform($streamReader);
	}
	
	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		Collection_CustomFieldSerializerBase::serialize($streamWriter, $instance);
	}
	
}

/** @gwtname com.google.gwt.user.client.rpc.core.java.util.HashMap_CustomFieldSerializer */
final class HashMap_CustomFieldSerializer {
	
	public static function transform(SerializationStreamReader $streamReader) {
		return Map_CustomFieldSerializerBase::transform($streamReader);
	}
	
	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		Map_CustomFieldSerializerBase::serialize($streamWriter, $instance);
	}
	
}

/** @gwtname com.google.gwt.user.client.rpc.core.java.util.TreeMap_CustomFieldSerializer */
final class TreeMap_CustomFieldSerializer {
	
	public static function transform(SerializationStreamReader $streamReader) {
		return Map_CustomFieldSerializerBase::transform($streamReader);
	}
	
	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		Map_CustomFieldSerializerBase::serialize($streamWriter, $instance);
	}
	
}

/** @gwtname com.google.gwt.user.client.rpc.core.java.util.IdentityMap_CustomFieldSerializer */
final class IdentityMap_CustomFieldSerializer {
	
	public static function transform(SerializationStreamReader $streamReader) {
		return Map_CustomFieldSerializerBase::transform($streamReader);
	}
	
	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		Map_CustomFieldSerializerBase::serialize($streamWriter, $instance);
	}
	
}

final class Collection_CustomFieldSerializerBase {
	
	public static function transform(SerializationStreamReader $streamReader) {
		$size = $streamReader->readInt();
		$result = array();
		for ($i=0; $i<$size; $i++) {
			$result[] = $streamReader->readObject();
		}
		return $result;
	}
	
	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		$array = $instance->toArray();
		$size = count($array);
		$streamWriter->writeInt($size);
		foreach ($array as $element) {
			$streamWriter->writeObject($element);
		}
	}
	
}

final class Map_CustomFieldSerializerBase {
	
	public static function transform(SerializationStreamReader $streamReader) {
		$size = $streamReader->readInt();
		$result = array();
		for ($i=0; $i<$size; $i++) {
			$obj = $streamReader->readObject();
			$value = $streamReader->readObject();
			$result[$obj] = $value;
		}
		return $result;
	}
	
	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		$streamWriter->writeInt($instance->count());
		foreach ($instance->keys() as $key) {
			$streamWriter->writeObject(Classes::toObject($key));
			$streamWriter->writeObject(Classes::toObject($instance->get($key)));
		}
		
		/*foreach ($instance as $key => $value) {
			$streamWriter->writeObject($key);
			$streamWriter->writeObject($value);
		}*/
	}
	
}

Classes::registerAlias('List', Classes::classOf(GenericList));