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

require_once PHPRPC_ROOT . 'serialization.php';
require_once PHPRPC_ROOT . 'rpcphptools.php';

interface Context {
}

class NullContext implements Context {
}

interface HasSetters {
	function getSetters();
	function set(Clazz $fieldDeclClass, $fieldName, ValueCommand $value);
}

interface HasTargetClass {
	function getTargetClass();
}

interface HasValues {
	function addValue(ValueCommand $value);
	function getValues();
}

class RpcCommandVisitor {

	public function acceptArray(array $x) {
		$this->doAcceptArray($x);
	}

	public function accept(RpcCommand $x) {
		return $this->doAccept($x);
	}

	public function endVisitArray(ArrayValueCommand $x, Context $ctx) {
	}

	public function endVisitBoolean(BooleanValueCommand $x, Context $ctx) {
	}

	public function endVisitByte(ByteValueCommand $x, Context $ctx) {
	}

	public function endVisitChar(CharValueCommand $x, Context $ctx) {
	}

	public function endVisitDouble(DoubleValueCommand $x, Context $ctx) {
	}

	public function endVisitEnum(EnumValueCommand $x, Context $ctx) {
	}

	public function endVisitFloat(FloatValueCommand $x, Context $ctx) {
	}

	public function endVisitInstantiate(InstantiateCommand $x, Context $ctx) {
	}

	public function endVisitInt(IntValueCommand $x, Context $ctx) {
	}

	public function endVisitInvoke(InvokeCustomFieldSerializerCommand $x, Context $ctx) {
	}

	public function endVisitLong(LongValueCommand $x, Context $ctx) {
	}

	public function endVisitNull(NullValueCommand $x, Context $ctx) {
	}

	public function endVisitReturn(ReturnCommand $x, Context $ctx) {
	}

	public function endVisitSet(SetCommand $x, Context $ctx) {
	}

	public function endVisitShort(ShortValueCommand $x, Context $ctx) {
	}

	public function endVisitString(StringValueCommand $x, Context $ctx) {
	}

	public function endVisitThrow(ThrowCommand $x, Context $ctx) {
	}

	public function visitArray(ArrayValueCommand $x, Context $ctx) {
		return true;
	}

	public function visitBoolean(BooleanValueCommand $x, Context $ctx) {
		return true;
	}

	public function visitByte(ByteValueCommand $x, Context $ctx) {
		return true;
	}

	public function visitChar(CharValueCommand $x, Context $ctx) {
		return true;
	}

	public function visitDouble(DoubleValueCommand $x, Context $ctx) {
		return true;
	}

	public function visitEnum(EnumValueCommand $x, Context $ctx) {
		return true;
	}

	public function visitFloat(FloatValueCommand $x, Context $ctx) {
		return true;
	}

	public function visitInstantiate(InstantiateCommand $x, Context $ctx) {
		return true;
	}

	public function visitInt(IntValueCommand $x, Context $ctx) {
		return true;
	}

	public function visitInvoke(InvokeCustomFieldSerializerCommand $x, Context $ctx) {
		return true;
	}

	public function visitLong(LongValueCommand $x, Context $ctx) {
		return true;
	}

	public function visitNull(NullValueCommand $x, Context $ctx) {
		return true;
	}

	public function visitReturn(ReturnCommand $x, Context $ctx) {
		return true;
	}

	public function visitSet(SetCommand $x, Context $ctx) {
		return true;
	}
	
	public function visitShort(ShortValueCommand $x, Context $ctx) {
		return true;
	}
	
	public function visitString(StringValueCommand $x, Context $ctx) {
		return true;
	}
	
	public function visitThrow(ThrowCommand $x, Context $ctx) {
		return true;
	}
	
	protected function doAcceptArray(array $x) {
		foreach ($x as $c)
			$this->accept($c);
	}
	
	protected function doAccept(RpcCommand $x) {
		$x->traverse($this, new NullContext());
	}

}

abstract class CommandSink {

	public abstract function accept(RpcCommand $command);
	public abstract function finish();

}

abstract class RpcCommand {
	
	public function equals($other) {
		return $this === $other;
	}

	public function clear() {
	}

	public abstract function traverse(RpcCommandVisitor $visitor, Context $ctx);
	
	public function __toString() {
		return '[class ' . get_class($this) . ']';
	}

}

abstract class ValueCommand extends RpcCommand {
}

abstract class IdentityValueCommand extends ValueCommand implements HasHashCode {
	
	private static $hashCounter = 0;
	private $hash;

	public function __construct() {
		$this->hash = ++self::$hashCounter;
	}
	
	public function hashCode() {
		return 'RPCCommand$' . $this->hash;
	}
	
}

abstract class ScalarValueCommand extends ValueCommand implements HasHashCode {

	public function equals($other) {
		if (!($other instanceof ScalarValueCommand)) {
			return false;
		}
		$myValue = $this->getValue();
		$otherValue = $other->getValue();
		if (is_null($myValue) && is_null($otherValue)) {
			return true;
		}
		else if (is_null($myValue) && !is_null($otherValue)) {
			return false;
		}
		else {
			return $myValue === $otherValue;
		}
	}
	
