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
require_once PHPRPC_ROOT . 'derpc/rpcphptools.php';
require_once PHPRPC_ROOT . 'serialization.php';
require_once PHPRPC_ROOT . 'SerializationStream.php';

class CommandServerSerializationStreamReaderVisitor extends RpcCommandVisitor {
	
	private $values = array();
	private $backRefs;
	
	public function __construct($backRefs) {
		$this->backRefs = $backRefs;
	}

	public function endVisitBoolean(BooleanValueCommand $x, Context $ctx) {
		$this->pushScalar($x);
	}

	public function endVisitByte(ByteValueCommand $x, Context $ctx) {
		$this->pushScalar($x);
	}

	public function endVisitChar(CharValueCommand $x, Context $ctx) {
		$this->pushScalar($x);
	}

	public function endVisitDouble(DoubleValueCommand $x, Context $ctx) {
		$this->pushScalar($x);
	}

	public function endVisitEnum(EnumValueCommand $x, Context $ctx) {
		$this->push($x, $x->getValue());
	}

	public function endVisitFloat(FloatValueCommand $x, Context $ctx) {
		$this->pushScalar($x);
	}

	public function endVisitInt(IntValueCommand $x, Context $ctx) {
		$this->pushScalar($x);
	}

	public function endVisitLong(LongValueCommand $x, Context $ctx) {
		$this->pushScalar($x);
	}

	public function endVisitNull(NullValueCommand $x, Context $ctx) {
		$this->pushScalar($x);
	}

	public function endVisitSet(SetCommand $x, Context $ctx) {
		try {
			$f = $x->getFieldDeclClass()->getField($x->getField());
			//$count = count($this->values);
			$value = array_pop($this->values);
			$instance = array_peek($this->values);
			//echo 'value = ' . var_export($value, true) . ' &instance = ' . var_export($instance, true) . '<br />';
			// TODO CommandSerializationUtil.getAccessor
			//assert($value != null);
			//echo 'end value (count=' . $count .')<br />';
			//Accessors::get($f->getType())->set($instance, $f, $value);
			$f->setValue($instance, $value);
			return;
		}
		catch (Exception $e) {
			throw new SerializationException('Unable to set field value ' . $e);
		}
	}

	public function endVisitShort(ShortValueCommand $x, Context $ctx) {
		$this->pushScalar($x);
	}

	public function endVisitString(StringValueCommand $x, Context $ctx) {
		$this->pushScalar($x);
	}

	public function visitArray(ArrayValueCommand $x, Context $ctx) {
		if ($this->maybePushBackRef($x)) {
			$values = $x->getComponentValues();
			$array = ArrayType::newInstance(null, count($values));
			
			$size = count($values);
			for ($i=0; $i<$size; $i++) {
				$this->accept($values[$i]);
				$array[$i] = array_pop($this->values);
			}
			
			$this->push($x, $array);
		}
		return false;
	}

	public function visitInstantiate(InstantiateCommand $x, Context $ctx) {
		if ($this->maybePushBackRef($x)) {
			$instance = $x->getTargetClass()->newInstance();
			$this->push($x, $instance);
			return true;
		}
		return false;
	}

	public function visitInvoke(InvokeCustomFieldSerializerCommand $x, Context $ctx) {
		if ($this->maybePushBackRef($x)) {
			$subReader = new CommandServerSerializationStreamReader($this->backRefs);
			$subReader->prepareToRead($x->getValues());
			
			$serializerClass = $x->getSerializerClass();
			assert($serializerClass != null);
			
			// For transform Integer into int by example s
			$trans = $serializerClass->getMethod('transform');
			if ($trans != null) {
				assert($trans->isStatic());
				$value = $trans->invoke($subReader);
				$this->pushValue($value);
				
				return false;
			}
			
			$instantiate = null;
			$deserialize = null;
			foreach ($serializerClass->getMethods() as $m) {
				if ($m->getName() == 'instanciate') {
					$instantiate = $m;
				}
				else if ($m->getName() == 'deserialize') {
					$deserialize = $m;
				}
				
				if ($instantiate != null && $deserialize != null) {
					break;
				}
			}
			
			assert($deserialize != null);
			
			$instance = null;
			if ($instantiate != null) {
				assert($instantiate->isStatic());
				$instance = $instantiate->invoke($subReader);
			}
			else {
				$instance = $x->getTargetClass()->newInstance();
			}
			
			assert($instance != null);
			$this->push($x, $instance);
			
			$this->acceptArray($x->getSetters());
			
			$deserialize->invoke($subReader, $instance);
		}
		
		return false;
	}
	
