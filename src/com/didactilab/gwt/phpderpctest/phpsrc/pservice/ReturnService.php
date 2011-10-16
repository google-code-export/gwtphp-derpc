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

/** @gwtname com.didactilab.gwt.phprpctest.client.service.ReturnService */
class ReturnService implements RemoteService {
	
	public function noReturn() {
	}
	
	/** @return boolean */
	public function boolTrueReturn() {
		return true;
	}
	
	/** @return boolean */
	public function boolFalseReturn() {
		return false;
	}
	
	/** @return byte */
	public function byteReturn() {
		return 5;
	}
	
	/** @return char */
	public function charReturn() {
		return 'c';
	}
	
	/** @return char */
	public function charUTF8Return() {
		return 'é';
	}
	
	/** @return double */
	public function doubleReturn() {
		return 100.5;
	}
	
	/** @return float */
	public function floatReturn() {
		return 50.3;
	}
	
	/** @return int */
	public function intReturn() {
		return 1000000000;
	}
	
	/** @return long */
	public function longReturn() {
		return 5000000000;
	}
	
	/** @return short */
	public function shortReturn() {
		return 32000;
	}
	
	/** @return string */
	public function stringReturn() {
		return 'Hello the world';
	}
	
	/** @return string */
	public function stringEscapeReturn() {
		return "Bonjour\nSalut\nHi\tHello";
	}
	
	/** @return string */
	public function stringUTF8Return() {
		return 'abc éèçàùîö';
	}
	
	/** @return CustomObject */
	public function objectReturn() {
		$obj = new CustomObject();
		$obj->number = 5;
		return $obj;
	}
	
	/** @return CustomEnum */
	public function enumReturn() {
		return CustomEnum::AVE;
	}
	
	/** @return int[][] */
	public function intArrayReturn() {
		return array(array(1, 2), array(3, 4));
	}
	
	/** @return String[] */
	public function stringArrayReturn() {
		return array('hello', 'salut', 'gutentag');
	}
	
}