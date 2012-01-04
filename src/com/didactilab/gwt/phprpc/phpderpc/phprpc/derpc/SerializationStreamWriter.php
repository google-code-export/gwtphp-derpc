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

require_once PHPRPC_ROOT . 'derpc/ast.php';
require_once PHPRPC_ROOT . 'classes.php';
require_once PHPRPC_ROOT . 'derpc/rpcphptools.php';
require_once PHPRPC_ROOT . 'serialization.php';
require_once PHPRPC_ROOT . 'SerializationStream.php';

require_once PHPRPC_ROOT . 'derpc/CommandSerializationUtil.php';
require_once PHPRPC_ROOT . 'derpc/SerializabilityUtil.php';

abstract class CommandSerializationStreamWriterBase implements SerializationStreamWriter {
	
	private $commandSink;
	
	protected function __construct(CommandSink $sink) {
		$this->commandSink = $sink;
	}
	
	public function writeBoolean($value) {
		$this->commandSink->accept($this->makeValue(Boolean::typeClass(), $value));
	}
	
	public function writeByte($value) {
		$this->commandSink->accept($this->makeValue(Byte::typeClass(), $value));
	}
	
	public function writeChar($value) {
		$this->commandSink->accept($this->makeValue(Character::typeClass(), $value));
	}
	
	public function writeDouble($value) {
		$this->commandSink->accept($this->makeValue(Double::typeClass(), $value));
	}
	
	public function writeFloat($value) {
		$this->commandSink->accept($this->makeValue(Float::typeClass(), $value));
	}
	
	public function writeInt($value) {
		$this->commandSink->accept($this->makeValue(Integer::typeClass(), $value));
	}
	
	public function writeLong($value) {
		$this->commandSink->accept($this->makeValue(Long::typeClass(), $value));
	}
	
	public function writeObject($value) {
		$this->commandSink->accept($this->makeValue(Classes::classOfValue($value), $value));
	}
	
	public function writeObject2(Clazz $clazz, $value) {
		$this->commandSink->accept($this->makeValue($clazz, $value));
	}
	
	public function writeShort($value)  {
		$this->commandSink->accept($this->makeValue(Short::typeClass(), $value));
	}
	
	public function writeString($value) {
		$this->commandSink->accept($this->makeValue(String::clazz(), $value));
	}
	
	public function writeEnum(Clazz $clazz, $value) {
		$this->commandSink->accept($this->makeValue($clazz, $value));
	}
	
	public function writeValue(Clazz $clazz, $value) {
		/*if ($clazz === Boolean::typeClass()) {
			$this->writeBoolean($value);
		}
		else if ($clazz === Byte::typeClass()) {
			$this->writeByte($value);
		}
		else if ($clazz === Character::typeClass()) {
			$this->writeChar($value);
		}
		else if ($clazz === Double::typeClass()) {
			$this->writeDouble($value);
		}
		else if ($clazz === Float::typeClass()) {
			$this->writeFloat($value);
		}
		else if ($clazz === Integer::typeClass()) {
			$this->writeInt($value);
		}
		else if ($clazz === Long::typeClass()) {
			$this->writeLong($value);
		}
		else if ($clazz === Short::typeClass()) {
			$this->writeShort($value);
		}*/
		if ($clazz === Boolean::typeClass()) {
			$this->writeObject(new Boolean($value));
		}
		else if ($clazz === Byte::typeClass()) {
			$this->writeObject(new Byte($value));
		}
		else if ($clazz === Character::typeClass()) {
			$this->writeObject(new Character($value));
		}
		else if ($clazz === Double::typeClass()) {
			$this->writeObject(new Double($value));
		}
		else if ($clazz === Float::typeClass()) {
			$this->writeObject(new Float($value));
		}
		else if ($clazz === Integer::typeClass()) {
			$this->writeObject(new Integer($value));
		}
		else if ($clazz === Long::typeClass()) {
			$this->writeObject(new Long($value));
		}
		else if ($clazz === Short::typeClass()) {
			$this->writeObject(new Short($value));
		}
		else if ($clazz === String::clazz()) {
			$this->writeString($value);
		}
		else {
			if ($clazz->isEnum()) {
				$this->writeEnum($clazz, $value);
			}
			else {
				$this->writeObject2($clazz, $value);
			}
		}
	}
	
	public function __toString() {
		return '';
	}
	
	protected function getCommandSink() {
		return $this->commandSink;
	}
	
	protected abstract function makeValue(Clazz $type, $value);
	
}

class CommandServerSerializationStreamWriter extends CommandSerializationStreamWriterBase {
	
	private $clientOracle;
	private $identityMap;
	
