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
require_once PHPRPC_ROOT . 'classes.php';
require_once PHPRPC_ROOT . 'stream.php';
require_once PHPRPC_ROOT . 'exception.php';

require_once PHPRPC_ROOT . 'EscapeUtil.php';

class BackRefAssigner extends RpcCommandVisitor {
	
	private $seenOnce;
	private $parent;
	
	public function __construct(WebModePayloadSink $parent) {
		$this->seenOne = new HashSet();
		$this->parent = $parent;
	}

	public function endVisitInvoke(InvokeCustomFieldSerializerCommand $x, Context $ctx) {
		$this->parent->makeBackRef($x);
	}

	public function endVisitLong(LongValueCommand $x, Context $ctx) {
		$this->process($x);
	}

	public function endVisitString(StringValueCommand $x, Context $ctx) {
		$this->process($x);
	}

	public function visitArray(ArrayValueCommand $x, Context $ctx) {
		return $this->process($x);
	}

	public function visitInstantiate(InstantiateCommand $x, Context $ctx) {
		return $this->process($x);
	}

	private function process(ValueCommand $x) {
		if (!$this->seenOne->add($x)) {
			$this->parent->makeBackRef($x);
			return false;
		}
		return true;
	}
	
}

class WebModePayloadVisitor extends RpcCommandVisitor {
	
	private $parent;
	
	private $constructorFunctions;
	private $commandBuffers;
	private $currentBuffer = null;
	private $stack = array();
	private $started;
	
	private $clientOracle;
	
	public function __construct(WebModePayloadSink $parent) {
		$this->parent = $parent;
		$this->constructorFunctions = new ObjectMap();
		$this->commandBuffers = new ObjectMap();
		$this->started = new HashSet();
		
		$this->clientOracle = $parent->getClientOracle();
	}

	public function endVisitBoolean(BooleanValueCommand $x, Context $ctx) {
		if ($x->getValue()) {
			$this->one();
		}
		else {
			$this->zero();
		}
	}

	public function endVisitByte(ByteValueCommand $x, Context $ctx) {
		$this->push(String::valueOf($x->getValue()));
	}

	public function endVisitChar(CharValueCommand $x, Context $ctx) {
		$this->push(String::valueOf(Character::ord($x->getValue())));
	}

	public function endVisitDouble(DoubleValueCommand $x, Context $ctx) {
		$this->push(String::valueOf($x->getValue()));
	}

	public function endVisitEnum(EnumValueCommand $x, Context $ctx) {
		$constantName = $x->getClass()->getConstantNameByValue($x->getValue());
		$fieldName = $this->clientOracle->getFieldId($x->getClass(), $constantName);
		if ($fieldName == null) {
			throw new IncompatibleRemoteServiceException('The client cannot accept ' . $constantName);
		}
		$clinitName = $this->clientOracle->getMethodId($x->getClass(), '$clinit', array());
		assert(!is_null($clinitName));
		
		// (clinit(), A)
		$this->lparen();
		$this->push($clinitName);
		$this->lparen();
		$this->rparen();
		$this->comma();
		$this->push($fieldName);
		$this->rparen();
	}

	public function endVisitFloat(FloatValueCommand $x, Context $ctx) {
		$this->push(String::valueOf($x->getValue()));
	}

	public function endVisitInt(IntValueCommand $x, Context $ctx) {
		$this->push(String::valueOf($x->getValue()));
	}

	public function endVisitLong(LongValueCommand $x, Context $ctx) {
		$fieldValue = $x->getValue();
		
		
		$l = $fieldValue & 0x3FFFFF;
		$m = ($fieldValue / 4194304) & 0x3FFFFF;
		$h = ($fieldValue / 17592186044416) & 0xFFFFF;
		
		$this->push('{l:' . $l . ',m:' . $m . ',h:' . $h . '}');
	}

	public function endVisitNull(NullValueCommand $x, Context $ctx) {
		$this->_null();
	}

	public function endVisitShort(ShortValueCommand $x, Context $ctx) {
		$this->push(String::valueOf($x->getValue()));
	}

	public function endVisitString(StringValueCommand $x, Context $ctx) {
		if ($this->parent->hasBackRef($x)) {
			if (!$this->isStarted($x)) {
				$escaped = EscapeUtil::escape($x->getValue());
				$this->push($this->beginValue($x));
				$this->eq();
				$this->quote();
				$this->push($escaped);
				$this->quote();
				$this->commit($x, false);
			}
			else {
				$this->push($this->parent->makeBackRef($x));
			}
		}
		else {
			$escaped = EscapeUtil::escape($x->getValue());
			$this->quote();
			$this->push($escaped);
			$this->quote();
		}
	}

