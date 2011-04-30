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
require_once PHPRPC_ROOT . 'ClientOracle.php';
require_once PHPRPC_ROOT . 'rpcphptools.php';

require_once PHPRPC_ROOT . 'stream.php';

require_once PHPRPC_ROOT . 'WebModePayloadSink.php';

class ClassData {
	
	public $castableTypeData;	// CastableTypeData
	public $fieldIdentsToNames = array();	// Map<String, String>
	public $fieldNamesToIdents = array();	// Map<String, String>
	public $methodJsniNamesToIdents = array();	// Map<String, String>
	public $queryId;	// int
	public $seedName;	// String
	public $serializableFields = array();	// List<String>
	public $typeName;	// String
	
}

class WebModeClientOracle extends ClientOracle {
	
	protected $classData = array();	// Map<String, ClassData>
	protected $idents = array();	//Set<String>
	protected $seedNamesToClassData = array();	//Map<String, ClassData>
	private $operableFieldMap;
	
	protected function __construct() {
		$this->operableFieldMap = new ObjectMap();
	}
	
	public function __sleep() {
		return array('classData', 'idents', 'seedNamesToClassData');
	}
	
	public function __wakeup() {
		$this->operableFieldMap = new ObjectMap();
	}
	
	public static function load($stream) {
		// execution time = 32 ms
		//$error = error_reporting(E_ALL);
		//$time = microtime(true);
		$obj = unserialize($stream);
		if ($obj === false) {
			throw new IncompatibleRemoteServiceException('The unserialization of WebModeClientOracle has failed');
		}
		//$t2 = microtime(true);
		//echo '[time ' . (($t2 - $time) * 1000 ) . ']';
		
		//error_reporting($error);
		
		return $obj;
	}
	
	
	
	public static function jsniName(Clazz $clazz) {
		if ($clazz->isPrimitive()) {
			if ($clazz === Boolean::typeClass()) {
				return 'Z';
			}
			else if ($clazz === Byte::typeClass()) {
				return 'B';
			}
			else if ($clazz === Character::typeClass()) {
				return 'C';
			}
			else if ($clazz === Short::typeClass()) {
				return 'S';
			}
			else if ($clazz === Integer::typeClass()) {
				return 'I';
			}
			else if ($clazz === Long::typeClass()) {
				return 'J';
			}
			else if ($clazz === Float::typeClass()) {
				return 'F';
			}
			else if ($clazz === Double::typeClass()) {
				return 'D';
			}
			throw new RuntimeException('Unhandled primitive tye ' + $clazz->getName());
		}
		else if ($clazz->isArray()) {
			return '[' . self::jsniName($clazz->getComponentType());
		}
		else {
			return 'L' . str_replace('.', '/', $clazz->getFullName()) . ';';
		}
	}
	
	
	
	
	public function createCommandSink(OutputStream $out) {
		return new WebModePayloadSink($this, $out);
	}
	
	public function createUnusedIdent($ident) {
		while (in_array($ident, $this->idents, true)) {
			$ident .= '$';
		}
		return $ident;
	}
	
	public function getCastableTypeData(Clazz $clazz) {
		while ($clazz != null) {
			$name = $this->canonicalName($clazz);
			//echo '[for ' . $clazz->getName() . ' => ' . $name . ']';
			$toReturn = $this->getCastableTypeDataByName($name);
			if ($toReturn != null) {
				return $toReturn;
			}
			$clazz = $clazz->getSuperClass();
		}
		return null;
	}
	
	public function getFieldId(Clazz $clazz, $fieldName) {
		while ($clazz != null) {
			$className = $clazz->getFullName();
			$data = $this->getClassData($className);
			if (isset($data->fieldNamesToIdents[$fieldName])) {
				return $data->fieldNamesToIdents[$fieldName];
			}
			$clazz = $clazz->getSuperClass();
		}
		return null;
	}
	
	public function getFieldIdByClassName($className, $fieldName) {
		$data = $this->getClassData($className);
		return $data->fieldNamesToIdents[$fieldName];
	}
	
	public function getFieldName(Clazz $clazz, $fieldId) {
		while ($clazz != null) {
			$data = $this->getClassData($clazz->getFullName());
			$fieldName = $data->fieldIdentsToNames[$fieldId];
			if ($fieldName == null) {
				$clazz = $clazz->getSuperClass();
			}
			else {
				return (object) array('class' => $clazz, 'fieldName' => $fieldName);
			}
		}
		return null;
	}
	