	public function __construct(CommandSink $sink, ClientOracle $oracle = null, HybridMap $identityMap = null) {
		parent::__construct($sink);
		$this->clientOracle = $oracle;
		if ($this->clientOracle == null)
			$this->clientOracle = new HostedModeClientOracle();
		$this->identityMap = $identityMap;
		if ($this->identityMap == null)
			$this->identityMap = new HybridMap();
	}
	
	protected function makeValue(Clazz $type, $value) {
		if (is_null($value)) {
			return NullValueCommand::INSTANCE();
		}
		
		$accessor = Accessors::get($type);
		if ($accessor->canMakeValueCommand()) {
			return $accessor->makeValueCommand($value);
		}
		else if ($this->identityMap->containsKey($value)) {
			return $this->identityMap->get($value);
		}
		else if ($type->isArray()) {
			return $this->makeArray($type, $value);
		}
		else if ($type->isEnum()) {
			return $this->makeEnum($type, $value);
		}
		else {
			return $this->makeObject($type, $value);
		}
	}
	
	private function makeArray(Clazz $type, array &$value) {
		$toReturn = new ArrayValueCommand($type->getComponentType());
		$this->identityMap->put($value, $toReturn);
		for ($i=0, $j=ArrayType::getLength($value); $i < $j; $i++) {
			$arrayValue = ArrayType::get($value, $i);
			if (is_null($arrayValue)) {
				$toReturn->add(NullValueCommand::INSTANCE());
			}
			else {
				$valueType = $type->getComponentType()->isPrimitive() ? 
					$type->getComponentType() : Classes::classOfValue($arrayValue);
				$toReturn->add($this->makeValue($valueType, $arrayValue));
			}
		}
		return $toReturn;
	}
	
	private function makeEnum(Clazz $type, $value) {
		$toReturn = new EnumValueCommand($type);
		$toReturn->setValue($value);
		return $toReturn;
	}
	
	private function makeObject(Clazz $type, $value) {
		// no anonymous class, and no local class in php
		
		$manualType = $type;
		$customSerializer = null;
		do {
			$customSerializer = SerializabilityUtil::hasCustomFieldSerializer($manualType);
			if ($customSerializer != null) {
				break;
			}
			$manualType = $manualType->getSuperClass();
		} while ($manualType != null);
		
		$ins = null;
		if ($customSerializer != null) {
			$ins = $this->serializeWithCustomSerializer($customSerializer, $value, $type, $manualType);
		}
		else {
			$ins = new InstantiateCommand($type);
			$this->identityMap->put($value, $ins);
		}
		
		if ($type != $manualType) {
			if (!Classes::classOf('GWTSerializable')->isAssignableFrom($type) && !Classes::classOf('IsSerializable')->isAssignableFrom($type)) {
				throw new SerializationException($type->getName() . ' is not a serializable type');
			}
		}
		
		while ($type != $manualType) {
			$serializableFields = $this->clientOracle->getOperableFields($type);
			foreach ($serializableFields as $declField) {
				assert ($declField != null);
				
				//echo '[' . $declField->getName() . ' = ' . $declField->getType() . ']<br />';
				$accessor = Accessors::get($declField->getType());
				$valueCommand = null;
				$fieldValue = $accessor->get($value, $declField);
				if (is_null($fieldValue)) {
					$valueCommand = NullValueCommand::INSTANCE();
				}
				else {
					$fieldType = $declField->getType()->isPrimitive() ? 
						$declField->getType() :
						//Classes::classOf($declField);
						//Classes::classOfValue($fieldValue);
						//($declField->getType()->isEnum() ? $declField->getType() : Classes::classOfValue($fieldValue));
						($declField->hasType() ? $declField->getType() : Classes::classOfValue($fieldValue));
					$valueCommand = $this->makeValue($fieldType, $fieldValue);
				}
				//echo '{set ' . $declField->getDeclaringClass()->getName() . ' / ' . $declField->getName() . ' / ' . $valueCommand . '}';
				$ins->set($declField->getDeclaringClass(), $declField->getName(), $valueCommand);
			}
			$type = $type->getSuperClass();
		}
		return $ins;
	}
	
	private function serializeWithCustomSerializer(Clazz $customSerializer, $instance, Clazz $instanceClass, Clazz $manuallySerializedType) {
		assert(!$instanceClass->isArray());
		
		foreach ($customSerializer->getMethods() as $method) {
			if ($method->getName() === 'serialize') {
				assert($method->isStatic());
				
				$toReturn = new InvokeCustomFieldSerializerCommand($instanceClass, $customSerializer, $manuallySerializedType);
				$this->identityMap->put($instance, $toReturn);
				
				$subWriter = new CommandServerSerializationStreamWriter(new HasValuesCommandSink($toReturn), $this->clientOracle, $this->identityMap);
				$method->invoke($subWriter, $instance);
				
				return $toReturn;
			}
		}
		
		throw new NoSuchMethodException('Could not find serialize method in custom serializer ' . $customSerializer->getName());
	}
	
}