	public function visitArray(ArrayValueCommand $x, Context $ctx) {
		$hasBackRef = $this->parent->hasBackRef($x);
		if ($hasBackRef && $this->isStarted($x)) {
			$this->push($this->parent->makeBackRef($x));
			return false;
		}
		
		// constructorFunction(x = [value,value,value])
		$currentBackRef = $this->beginValue($x);
		$this->push($this->constructorFunctionArray($x));
		$this->lparen();
		if ($hasBackRef) {
			$this->push($currentBackRef);
			$this->eq();
		}
		$this->lbracket();
		$values = $x->getComponentValues();
		for ($i=0; $i<count($values); $i++) {
			$this->accept($values[$i]);
			if ($i < count($values) - 1) {
				$this->comma();
			}
		}
		$this->rbracket();
		$this->rparen();
		$this->commit($x, false);
		if (!$hasBackRef) {
			$this->parent->forget($x);
		}
		return false;
	}

	public function visitInstantiate(InstantiateCommand $x, Context $ctx) {
		$hasBackRef = $this->parent->hasBackRef($x);
		if ($hasBackRef && $this->isStarted($x)) {
			$this->push($this->parent->makeBackRef($x));
			return false;
		}
		
		$currentBackRef = $this->beginValue($x);
		$constructorFunction = $this->constructorFunctionInstantiate($x);
		$seedName = $this->clientOracle->getSeedName($x->getTargetClass());
		
		if (is_null($seedName)) {
			throw new IncompatibleRemoteServiceException('The client cannot create type ' . $x->getTargetClass()->getName());
		}
		
		// constructorFunctionFoo(x = new Foo, field1, field2)
		$this->push($constructorFunction);
		$this->lparen();
		if ($hasBackRef) {
			$this->push($currentBackRef);
			$this->eq();
		}
		$this->_new();
		$this->push($seedName);
		foreach ($x->getSetters() as $setter) {
			$this->comma();
			$this->accept($setter->getValue());
		}
		$this->rparen();
		
		$this->commit($x, false);
		if (!$hasBackRef) {
			$this->parent->forget($x);
		}
		return false;
	}

	public function visitInvoke(InvokeCustomFieldSerializerCommand $x, Context $ctx) {
		if ($this->isStarted($x)) {
			$this->push($this->parent->makeBackRef($x));
			return false;
		}
		
		$currentBackRef = $this->beginValue($x);
		$this->lparen();
		
		$makeReader = new InstantiateCommand(Classes::classOf('CommandClientSerializationStreamReader'));
		$this->parent->makeBackRef($makeReader);
		
		$payload = new ArrayValueCommand(Classes::classOf('Object'));
		foreach ($x->getValues() as $value) {
			$payload->add($value);
		}
		$makeReader->set(Classes::classOf('CommandClientSerializationStreamReader'), 'payload', $payload);
		
		$instantiateIdent = $this->clientOracle->getMethodId($x->getSerializerClass(), 
				'instantiate', 
				array(Classes::classOf('SerializationStreamReader')));
				
		// x = new Foo,
		// x = instantiate(reader),
		$this->push($currentBackRef);
		$this->eq();
		if (is_null($instantiateIdent)) {
			// No instantiate method, we'll have to invoke the constructor
			// new Foo()
			if (is_null($x->getTargetClass()->getEnclosingClass())) {
				$constructorMethodName = $x->getTargetClass()->getName();
			}
			else {
				$name = $x->getTargetClass()->getFullName();
				$constructorMethodName = mb_substr($name, mb_strrpos($name, '.') + 1);
			}
			
			$constructorIdent = $this->clientOracle->getMethodId($x->getTargetClass(), 
					$constructorMethodName, array());
			assert(!is_null($constructorIdent));
			
			// new contructor,
			$this->_new();
			$this->push($constructorIdent);
			$this->comma();
		}
		else {
			// instantiate(reader)
			$this->push($instantiateIdent);
			$this->lparen();
			$this->accept($makeReader);
			$this->rparen();
			$this->comma();
		}
		
		$deserializeIdent = $this->clientOracle->getMethodId(
				$x->getSerializerClass(), 'deserialize',
				array(Classes::classOf('SerializationStreamReader'), $x->getManuallySerializedType()));
		if ($deserializeIdent != null) {
			// deserializer(reader, obj),
			$this->push($deserializeIdent);
			$this->lparen();
			$this->accept($makeReader);
			$this->comma();
			$this->push($currentBackRef);
			$this->rparen();
			$this->comma();
		}
		
		foreach ($x->getSetters() as $setter) {
			$this->accept($setter);
			$this->comma();
		}
		
		$this->push($currentBackRef);
		$this->rparen();
		$this->commit($x, false);
		$this->parent->forget($makeReader);
		
		return false;
	}

