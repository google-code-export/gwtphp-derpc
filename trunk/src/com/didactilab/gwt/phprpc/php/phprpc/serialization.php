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

class SerializationException extends Exception {
	
}

class RemoteException extends Exception {
	
}

class SecurityException extends Exception {
	
}

interface GWTSerializable {
	
}

interface IsSerializable {
	
}

class Throwable implements IsSerializable, Magic {
	/** @var string **/
	public $detailMessage;
	/** @transient */
	private $exceptionClass;
	
	public function __construct($exceptionClassName, $msg) {
		$this->exceptionClass = Classes::classOf($exceptionClassName);
		$this->detailMessage = $msg;
	}
	
	public function getMagicClassName() {
		return $this->exceptionClass->getName();
	}
	
	public function getMagicClassFullName() {
		return $this->exceptionClass->getFullName();
	}
}