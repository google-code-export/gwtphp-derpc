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

class StreamException extends Exception {
}

abstract class OutputStream {
	public abstract function write($data);
}

class BufferedOutputStream extends OutputStream {
	private $buffer = '';
	
	public function write($data) {
		$this->buffer .= $data;
	}
	
	public function getBuffer() {
		return $this->buffer;
	}
}

class EchoOutputStream extends OutputStream {
	public function write($data) {
		echo $data;
	}
}

interface Appendable {
	
	function append($data);
	function appendData($data, $start, $end);
	
}

class BufferedWriter implements Appendable {
	
	private $buffer = '';
	private $output;
	
	public function __construct(OutputStream $output = null) {
		$this->output = $output;
	}
	
	public function append($data) {
		$this->buffer .= $data;
		return $this;
	}
	
	public function appendData($data, $start, $end) {
		$this->buffer .= substr($data, $start, $end - $start);
		return $this;
	}
	
	public function toString() {
		return $this->buffer;
	}
	
	public function flush() {
		if ($this->output != null)
			$this->output->write($this->buffer);
	}
	
}

class StringBuffer {
	
	private $buffer = '';
	
	public function put(&$data) {
		/*if (is_object($data)) {
			if ($data instanceof StringBuffer) {
				$this->buffer .= (string) $data;
				return;
			}
		}*/
		$this->buffer .= (string) $data;
	}
	
	public function __toString() {
		return $this->buffer;
	}
	
	public function getPosition() {
		return mb_strlen($this->buffer);
	}
	
	public function clear() {
		$this->buffer = '';
	}
	
}

class StringReader {
	
	const SEPARATOR = '~';
	
	private $buffer;
	private $position = 0;
	
	public function __construct($buffer) {
		$this->buffer = $buffer;
	}
	
	public function readInteger() {
		return intval($this->readNextToken());
	}
	
	public function read($size) {
		if ($size == 0) {
			return '';
		}
		else {
			$val = mb_substr($this->buffer, $this->position, $size);
			$this->position += $size;
			return $val;
		}
	}
	
	private function readNextToken() {
		$end = mb_strpos($this->buffer, self::SEPARATOR, $this->position);
		if ($end === false) {
			$this->position = count($this->buffer);
			return $this->buffer;
		}
		else {
			$val = mb_substr($this->buffer, $this->position, $end - $this->position);
			$this->position = $end + 1;
			return $val;
		}
	}
	
	public function readString() {
		//echo 'buffer(' . mb_substr($this->buffer, $this->position, 100) . ')';
		$length = $this->readInteger();
		//echo 'length(' . $length . ')';
		if ($length == -1) {
			$value = null;
		}
		else {
			$value = $this->read($length);
		}
		$marker = $this->read(1);
		if ($marker !== self::SEPARATOR) {
			echo 'marker(' . $marker . ')';
			//echo 'buffer(' . mb_substr($this->buffer, $this->position - $length - 10, $length + 10) . ')';
			throw new StreamException('no marker at end of string');
		}
		return $value;
	}
	
	public function atEnd() {
		return ($this->position == count($this->buffer));
	}
	
	public function readStringStringMap() {
		$map = array();
		$count = $this->readInteger();
		for ($i=0; $i<$count; $i++) {
			$key = $this->readString();
			$value = $this->readString();
			$map[$key] = $value;
		}
		return $map;
	}
	
	public function readStringList() {
		$list = array();
		$count = $this->readInteger();
		for ($i=0; $i<$count; $i++) {
			$value = $this->readString();
			$list[] = $value;
		}
		return $list;
	}
	
}