	public function visitReturn(ReturnCommand $x, Context $ctx) {
		$values = $x->getValues();
		$size = count($values);
		
		$this->beginRpc($x);
		$this->_return();
		
		// return [a,b,c];
		$this->lbracket();
		for ($i=0; $i<$size; $i++) {
			$this->accept($values[$i]);
			if ($i < $size - 1) {
				$this->comma();
			}
		}
		$this->rbracket();
		
		$this->semi();
		
		$this->commit($x);
		
		return false;
	}

	public function visitSet(SetCommand $x, Context $ctx) {
		$fieldName = $this->clientOracle->getFieldId($x->getFieldDeclClass(), $x->getField());
		
		if (is_null($fieldName)) {
			throw new IncompatibleRemoteServiceException('The client does not have field ' . $x->getField() . ' in type ' . $x->getFieldDeclClass().getFullName());
		}
		
		// i[3].foo = bar
		$this->push($this->parent->makeBackRef(array_peek($this->stack)));
		$this->dot();
		$this->push($fieldName);
		$this->eq();
		$this->accept($x->getValue());
		
		return false;
	}
	
	public function visitThrow(ThrowCommand $x, Context $ctx) {
		// throw foo;
		$this->beginRpc($x);
		$this->_throw();
		
		assert(count($x->getValues()) == 1);
		$this->acceptArray($x->getValues());
		
		$this->semi();
		$this->commit($x);
		
		return false;
	}
	
	private function _new() {
		$this->push(WebModePayloadSink::NEW_BYTES);
	}
	
	private function _null() {
		$this->push(WebModePayloadSink::NULL_BYTES);
	}
	
	private function _return() {
		$this->push(WebModePayloadSink::RETURN_BYTES);
	}
	
	private function _throw() {
		$this->push(WebModePayloadSink::THROW_BYTES);
	}
	
	private function beginRpc(RpcCommand $x) {
		assert(!$this->commandBuffers->containsKey($x));
		
		$this->started->add($x);
		array_push($this->stack, $x);
		$this->currentBuffer = new StringBuffer();
		$this->commandBuffers->put($x, $this->currentBuffer);
	}
	
	private function beginValue(ValueCommand $x) {
		$this->beginRpc($x);
		return $this->parent->makeBackRef($x);
	}
	
	private function comma() {
		$this->push(WebModePayloadSink::COMMA_BYTES);
		$this->spaceOpt();
	}
	
	private function commit(RpcCommand $x, $send = true) {
		if (array_pop($this->stack) !== $x) {
			throw new IllegalStateException('Did not pop expected command');
		}
		
		$x->clear();
		
		$sb = $this->commandBuffers->remove($x);
		assert(!is_null($sb));
		
		if (!empty($this->stack)) {
			$this->currentBuffer = $this->commandBuffers->get(array_peek($this->stack));
			assert(!is_null($this->currentBuffer));
		}
		else {
			$this->currentBuffer = null;
		}
		
		if ($send) {
			try {
				$this->parent->send($sb);
			}
			catch (SerializationException $e) {
				error_log($e->getMessage());
				exit(-1);
			}
		}
		else {
			$this->pushBuffer($sb);
		}
	}
	
	private function constructorFunctionArray(ArrayValueCommand $x) {
		$targetClass = ArrayType::clazz($x->getComponentType());
		
		$functionName = $this->constructorFunctions->get($targetClass);
		if (!is_null($functionName)) {
			return $functionName;
		}
		
		$initValuesId = $this->clientOracle->getMethodIdByClassName(
			'com.google.gwt.lang.Array', 'initValues', 
			array('Ljava/lang/Class;', 'Lcom/google/gwt/core/client/JavaScriptObject;', 'I', 'Lcom/google/gwt/lang/Array;'));
		assert(!is_null($initValuesId));
		
		$classLitId = $this->clientOracle->getFieldIdByClassName(
			'com.google.gwt.lang.ClassLiteralHolder', $this->getJavahSignatureName($x->getComponentType()) . '_classLit');
		assert(!is_null($classLitId));
		
		$functionName = $this->clientOracle->createUnusedIdent($classLitId);
		$this->constructorFunctions->put($targetClass, $functionName);
		
		$castableTypeData = $this->clientOracle->getCastableTypeData($targetClass);
		if (is_null($castableTypeData)) {
			$castableTypeData = $this->clientOracle->getCastableTypeData(Classes::classOf('Object[]'));
		}
		
		$queryId = $this->clientOracle->getQueryId($x->getComponentType());
		if ($queryId == 0) {
			$queryId = $this->clientOracle->getQueryId(Classes::classOf('Object'));
		}
		
		$ident = '_0';
		
		// function foo(_0) {return initValues(classLid, castableTypeData, queryId, _0)}
		$this->_function();
      	$this->push($functionName);
		$this->lparen();
		$this->push($ident);
		$this->rparen();
		$this->lbrace();
		$this->_return();
		$this->push($initValuesId);
		$this->lparen();
		$this->push($classLitId);
		$this->comma();
		$this->push($castableTypeData->toJs());
		$this->comma();
		$this->push(String::valueOf($queryId));
		$this->comma();
		$this->push($ident);
		$this->rparen();
		$this->rbrace();

		$this->flush($x);
		
		return $functionName;
	}
	
