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
interface Collection extends Traversable, IteratorAggregate, Countable, HasHashCode {
	
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

/** @gwtname java.util.Queue */
interface Queue {
	function offer($e);
	function removeHead();
	function poll();
	function element();
	function peek();
}

/** @gwtname java.util.Deque */
interface Deque {
	function addFirst($e);
	function addLast($e);
	function offerFirst($e);
	function offerLast($e);
	function removeFirst();
	function removeLast();
	function pollFirst();
	function pollLast();
	function getFirst();
	function getLast();
	function peekFirst();
	function peekLast();
	function removeFirstOccurence($o);
	function removeLastOccurence($o);
	function push($e);
	function pop();
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
interface Map extends IteratorAggregate, ArrayAccess, Countable, HasHashCode {
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
	
	public function hashCode() {
		$hashCode = '';
		foreach ($this as $obj) {
			$hashCode .= '31' . Hasher::hash($obj);
		}
		return md5($hashCode);
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
		next($this->array);
	}

	public function key() {
		return key($this->array);
	}

	public function valid() {
		return key($this->array) !== null;
	}

	public function rewind() {
		reset($this->array);
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

/** @gwtname java.util.LinkedList */
class LinkedList extends AbstractList implements Deque {
	
	private $header;
	private $size;
	
	public function __construct($from = null) {
		$this->header = new LinkedListEntry(null, null, null);
		$this->header->next = $this->header->previous = $this->header;
		if (!is_null($from)) {
			if (is_array($from)) {
				$this->addArray($from);
			}
			else if ($from instanceof Collection) {
				$this->addAll($from);
			}
		}
	}
	
	public function getFirst() {
		if ($this->size == 0) {
			throw new NoSuchElementException();
		}
		return $this->header->next->element;
	}
	
	public function getLast() {
		if ($this->size == 0) {
			throw new NoSuchElementException();
		}
		return $this->header->previous->element;
	}
	
	public function removeFirst() {
		return $this->removeEntry($this->header->next);
	}
	
	private function removeEntry($e) {
		if ($e === $this->header) {
			throw new NoSuchElementException();
		}
		$result = $e->element;
		$e->previous->next = $e->next;
		$e->next->previous = $e->previous;
		$e->next = $e->previous = null;
		$e->element = null;
		$this->size--;
		return $result;
	}
	
	public function removeLast() {
		return $this->removeEntry($this->header->previous);
	}
	
	public function addFirst($e) {
		$this->addBefore($e, $this->header->next);
	}
	
	public function addLast($e) {
		$this->addBefore($e, $this->header);
	}
	
	public function contains($o) {
		return $this->indexOf($o) != -1;
	}
	
	public function count() {
		return $this->size;
	}
	
	public function add($e) {
		$this->addBefore($e, $this->header);
		return true;
	}
	
	public function removeElement($o) {
		if (is_null($o)) {
			for ($e = $this->header->next; $e !== $header; $e = $e->next) {
				if (is_null($e->element)) {
					$this->removeEntry($e);
					return true;
				}
			}
		}
		else {
			for ($e = $this->header->next; $e !== $header; $e = $e->next) {
				if ($o === $e->element) {
					$this->removeEntry($e);
					return true;
				}
			}
		}
		return false;
	}
	
	public function addAll(Collection $c) {
		return $this->insertAll($this->size, $c);
	}
	
	public function insertAll($index, Collection $c) {
		if ($index < 0 || $index > $this->size) {
			throw new IndexOutOfBoundsException("Index: $index, Size: $this->size");
		}
		
		$a = $c->toArray();
		$numNew = count($a);
		if ($numNew == 0) {
			return false;
		}
		$successor = ($index == $this->size ? $this->header : $this->entry($index));
		$predecessor = $successor->previous;
		for ($i=0; $i<$numNew; $i++) {
			$e = new LinkedListEntry($a[$i], $successor, $predecessor);
			$predecessor->next = $e;
			$predecessor = $e;
		}
		$successor->previous = $predecessor;
		
		$this->size += $numNew;
		return true;
	}
	
	public function clear() {
		$e = $this->next;
		while ($e != $this->header) {
			$next = $e->next;
			$e->next = $e->previous = null;
			$e->element = null;
			$e = $next;
		}
		$this->header->next = $this->header->previous = $this->header;
		$this->size = 0;
	}
	
	public function get($index) {
		return $this->entry($index)->element;
	}
	
	public function set($index, $element) {
		$e = $this->entry($index);
		$oldVal = $e->element;
		$e->element = $element;
		return $oldVal;
	}
	
	public function insert($index, $element) {
		$this->addBefore($element, ($index == $this->size ? $this->header : $this->entry($index)));
	}
	
	public function remove($index) {
		return $this->removeEntry($this->entry($index));
	}
	
	private function entry($index) {
		if ($index < 0 || $index >= $this->size) {
			throw new IndexOutOfBoundsException("Index: $index, Size: $this->size");
		}
		
		$e = $this->header;
		if ($index < ($this->size >> 1)) {
			for ($i=0; $i<=$index; $i++) {
				$e = $e->next;
			}
		}
		else {
			for ($i=$this->size; $i>$index; $i--) {
				$e = $e->previous;
			}
		}
		return $e;
	}
	
	public function indexOf($o) {
		$index = 0;
		if (is_null($o)) {
			for ($e = $this->header->next; $e != $this->header; $e = $e->next) {
				if (is_null($e->element)) {
					return $index;
				}
				$index++;
			}
		}
		else {
			for ($e = $this->header->next; $e != $this->header; $e = $e->next) {
				if ($e->element === $o) {
					return $index;
				}
				$index++;
			}
		}
		return -1;
	}
	
	public function lastIndexOf($o) {
		$index = $this->size;
		if (is_null($o)) {
			for ($e = $this->header->previous; $e != $this->header; $e = $e->previous) {
				if (is_null($e->element)) {
					return $index;
				}
				$index++;
			}
		}
		else {
			for ($e = $this->header->previous; $e != $this->header; $e = $e->previous) {
				if ($e->element === $o) {
					return $index;
				}
				$index++;
			}
		}
		return -1;
	}
	
	public function peek() {
		if ($this->size == 0) {
			return null;
		}
		return $this->getFirst();
	}
	
	public function element() {
		return $this->getFirst();
	}
	
	public function poll() {
		if ($this->size == 0) {
			return null;
		}
		return $this->removeFirst();
	}
	
	public function removeHead() {
		return $this->removeFirst();
	}
	
	public function offer($e) {
		return $this->add($e);
	}
	
	public function offerFirst($e) {
		$this->addFirst($e);
		return true;
	}
	
	public function offerLast($e) {
		$this->addLast($e);
		return true;
	}
	
	public function peekFirst() {
		if ($this->size == 0) {
			return null;
		}
		return $this->getFirst();
	}
	
	public function peekLast() {
		if ($this->size == 0) {
			return null;
		}
		return $this->getLast();
	}
	
	public function pollFirst() {
		if ($this->size == 0) {
			return null;
		}
		return $this->removeFirst();
	}
	
	public function pollLast() {
		if ($this->size == 0) {
			return null;
		}
		return $this->removeLast();
	}
	
	public function push($e) {
		$this->addFirst($e);
	}
	
	public function pop() {
		return $this->removeFirst();
	}
	
	public function removeFirstOccurence($o) {
		return $this->removeElement($o);
	}
	
	public function removeLastOccurence($o) {
		if (is_null($o)) {
			for ($e = $this->header->previous; $e != $this->header; $e = $e->previous) {
				if (is_null($e->element)) {
					$this->removeEntry($e);
					return true;
				}
			}
		}
		else {
			for ($e = $this->header->previous; $e != $this->header; $e = $e->previous) {
				if ($o === $e->element) {
					$this->removeEntry($e);
					return true;
				}
			}
		}
		return false;
	}
	
	public function getIterator() {
		return new LinkedListIterator($this->header, 0, $this->size);
	}
	
	private function addBefore($e, LinkedListEntry $entry) {
		$newEntry = new LinkedListEntry($e, $entry, $entry->previous);
		$newEntry->previous->next = $newEntry;
		$newEntry->next->previous = $newEntry;
		$this->size++;
		return $newEntry;
	}
	
	public function &toArray() {
		$result = array();
		for ($e=$this->header->next; $e != $this->header; $e = $e->next) {
			$result[] = $e->element;
		}
		return $result;
	}
	
	public function offsetExists($offset) {
		return $offset > 0 && $offset < $this->size;
	}
	
	public function offsetSet($offset, $value) {
		$this->set($offset, $value);
	}
	
	public function offsetGet($offset) {
		return $this->get($offset);
	}
	
	public function offsetUnset($offset) {
		$this->remove($offset);
	}
}

class LinkedListEntry {
	public $element;
	public $next;
	public $previous;
	
	public function __construct($element, Entry $next = null, Entry $previous = null) {
		$this->element = $element;
		$this->next = $next;
		$this->previous = $previous;
	}
}

class LinkedListIterator implements Iterator {
	
	private $size;
	private $next;
	private $nextIndex;
	private $header;
	
	public function __construct(LinkedListEntry $header, $index, $size) {
		$this->header = $header;
		$this->size = $size;
		if ($index < 0 || $index >= $size) {
			throw new IndexOutOfBoundsException("Index: $index, Size: $size");
		}
		
		if ($index < ($size >> 1)) {
			$this->next = $header->next;
			for ($this->nextIndex=0; $this->$nextIndex < $index; $this->$nextIndex++) {
				$this->next = $this->next->next;
			}
		}
		else {
			$this->next = $header;
			for ($this->nextIndex=$size; $this->nextIndex > $index; $this->nextIndex--) {
				$this->next = $this->next->previous;
			}
		}
	}
	
	public function current() {
		return $this->next->element;
	}

	public function next() {
		$this->next = $this->next->next;
		$this->nextIndex++;
	}

	public function key() {
		return $this->nextIndex;
	}

	public function valid() {
		return $this->nextIndex != $this->size;
	}

	public function rewind() {
		$this->nextIndex = 0;
		$this->next = $this->header->next;
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
		$this->pos++;
		return next($this->array);
	}

	public function key() {
		return $this->pos;
	}

	public function valid() {
		return key($this->array) !== null;
	}

	public function rewind() {
		$this->pos = 0;
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
		$temp = array_values($this->array);
		return $temp;
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
	
	public function hashCode() {
		$hashCode = '';
		foreach ($this as $key => $value) {
			$hashCode .= '32' . Hasher::hash($key) . '=>' . Hasher::hash($value);
		}
		return md5($hashCode);
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

/** @gwtname java.util.IdentityHashMap */
class IdentityHashMap extends HashMap {}

class ObjectMap extends HashMap {
	public function __construct($from = null) {
		parent::__construct($from, 'hashObject');
	}
}

class HybridMap extends HashMap {
}

class UnmodifiableCollection implements Collection {
	
	private $c;
	
	public function __construct(Collection $c) {
		if (is_null($c)) {
			throw new NullPointerException();
		}
		$this->c = $c;
	}
	
	public function add($e) {
		throw new UnsupportedOperationException();
	}
	
	public function addArray(array $array) {
		throw new UnsupportedOperationException();
	}
	
	public function addAll(Collection $c) {
		throw new UnsupportedOperationException();
	}
	
	public function clear() {
		throw new UnsupportedOperationException();
	}
	
	public function removeAll(Collection $c) {
		throw new UnsupportedOperationException();
	}
	
	public function removeArray(array $array) {
		throw new UnsupportedOperationException();
	}
	
	public function removeElement($e) {
		throw new UnsupportedOperationException();
	}
	
	public function retainAll(Collection $c) {
		throw new UnsupportedOperationException();
	}
	
	public function retainArray(array $array) {
		throw new UnsupportedOperationException();
	}
	
	public function contains($o) {
		return $this->c->contains($o);
	}
	
	public function containsAll(Collection $c) {
		return $this->c->containsAll($c);
	}
	
	public function containsArray(array $array) {
		return $this->c->containsArray($array);
	}
	
	public function isEmpty() {
		return $this->c->isEmpty();
	}
	
	public function size() {
		return $this->c->size();
	}
	
	public function getIterator() {
		return $this->c->getIterator();
	}
	
	public function count() {
		return count($this->c);
	}
	
	public function &toArray() {
		return $this->c->toArray();
	}
	
	public function hashCode() {
		return $this->c->hashCode();
	}
	
}

class UnmodifiedList extends UnmodifiableCollection implements GenericList {
	
	private $l;
	
	public function __construct(GenericList $l) {
		if (is_null($l)) {
			throw new NullPointerException();
		}
		parent::__construct($l);
		$this->l = $l;
	}
	
	public function insertAll($index, Collection $c) {
		throw new UnsupportedOperationException();
	}
	
	public function insertArray($index, array $array) {
		throw new UnsupportedOperationException();
	}
	
	public function remove($index) {
		throw new UnsupportedOperationException();
	}
	
	public function insert($index, $e) {
		throw new UnsupportedOperationException();
	}
	
	public function indexOf($o) {
		return $this->l->indexOf($o);
	}
	
	public function get($index) {
		return $this->l->get($index);
	}
	
	public function set($index, $value) {
		throw new UnsupportedOperationException();
	}
	
	public function offsetExists($offset) {
		return $this->l->offsetExists($offset);
	}
	
	public function offsetSet($offset, $value) {
		throw new UnsupportedOperationException();
	}
	
	public function offsetGet($offset) {
		return $this->offsetGet($offset);
	}
	
	public function offsetUnset($offset) {
		throw new UnsupportedOperationException();
	}
}

class UnmodifiableSet extends UnmodifiableCollection implements Set {
	
	public function __construct(Set $s) {
		if (is_null($s)) {
			throw new NullPointerException();
		}
		parent::__construct($s);
	}
	
}

class UnmodifiableMap implements Map {
	
	private $m;
	
	public function __construct(Map $m) {
		if (is_null($m)) {
			throw new NullPointerException();
		}
		$this->m = $m;
	}
	
	public function putAll(Map $m) {
		throw new UnsupportedOperationException();
	}
	
	public function putArray(array $a) {
		throw new UnsupportedOperationException();
	}
	
	public function size() {
		return count($this->m);
	}
	
	public function clear() {
		throw new UnsupportedOperationException();
	}
	
	public function containsKey($key) {
		return $this->m->containsKey($key);
	}
	
	public function containsValue($value) {
		return $this->m->containsValue($value);
	}
	
	public function isEmpty() {
		return $this->m->isEmpty();
	}
	
	public function get($key) {
		return $this->m->get($key);
	}
	
	public function put($key, $value) {
		throw new UnsupportedOperationException();
	}
	
	public function remove($key) {
		throw new UnsupportedOperationException();
	}
	
	public function values() {
		return $this->m->values();
	}
	
	public function keys() {
		return $this->m->keys();
	}
	
	public function count() {
		return count($this->m);
	}
	
	public function offsetExists($offset) {
		return $this->m->offsetExists($offset);
	}
	
	public function offsetSet($offset, $value) {
		throw new UnsupportedOperationException();
	}
	
	public function offsetGet($offset) {
		return $this->m->iffsetGet($offset);
	}
	
	public function offsetUnset($offset) {
		throw new UnsupportedOperationException();
	}
	
	public function getIterator() {
		return $this->m->getIterator();
	}
	
	public function hashCode() {
		return $this->m->hashCode();
	}
	
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
		else if (Classes::classOf($x)->hasMethod('hashCode')) {
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

interface JavaLikeIterator {
	function hasNext();
	function next();
}

class JavaLikeIteratorImpl implements JavaLikeIterator {
	
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
	
	public static function unmodifiableCollection(Collection $c) {
		return new UnmodifiableCollection($c);
	}
	
	public static function unmodifiableList(GenericList $l) {
		return new UnmodifiableList($l);
	}
	
	public static function unmodifiableSet(Set $s) {
		return new UnmodifiableSet($s);
	}
	
	public static function unmodifiableMap(Map $m) {
		return new UnmodifiableMap($m);
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

/** @gwtname com.google.gwt.user.client.rpc.core.java.util.IdentityHashMap_CustomFieldSerializer */
final class IdentityHashMap_CustomFieldSerializer {
	
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

Classes::registerAlias('List', Classes::classOf('GenericList'));