	public function hashCode() {
		return is_null($this->getValue()) ? 0 : (string) $this->getValue();
	}

	public abstract function getValue();

}

class SetCommand extends RpcCommand {
	private $field;

	private $fieldDeclClazz;
	private $value;

	public function __construct(Clazz $fieldDeclClass, $field, ValueCommand $value) {
		$this->field = $field;
		$this->fieldDeclClazz = $fieldDeclClass;
		$this->value = $value;
	}

	public function clear() {
		$this->field = null;
		$this->value = null;
	}

	public function getField() {
		return $this->field;
	}

	public function getFieldDeclClass() {
		return $this->fieldDeclClazz;
	}

	public function getValue() {
		return $this->value;
	}

	public function traverse(RpcCommandVisitor $visitor, Context $ctx) {
		if ($visitor->visitSet($this, $ctx)) {
			$this->value->traverse($visitor, $ctx);
		}
		$visitor->endVisitSet($this, $ctx);
	}
}

class ReturnCommand extends RpcCommand implements HasValues {
	private $values = array();

	public function addValue(ValueCommand $value) {
		array_push($this->values, $value);
	}

	public function clear() {
		$this->values = array();
	}

	public function getValues() {
		return $this->values;
	}

	public function traverse(RpcCommandVisitor $visitor, Context $ctx) {
		if ($visitor->visitReturn($this, $ctx)) {
			$visitor->acceptArray($this->values);
		}
		$visitor->endVisitReturn($this, $ctx);
	}
}

class ThrowCommand extends RpcCommand implements HasValues {
	private $toThrow;

	public function addValue(ValueCommand $value) {
		assert($toThrow == null);
		$this->toThrow = $value;
	}

	public function getThrownValue() {
		return $this->toThrow;
	}

	public function getValues() {
		return array($this->toThrow);
	}

	public function traverse(RpcCommandVisitor $visitor, Context $ctx) {
		if ($visitor->visitThrow($this, $ctx)) {
			$visitor->accept($this->toThrow);
		}
		$visitor->endVisitThrow($this, $ctx);
	}
}

class ArrayValueCommand extends IdentityValueCommand {
	private $componentType;
	private $values = array();

	public function __construct($componentType) {
		parent::__construct();
		$this->componentType = $componentType;
	}

	public function add(ValueCommand $x) {
		array_push($this->values, $x);
	}

	public function clear() {
		$this->values = array();
	}

	public function getComponentType() {
		return $this->componentType;
	}

	public function getComponentValues() {
		return $this->values;
	}

	public function traverse(RpcCommandVisitor $visitor, Context $ctx) {
		if ($visitor->visitArray($this, $ctx)) {
			$visitor->acceptArray($this->values);
		}
		$visitor->endVisitArray($this, $ctx);
	}
}

class EnumValueCommand extends IdentityValueCommand {

	private $value;
	private $clazz;
	
	public function __construct(Clazz $clazz) {
		parent::__construct();
		$this->clazz = $clazz;
	}

	public function getValue() {
		return $this->value;
	}

	public function setValue($value) {
		$this->value = $value;
	}
	
	public function getClass() {
		return $this->clazz;
	}

	public function traverse(RpcCommandVisitor $visitor, Context $ctx) {
		$visitor->visitEnum($this, $ctx);
		$visitor->endVisitEnum($this, $ctx);
	}

}

class InstantiateCommand extends IdentityValueCommand implements HasSetters, HasTargetClass {

	private $clazz;
	private $setters = array();

	public function __construct(Clazz $clazz) {
		parent::__construct();
		$this->clazz = $clazz;
	}

	public function clear() {
		$this->setters = array();
	}

	public function getSetters() {
		return $this->setters;
	}

	public function getTargetClass() {
		return $this->clazz;
	}

	public function set(Clazz $fieldDeclClass, $fieldName, ValueCommand $value) {
		array_push($this->setters, new SetCommand($fieldDeclClass, $fieldName, $value));
	}

	public function traverse(RpcCommandVisitor $visitor, Context $ctx) {
		if ($visitor->visitInstantiate($this, $ctx)) {
			$visitor->acceptArray($this->setters);
		}
		$visitor->endVisitInstantiate($this, $ctx);
	}

}

class InvokeCustomFieldSerializerCommand extends IdentityValueCommand implements HasSetters, HasTargetClass, HasValues {
	private $manuallySerializedType;
	private $serializer;
	private $setters = array();
	private $instanciatedType;
	private $values = array();

	public function __construct(Clazz $instantiatedType, Clazz $serializer, Clazz $manuallySerializedType) {
		parent::__construct();
		$this->instanciatedType = $instantiatedType;
		$this->serializer = $serializer;
		$this->manuallySerializedType = $manuallySerializedType;
	}

	public function addValue(ValueCommand $value) {
		array_push($this->values, $value);
	}

	public function clear() {
		$this->values = array();
	}

	public function getManuallySerializedType() {
		return $this->manuallySerializedType;
	}

	public function getSerializerClass() {
		return $this->serializer;
	}

	public function getSetters() {
		return $this->setters;
	}