	private function constructorFunctionInstantiate(InstantiateCommand $x) {
		$targetClass = $x->getTargetClass();
		$functionName = $this->constructorFunctions->get($targetClass);
		if (!is_null($functionName)) {
			return $functionName;
		}
		
		//echo '[constructor ' . $targetClass->getFullName() . ']';
		
		$seedName = $this->clientOracle->getSeedName($targetClass);
		assert(!is_null($seedName));
		$functionName = $this->clientOracle->createUnusedIdent($seedName);
		$this->constructorFunctions->put($targetClass, $functionName);
		$setters = $x->getSetters();
		$idents = array_new(count($setters) + 1, '');
		for ($i=0, $j=count($idents); $i < $j; $i++) {
			$idents[$i] = '_' . $i;
		}
		
		// function foo(_0, _1, _2) {_0.a = _1; _0.b=_2; return _0}
		$this->_function();
		$this->push($functionName);
		$this->lparen();
		for ($i=0, $j=count($idents); $i<$j; $i++) {
			$this->push($idents[$i]);
			if ($i < $j - 1) {
				$this->comma();
			}
		}
		$this->rparen();
		$this->lbrace();
		$this->newlineOpt();
		for ($i=1, $j=count($idents); $i<$j; $i++) {
			$setter = $setters[$i - 1];
			$fieldIdent = $this->clientOracle->getFieldId($setter->getFieldDeclClass(), $setter->getField());
			
			// _0.foo = bar;
			$this->spaceOpt();
			$this->push($idents[0]);
			$this->dot();
			$this->push($fieldIdent);
			$this->eq();
			$this->push($idents[$i]);
			$this->semi();
		}
		$this->spaceOpt();
		$this->_return();
		$this->push($idents[0]);
		$this->rbrace();
		$this->newlineOpt();
		
		$this->flush($x);
		
		return $functionName;
	}
	
	private function dot() {
		$this->push(WebModePayloadSink::DOT_BYTES);
	}
	
	private function eq() {
		$this->spaceOpt();
		$this->push(WebModePayloadSink::EQ_BYTES);
		$this->spaceOpt();
	}
	
	private function flush(RpcCommand $x) {
		$sb = $this->commandBuffers->get($x);
		if (is_null($sb) || ($sb->getPosition() == 0)) {
			return;
		}
		
		try {
			$this->parent->send($sb);
		}
		catch (SerializationException $e) {
			error_log($e->getMessage());
			exit(-1);
		}
		$sb->clear();
	}
	
	private function _function() {
		$this->newlineOpt();
		$this->push(WebModePayloadSink::FUNCTION_BYTES);
	}
	
	private function getJavahSignatureName(Clazz $clazz) {
		if ($clazz->isArray()) {
			$leafType = $clazz;
			$dims = 0;
			do {
				$dims++;
				$leafType = $leafType->getComponentType();
			} while (!is_null($leafType->getComponentType()));
			assert($dims > 0);
			
			$s = $this->getJavahSignatureName($leafType);
			for ($i=0; $i < $dims; ++$i) {
				$s = '_3' . $s;
			}
			return $s;
		}
		else if ($clazz->isPrimitive()) {
			return WebModeClientOracle::jsniName($clazz);
		}
		else {
			$name = $clazz->getFullName();
			$name = str_replace('_', '_1', $name);
			$name = str_replace('.', '_', $name);
			return 'L' . $name . '_2';
		}
	}
	
	private function isStarted(RpcCommand $x) {
		return $this->started->contains($x);
	}
	
	private function lbrace() {
		$this->push(WebModePayloadSink::LBRACE_BYTES);
	}
	
