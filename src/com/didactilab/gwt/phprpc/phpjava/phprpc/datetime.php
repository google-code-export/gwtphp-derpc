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

define('PHP_DATE_MIN', ~PHP_INT_MAX); 

/** @gwtname java.sql.Timestamp */
class Timestamp {
	
}

/** @gwtname java.util.Date */
class Date {
	private $time;

	public function __construct($timestamp = PHP_DATE_MIN) {
		if ($timestamp == PHP_DATE_MIN) {
			$timestamp = time() * 1000;
		}
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

/** @gwtname java.sql.Time */
class Time extends Date {

}