	public function getMethodId(Clazz $clazz, $methodName, array $argsClass) {
		while ($clazz != null) {
			$toReturn = $this->getMethodIdByClass($clazz->getFullName(), $methodName, $argsClass);
			if ($toReturn != null) {
				return $toReturn;
			}
			$clazz = $clazz->getSuperClass();
		}
		return null;
	}
	
	public function getMethodIdByClassName($clazzName, $methodName, array $jsniArgTypes) {
		$sb = $methodName . '(';
		foreach ($jsniArgTypes as $jsniArg) {
			$sb .= $jsniArg;
		}
		$sb .= ')';
		
		///echo "[methodId '$sb']";
		
		$data = $this->getClassData($clazzName);
		
		//echo '{' . var_export($this->classData, true) . '}';
		
		//echo '{' . var_export($data->methodJsniNamesToIdents, true) . '}';
		
		$jsIdent = $data->methodJsniNamesToIdents[$sb];
		return $jsIdent;
	}
	
	public function getOperableFields(Clazz $clazz) {
		$toReturn = $this->operableFieldMap->get($clazz);
		if (!is_null($toReturn)) {
			return $toReturn;
		}
		
		$data = $this->getClassData($clazz->getFullName());
		$toReturn = array_new(count($data->serializableFields), null);
		for ($i = 0, $c = count($data->serializableFields); $i < $c; $i++) {
			$fieldName = $data->serializableFields[$i];
			$toReturn[$i] = $clazz->getField($fieldName);
			if (is_null($toReturn[$i])) {
				throw new IncompatibleRemoteServiceException('No field ' . $fieldName);
			}
		}
		
		$this->operableFieldMap->put($clazz, $toReturn);
		return $toReturn;
	}
	
	public function getQueryId(Clazz $clazz) {
		while (!is_null($clazz)) {
			$toReturn = $this->getQueryIdByClassName($this->canonicalName($clazz));
			if ($toReturn != 0) {
				return $toReturn;
			}
			$clazz = $clazz->getSuperClass();
		}
		return 0;
	}
	
	public function getSeedName(Clazz $clazz) {
		$data = $this->getClassData($clazz->getFullName());
		return $data->seedName;
	}
	
	public function getTypeName($seedName) {
		// TODO: Decide how to handle the no-metadata case
		if (mb_strpos($seedName, 'Class$') !== FALSE) {
			$seedName = mb_substr($seedName, 6);
		}
		$data = $this->seedNamesToClassData[$seedName];
		return $data == null ? null : $data->typeName;
	}
	
	public function isScript() {
		return true;
	}
	
	private function canonicalName(Clazz $clazz) {
		if ($clazz->isArray()) {
			$leafType = $clazz;
			do {
				$leafType = $leafType->getComponentType();
			} while ($leafType->isArray());
			
			$enclosing = $leafType->getEnclosingClass();
			if (!is_null($enclosing)) {
				// com.foo.Enclosing$Name[]
				return $this->canonicalName($enclosing) . '$' . $clazz->getName();
			}
			else if ($leafType->getPackage() === '') {
				// Name0[
				return $clazz->getName();
			}
			else {
				// com.foo.Name[]
				return $leafType->getPackage() . '.' . $clazz->getName();
			}
		}
		else {
			return $clazz->getFullName();
		}
	}
	
	private function getCastableTypeDataByName($className) {
		$data = $this->getClassData($className);
		return $data->castableTypeData;
	}
	
	private function getQueryIdByClassName($className) {
		$data = $this->getClassData($className);
		return $data->queryId;
	}
	
	private function getClassData($className) {
		//echo '[getClassData ' . $className . ']';
		if (!isset($this->classData[$className])) {
			//echo '[not set]';
			$toReturn = new ClassData();
			$this->classData[$className] = $toReturn;
			return $toReturn;
		}
		return $this->classData[$className];
	}
	
	private function getMethodIdByClass($className, $methodName, array $args) {
		$jsniArgTypes = array_new(count($args), null);
		for ($i=0, $c=count($args); $i < $c; $i++) {
			$jsniArgTypes[$i] = $this->jsniName($args[$i]);
		}
		return $this->getMethodIdByClassName($className, $methodName, $jsniArgTypes);
	}
	
}