	private function lbracket() {
		$this->push(WebModePayloadSink::LBRACKET_BYTES);
	}
	
	private function lparen() {
		$this->push(WebModePayloadSink::LPAREN_BYTES);
	}
	
	private function newlineOpt() {
		$this->pushOpt(WebModePayloadSink::NEWLINE_BYTES);
	}
	
	private function one() {
		$this->push(WebModePayloadSink::ONE_BYTES);
	}
	
	private function push($data) {
		$this->currentBuffer->put($data);
	}
	
	private function pushBuffer(StringBuffer $buffer) {
		assert(!is_null($this->currentBuffer));
		$this->currentBuffer->put($buffer);
	}
	
	private function pushOpt($x) {
		if (WebModePayloadSink::PRETTY) {
			$this->push($x);
		}
	}
	
	private function quote() {
		$this->push(WebModePayloadSink::QUOTE_BYTES);
	}
	
	private function rbrace() {
		$this->push(WebModePayloadSink::RBRACE_BYTES);
	}
	
	private function rbracket() {
		$this->push(WebModePayloadSink::RBRACKET_BYTES);
	}
	
	private function rparen() {
		$this->push(WebModePayloadSink::RPAREN_BYTES);
	}
	
	private function semi() {
		$this->push(WebModePayloadSink::SEMI_BYTES);
		$this->newlineOpt();
	}
	
	private function spaceOpt() {
		$this->pushOpt(WebModePayloadSink::SPACE_BYTES);
	}
	
	private function zero() {
		$this->push(WebModePayloadSink::ZERO_BYTES);
	}
	
}

class WebModePayloadSink extends CommandSink {
	
	const PRETTY = false;
	
	const COMMA_BYTES = ",";
	const DOT_BYTES = ".";
	const EQ_BYTES = "=";
	const FUNCTION_BYTES = "function ";
	const LBRACE_BYTES = "{";
	const LBRACKET_BYTES = "[";
	const LPAREN_BYTES = "(";
	const NEW_BYTES = "new ";
	const NEWLINE_BYTES = "\n";
	const NULL_BYTES = "null";
	const ONE_BYTES = "1";
	const QUOTE_BYTES = "\"";
	const RBRACE_BYTES = "}";
	const RBRACKET_BYTES = "]";
	const RETURN_BYTES = "return ";
	const RPAREN_BYTES = ")";
	const SPACE_BYTES = " ";
	const SEMI_BYTES = ";";
	const THROW_BYTES = "throw ";
	const ZERO_BYTES = "0";
	
	private $clientOracle;
	private $finished = false;
	private $out;
	private $valueBackRefs;
	private $visitor;
	
	private $freeBackRefs = array();
	
	public function __construct(ClientOracle $clientOracle, OutputStream $out) {
		$this->valueBackRefs = new ObjectMap();
		
		$this->clientOracle = $clientOracle;
		$this->out = $out;
		
		$this->visitor = new WebModePayloadVisitor($this);
	}
	
	public function accept(RpcCommand $command) {
		if ($this->finished) {
			throw new IllegalStateException('finish() has already been called');
		}
		
		$back = new BackRefAssigner($this);
		$back->accept($command);
		
		if ($command instanceof ValueCommand) {
			$this->makeBackRef($command);
		}
		$this->visitor->accept($command);
	}
	
	public function finish() {
		$this->finished = true;
	}
	
	public function getClientOracle() {
		return $this->clientOracle;
	}
	
	public function forget(ValueCommand $x) {
		assert($this->valueBackRefs->containsKey($x));
		array_push($this->freeBackRefs, $this->valueBackRefs->remove($x));
	}
	
	public function hasBackRef(ValueCommand $x) {
		return $this->valueBackRefs->containsKey($x);
	}
	
	public function makeBackRef(ValueCommand $x) {
		$toReturn = $this->valueBackRefs->get($x);
		if (is_null($toReturn)) {
			if (empty($this->freeBackRefs)) {
				$idx = $this->valueBackRefs->size();
				$toReturn = CommandClientSerializationStreamReader::BACKREF_IDENT . '._' .
						Integer::toString($idx, Character::MAX_RADIX);
			}
			else {
				$toReturn = array_pop($this->freeBackRefs);
			}
			$this->valueBackRefs->put($x, $toReturn);
		}
		return $toReturn;
	}
	
	public function send(StringBuffer $x) {
		$this->out->write((string) $x);
	}
}

/** @gwtname com.google.gwt.rpc.client.impl.CommandClientSerializationStreamReader */
class CommandClientSerializationStreamReader {
	
	const BACKREF_IDENT = '_';
}