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

require_once PHPRPC_ROOT . 'ast.php';
require_once PHPRPC_ROOT . 'stream.php';

require_once PHPRPC_ROOT . 'EscapeUtil.php';

const ARRAY_TYPE = '[';
const BACKREF_TYPE = '@';
const BOOLEAN_TYPE = 'Z';
const BYTE_TYPE = 'B';
const CHAR_TYPE = 'C';
const DOUBLE_TYPE = 'D';
const ENUM_TYPE = 'E';
const FLOAT_TYPE = 'F';
const INT_TYPE = 'I';
const INVOKE_TYPE = '!';
const LONG_TYPE = 'J';
const NL_CHAR = '\n';
const OBJECT_TYPE = 'L';
const RETURN_TYPE = 'R';
const RPC_SEPARATOR_CHAR = '~';
const SHORT_TYPE = 'S';
const STRING_TYPE = '"';
const THROW_TYPE = 'T';
const VOID_TYPE = 'V';

class SimplePayloadSinkVisitor extends RpcCommandVisitor {

	private $buffer;
	private $backRefs;

	public function __construct(Appendable $buffer, Map $backRefs) {
		$this->buffer = $buffer;
		$this->backRefs = $backRefs;
	}

	public function endVisitBoolean(BooleanValueCommand $x, Context $ctx) {
		$this->appendTypedData(BOOLEAN_TYPE, $x->getValue() ? "1" : "0");
	}

	public function endVisitByte(ByteValueCommand $x, Context $ctx) {
		$this->appendTypedData(BYTE_TYPE, $x->getValue());
	}

	public function endVisitChar(CharValueCommand $x, Context $ctx) {
		$this->appendTypedData(CHAR_TYPE, Character::ord($x->getValue()));
	}

	public function endVisitDouble(DoubleValueCommand $x, Context $ctx) {
		$this->appendTypedData(DOUBLE_TYPE, $x->getValue());
	}

	public function endVisitEnum(EnumValueCommand $x, Context $ctx) {
		//ETypeSeedNameIOrdinal~
		//TODO:Enum
		if ($this->appendIdentity($x)) {
			$this->appendTypedData(ENUM_TYPE, $x->getClass()->getFullName());
			$this->appendTypedData(INT_TYPE, $x->getValue());
		}
	}

	public function endVisitFloat(FloatValueCommand $x, Context $ctx) {
		$this->appendTypedData(FLOAT_TYPE, $x->getValue());
	}

	public function endVisitInt(IntValueCommand $x, Context $ctx) {
		$this->appendTypedData(INT_TYPE, $x->getValue());
	}

	public function endVisitLong(LongValueCommand $x, Context $ctx) {
		$this->appendTypedData(LONG_TYPE, Long::toLongString($x->getValue()));
	}

	public function endVisitNull(NullValueCommand $x, Context $ctx) {
		$this->appendTypedData(VOID_TYPE, "");
	}

	public function endVisitShort(ShortValueCommand $x, Context $ctx) {
		$this->appendTypedData(SHORT_TYPE, $x->getValue());
	}

	public function endVisitString(StringValueCommand $x, Context $ctx) {
		// "4~abcd
		if ($this->appendIdentity($x)) {
			$value = $x->getValue();
			$this->appendTypedData(STRING_TYPE, $x->getValueLength());
			$this->append($value);
		}
	}

	public function visitArray(ArrayValueCommand $x, Context $ctx) {
		/*
		 * Encoded as (leafType, dimensions, length, .... )
		 *
		 * Object[] foo = new Object[3];
		 *
		 * becomes
		 *
		 * [ObjectSeedname~1~3~@....~@....~@...~
		 *
		 * Object[][] foo = new Object[3][];
		 *
		 * becomes
		 *
		 * [ObjectSeedName~2~3~...three one-dim arrays...
		 */
		//TODO:Array
		if ($this->appendIdentity($x)) {
			$dims = 1;
			$leaf = $x->getComponentType();
			while ($leaf->getComponentType() != null) {
				$dims++;
				$leaf = $leaf->getComponentType();
			}
				
			$this->appendTypedData(ARRAY_TYPE, $leaf->getFullName());
			$this->accept(new IntValueCommand($dims));
			$this->accept(new IntValueCommand(count($x->getComponentValues())));
			return true;
		}
		else
			return false;
	}

	public function visitInstantiate(InstantiateCommand $x, Context $ctx) {
		// @TypeSeedName~3~... N-many setters ...
		if ($this->appendIdentity($x)) {
			$this->appendTypedData(OBJECT_TYPE, $x->getTargetClass()->getFullName());
			$this->accept(new IntValueCommand(count($x->getSetters())));
			return true;
		}
		else
		return false;
	}

	public function visitInvoke(InvokeCustomFieldSerializerCommand $x, Context $ctx) {
		// !TypeSeedName~Number of objects written by CFS~...CFS objects...~
		// Number of extra fields~...N-many setters...
		if ($this->appendIdentity($x)) {
			$this->appendTypedData(INVOKE_TYPE, $x->getTargetClass()->getFullName());
			$this->accept(new IntValueCommand(count($x->getValues())));
			$this->acceptArray($x->getValues());
			$this->accept(new IntValueCommand(count($x->getSetters())));
			$this->acceptArray($x->getSetters());
			return false;
		}
		else
		return false;
	}

	public function visitReturn(ReturnCommand $x, Context $ctx) {
		// R4~...values...
		$this->appendTypedData(RETURN_TYPE, count($x->getValues()));
		return true;
	}

	public function visitSet(SetCommand $x, Context $ctx) {
		/*
		 * In Development Mode, the field's declaring class is written to the
		 * stream to handle field shadowing. In Production Mode, this isn't
		 * necessary because all field names are allocated in the same "object"
		 * scope.
		 *
		 * DeclaringClassName~FieldName~...value...
		 */
		// Only in byteCode here, no script mode
		$this->accept(new StringValueCommand($x->getFieldDeclClass()->getFullName()));
		$this->accept(new StringValueCommand($x->getField()));
		return true;
	}

	public function visitThrow(ThrowCommand $x, Context $ctx) {
		// T...value...
		$this->appendTypedData(THROW_TYPE, "");
		return true;
	}

	private function append($x) {
		$this->buffer->append(EscapeUtil::escape($x))->append(RPC_SEPARATOR_CHAR);
	}

	private function appendIdentity(ValueCommand $x) {
		$backRef = $this->backRefs->get($x);
		if ($backRef != null) {
			if (SimplePayloadSink::PRETTY) {
				$this->buffer->append(NL_CHAR);
			}
			$this->append(BACKREF_TYPE . $backRef);
			return false;
		}
		else {
			$this->backRefs->put($x, $this->backRefs->size());
			return true;
		}
	}

	private function appendTypedData($type, $value) {
		if (SimplePayloadSink::PRETTY) {
			$this->buffer->append(NL_CHAR);
		}
		$this->buffer->append($type)->append($value)->append(RPC_SEPARATOR_CHAR);
	}

}

class SimplePayloadSink extends CommandSink {

	const PRETTY = false;
	
	private $backRefs;
	private $buffer;
	
	public function __construct(Appendable $buffer) {
		$this->buffer = $buffer;
		$this->backRefs = new ObjectMap();
	}
	
	public function accept(RpcCommand $command) {
		$visitor = new SimplePayloadSinkVisitor($this->buffer, $this->backRefs);
		$visitor->accept($command);
	}
	
	public function finish() {
	}
	
	public function getBackRefs() {
		return $this->backRefs;
	}

}