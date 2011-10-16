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

require_once 'common.php';

/** @gwtname com.didactilab.gwt.phprpctest.client.service.ObjectService */
class RpcObjectService implements RemoteService {

	/** @return boolean */
	public function paramCall($param) {
		return self::isValidComplexCustomObject($param);
	}
	
	/** @return ComplexCustomObject */
	public function returnTest() {
		$obj = new ComplexCustomObject();
		self::fillComplexCustomObject($obj);
		return $obj;
	}
	
	/** @return boolean */
	public function arrayListCall($list) {
		if (count($list) != 3) {
			return false;
		}
		if ($list[0] !== true) {
			return false;
		}
		if ($list[1] !== false) {
			return false;
		}
		if ($list[2] !== true) {
			return false;
		}
		return true;
	}

	/** @return ArrayList */
	public function arrayListReturn() {
		return new ArrayList(array(new CustomObject(), new CustomObject(), new CustomObject()));
	}

	/** @return boolean */
	public function hashMapCall($map) {
		return ($map['hello'] === 'salut') && 
				($map['yes'] === 'oui') &&
				($map['no'] === 'non') &&
				($map['house'] === 'maison');
	}

	/** @return HashMap */
	public function hashMapReturn() {
		return new HashMap(array(0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three'));
	}

	/** @return boolean */
	public function hashSetCall($set) {
		return in_array(1, $set) && 
				in_array(3, $set) &&
				in_array(5, $set) &&
				in_array(7, $set);
	}

	/** @return HashSet */
	public function hashSetReturn() {
		return new HashSet(array('one', 'two', 'three', 'four', 'five'));
	}
	
	/** @return boolean */
	public function dateCall($date) {
		$part = getdate($date->toTime());
		return ($part['minutes'] == 30) &&
				($part['hours'] == 10) &&	// for winter hour
				($part['mday'] == 5) &&
				($part['mon'] == 2) && 
				($part['year'] == 1984);
	}

	/** @return Date */
	public function dateReturn() {
		$part = getdate(); // Always winter hour
		return Date::create(mktime(0, 0, 0, $part['mon'], $part['mday'], $part['year']));
	}
	
	public static function fillComplexCustomObject($obj) {
		$obj->string = "salut";
		$obj->number = 5;
		$obj->bool = true;
		$obj->real = 560.3345;
		$obj->big = 6000000000;
		$obj->custom = CustomEnum::HELLO;
		$obj->bytes = array(1, 2, 3);
		$obj->escapedString = "salut\nhello\tbonjour";
	}
	
	public static function isValidComplexCustomObject($obj) {
		return ($obj->string === "salut") &&
				($obj->number == 5) &&
				($obj->bool) &&
				($obj->real == 560.3345) &&
				($obj->big == 6000000000) &&
				($obj->custom == CustomEnum::HELLO) &&
				($obj->bytes[0] == 1) &&
				($obj->bytes[1] == 2) &&
				($obj->bytes[2] == 3) &&
				($obj->escapedString === "salut\nhello\tbonjour");
	}
	
	/** @return boolean */
	public function customSubObjectCall($obj) {
		return ($obj instanceof CustomObject_CustomSubObject) && ($obj->name === "February");
	}

	/** @return CustomObject_CustomSubObject */
	public function customSubObjectReturn() {
		$obj = new CustomObject_CustomSubObject();
		$obj->name = "February";
		return $obj;
	}
	
}