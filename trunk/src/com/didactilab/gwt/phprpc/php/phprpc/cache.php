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

/**
 * EXPERIMENTAL
 * The main idea is to cache all classes and WebOracle data in APC type cache
 * @author mathieu
 */

interface CacheEngine {
	
	function get($key);
	function set($key, $value);
	function delete($key);
	function exists($key);
	function getName();
	
}

class Cache {
	
	private static $cache = null;
	
	public static function setEngine(CacheEngine $cache) {
		self::$cache = $cache;
	}
	
	public static function getEngine() {
		return self::$cache;
	}
	
	public static function get($key) {
		return self::$cache->get($key);
	}
	
	public static function set($key, $value) {
		self::$cache->set($key, $value);
	}
	
	public static function delete($key) {
		self::$cache->delete($key);
	}
	
	public static function exists($key) {
		return self::$cache->exists($key);
	}
	
	public static function enabled() {
		return (self::$cache != null);
	}
	
}

class SessionCacheEngine implements CacheEngine {
	
	public function __construct() {
		session_start();
	}
	
	public function get($key) {
		return $_SESSION[$key];
	}
	
	public function set($key, $value) {
		$_SESSION[$key] = $value;
	}
	
	public function delete($key) {
		unset($_SESSION[$key]);
	}
	
	public function exists($key) {
		return isset($_SESSION[$key]);
	}
	
	public function getName() {
		return 'Session cache v1';
	}
	
}

class ApcCacheEngine implements CacheEngine {
	
	public function __construct() {
	}
	
	public function get($key) {
		return apc_fetch($key);
	}
	
	public function set($key, $value) {
		apc_store($key, $value);
	}
	
	public function delete($key) {
		apc_delete($key);
	}
	
	public function exists($key) {
		return apc_exists($key);
	}
	
	public function getName() {
		return 'APC cache v1';
	}
	
}