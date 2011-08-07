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
require_once PHPRPC_ROOT . 'stream.php';

require_once PHPRPC_ROOT . 'derpc/SerializabilityUtil.php';
require_once PHPRPC_ROOT . 'derpc/SimplePayloadSink.php';

interface CastableTypeData {
	function toJs();
}

class CastableTypeDataImpl {
	private $castableTypeMapJs;

	public function __construct($castableTypeMapJs) {
		$this->castableTypeMapJs = $castableTypeMapJs;
	}

	public function toJs() {
		return $this->castableTypeMapJs;
	}
}

abstract class ClientOracle {

	public abstract function createCommandSink(OutputStream $out);
	public abstract function createUnusedIdent($ident);
	public abstract function getCastableTypeData(Clazz $clazz);
	public abstract function getFieldId(Clazz $clazz, $fieldName);
	public abstract function getFieldIdByClassName($className, $fieldName);
	public abstract function getFieldName(Clazz $clazz, $fieldId);
	public abstract function getMethodId(Clazz $clazz, $methodName, array $argsClass);
	public abstract function getMethodIdByClassName($clazzName, $methodName, array $jsniArgTypes);
	public abstract function getOperableFields(Clazz $clazz);
	public abstract function getQueryId(Clazz $clazz);
	public abstract function getSeedName(Clazz $clazz);
	public abstract function getTypeName($seedName);
	public abstract function isScript();

}

class HostedModeSimplePayloadSink extends SimplePayloadSink {

	private $buffer;

	public function __construct(BufferedWriter $buffer) {
		parent::__construct($buffer);
		$this->buffer = $buffer;
	}

	public function finish() {
		parent::finish();
		$this->buffer->flush();
	}

}

class HostedModeClientOracle extends ClientOracle {

	public function createCommandSink(OutputStream $out) {
		$writer = new BufferedWriter($out);
		return new HostedModeSimplePayloadSink($writer);
	}

	public function createUnusedIdent($ident) {
		self::unimplemented();
	}

	public function getCastableTypeData(Clazz $clazz) {
		self::unimplemented();
	}

	public function getFieldId(Clazz $clazz, $fieldName) {
		self::unimplemented();
	}

	public function getFieldIdByClassName($className, $fieldName) {
		self::unimplemented();
	}

	public function getFieldName(Clazz $clazz, $fieldId) {
		while ($clazz != null) {
			if ($clazz->hasField($fieldId))
			return (object) array('class' => $clazz, 'fieldName' => $fieldId);
			$clazz = $clazz->getSuperClass();
		}
		return null;
		//return (object) array('class' => $clazz, 'fieldName' => $fieldId);
	}

	public function getMethodId(Clazz $clazz, $methodName, array $argsClass) {
		self::unimplemented();
	}

	public function getMethodIdByClassName($clazzName, $methodName, array $jsniArgTypes) {
		self::unimplemented();
	}

	public function getOperableFields(Clazz $clazz) {
		return SerializabilityUtil::applyFieldSerializationPolicy($clazz);
	}

	public function getQueryId(Clazz $clazz) {
		self::unimplemented();
	}

	public function getSeedName(Clazz $clazz) {
		self::unimplemented();
	}

	public function getTypeName($seedName) {
		return $seedName;
	}

	public function isScript() {
		return false;
	}

	private static function unimplemented() {
		throw new RuntimeException('Not supported in Developpement Mode');
	}
}

?>