	public function getTargetClass() {
		return $this->instanciatedType;
	}

	public function getValues() {
		return $this->values;
	}

	public function set(Clazz $fieldDeclClass, $fieldName, ValueCommand $value) {
		array_push($this->setters, new SetCommand($fieldDeclClass, $fieldName, $value));
	}

	public function traverse(RpcCommandVisitor $visitor, Context $ctx) {
		if ($visitor->visitInvoke($this, $ctx)) {
			$visitor->acceptArray($this->values);
			$visitor->acceptArray($this->setters);
		}
		$visitor->endVisitInvoke($this, $ctx);
	}
}

class BooleanValueCommand extends ScalarValueCommand {

	private $value;

	public function __construct($value) {
		$this->value = (bool) $value;
	}

	public function getValue() {
		return $this->value;
	}

	public function traverse(RpcCommandVisitor $visitor, Context $ctx) {
		$visitor->visitBoolean($this, $ctx);
		$visitor->endVisitBoolean($this, $ctx);
	}

}

class ByteValueCommand extends ScalarValueCommand {

	private $value;

	public function __construct($value) {
		$this->value = (int) $value;
	}

	public function getValue() {
		return $this->value;
	}

	public function traverse(RpcCommandVisitor $visitor, Context $ctx) {
		$visitor->visitByte($this, $ctx);
		$visitor->endVisitByte($this, $ctx);
	}

}

class CharValueCommand extends ScalarValueCommand {

	private $value;

	public function __construct($value) {
		$this->value = (string) $value;
		if (mb_strlen($this->value) > 1)
			$this->value = mb_strimwidth($this->value, 0, 1);
	}

	public function getValue() {
		return $this->value;
	}

	public function traverse(RpcCommandVisitor $visitor, Context $ctx) {
		$visitor->visitChar($this, $ctx);
		$visitor->endVisitChar($this, $ctx);
	}

}

class DoubleValueCommand extends ScalarValueCommand {

	private $value;

	public function __construct($value) {
		$this->value = (double) $value;
	}

	public function getValue() {
		return $this->value;
	}

	public function traverse(RpcCommandVisitor $visitor, Context $ctx) {
		$visitor->visitDouble($this, $ctx);
		$visitor->endVisitDouble($this, $ctx);
	}

}

class FloatValueCommand extends ScalarValueCommand {

	private $value;

	public function __construct($value) {
		$this->value = (float) $value;
	}

	public function getValue() {
		return $this->value;
	}

	public function traverse(RpcCommandVisitor $visitor, Context $ctx) {
		$visitor->visitFloat($this, $ctx);
		$visitor->endVisitFloat($this, $ctx);
	}

}

class IntValueCommand extends ScalarValueCommand {

	private $value;

	public function __construct($value) {
		$this->value = (int) $value;
	}

	public function getValue() {
		return $this->value;
	}

	public function traverse(RpcCommandVisitor $visitor, Context $ctx) {
		$visitor->visitInt($this, $ctx);
		$visitor->endVisitInt($this, $ctx);
	}

}

class LongValueCommand extends ScalarValueCommand {

	private $value;

	public function __construct($value) {
		$this->value = (float) $value;
	}

	public function getValue() {
		return $this->value;
	}

	public function traverse(RpcCommandVisitor $visitor, Context $ctx) {
		$visitor->visitLong($this, $ctx);
		$visitor->endVisitLong($this, $ctx);
	}

}

class ShortValueCommand extends ScalarValueCommand {

	private $value;

	public function __construct($value) {
		$this->value = (int) $value;
	}

	public function getValue() {
		return $this->value;
	}

	public function traverse(RpcCommandVisitor $visitor, Context $ctx) {
		$visitor->visitShort($this, $ctx);
		$visitor->endVisitShort($this, $ctx);
	}

}

class NullValueCommand extends ScalarValueCommand {

	private static $instance = null;

	public static function INSTANCE() {
		if (self::$instance == null)
		self::$instance = new NullValueCommand();
		return self::$instance;
	}

	public function getValue() {
		return null;
	}

	public function traverse(RpcCommandVisitor $visitor, Context $ctx) {
		$visitor->visitNull($this, $ctx);
		$visitor->endVisitNull($this, $ctx);
	}

}

class StringValueCommand extends ScalarValueCommand {

	private $value;

	public function __construct($value) {
		$this->value = (string) $value;
	}

	public function getValue() {
		return $this->value;
	}
	
	public function getValueLength() {
		return mb_strlen($this->value);
	}

	public function traverse(RpcCommandVisitor $visitor, Context $ctx) {
		$visitor->visitString($this, $ctx);
		$visitor->endVisitString($this, $ctx);
	}
	
	public function __toString() {
		return parent::__toString() . '{' . $this->value . '}';
	}

}

class HasValuesCommandSink extends CommandSink {
	
	private $container;
	
	public function __construct(HasValues $container) {
		$this->container = $container;
	}
	
	public function accept(RpcCommand $command) {
		if (!($command instanceof ValueCommand)) {
			throw new SerializationException(Classes::classOf($command)->getName());
		}
		$this->container->addValue($command);
	}
	
	public function finish() {
	}
	
}