	private function maybePushBackRef(IdentityValueCommand $x) {
		$instance = $this->backRefs->get($x);
		//$instance = $this->backRefs[spl_object_hash($x)];
		if ($instance == null)
			return true;
		else {
			array_push($this->values, $instance);
			return false;
		}
	}
	
	private function push(IdentityValueCommand $x, $value) {
		assert(!$this->backRefs->containsKey($x));
		//assert(!isset($this->backRefs[spl_object_hash($x)]));
		//$this->backRefs[spl_object_hash($x)] = $value;
		$this->backRefs->put($x, $value);
		array_push($this->values, $value);
	}
	
	private function pushScalar(ScalarValueCommand $x) {
		array_push($this->values, $x->getValue());
	}
	
	private function pushValue(&$value) {
		array_push($this->values, $value);
	}
	
	public function getValues() {
		return $this->values;
	}
	
}

class CommandServerSerializationStreamReader implements SerializationStreamReader {
	
	private $backRefs;
	private $values;
	
	public function __construct(ObjectMap $backRefs = null) {
		if ($backRefs == null)
			$this->backRefs = new ObjectMap();
		else 
			$this->backRefs = $backRefs;
	}
	
	public function prepareToRead(array $commands) {
		$this->values = new JavaLikeIteratorImpl($commands);
	}
	
	public function readBoolean() {
		return $this->readNumberCommand('BooleanValueCommand')->getValue();
	}
	
	public function readByte() {
		return $this->readNumberCommand('ByteValueCommand')->getValue();
	}
	
	public function readChar() {
		return $this->readNumberCommand('CharValueCommand')->getValue();
	}
	
	public function readDouble() {
		return $this->readNumberCommand('DoubleValueCommand')->getValue();
	}
	
	public function readFloat() {
		return $this->readNumberCommand('FloatValueCommand')->getValue();
	}
	
	public function readInt() {
		return $this->readNumberCommand('IntValueCommand')->getValue();
	}
	
	public function readLong() {
		return $this->readNumberCommand('LongValueCommand')->getValue();
	}
	
	public function readObject() {
		$command = $this->readNextCommand('ValueCommand');
		$v = new CommandServerSerializationStreamReaderVisitor($this->backRefs);
		$v->accept($command);
		return array_pop($v->getValues());
	}
	
	public function readShort() {
		return $this->readNumberCommand('ShortValueCommand')->getValue();
	}
	
	public function readString() {
		return $this->readObject();
	}
	
	private function readNextCommand($clazzName) {
		if (!$this->values->hasNext()) {
			throw new SerializationException('Reached end of stream');
		}
		$next = $this->values->next();
		if (!($next instanceof $clazzName)) {
			throw new SerializationException('Cannot assign ' . next . ' to ' . $clazzName);
		}
		return $next;
	}
	
	private function readNumberCommand($clazzName) {
		if (!$this->values->hasNext()) {
			throw new SerializationException('Reached end of stream');
		}
		
		$next = $this->values->next();
		
		if ($next instanceof $clazzName) {
			return $next;
		}
		else if ($next instanceof LongValueCommand) {
			if (!($next instanceof $clazzName))
				throw new SerializationException('Cannot assign ' . $next . ' to ' . $clazzName);
			return $next;
		}
		else if ($next instanceof DoubleValueCommand) {
			return new $clazzName($next->getValue());
		}
		else {
			throw new SerializationException('Cannot create a numeric ValueCommand from a ');
		}
	}
	
}

