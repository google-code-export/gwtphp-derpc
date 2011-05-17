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

require_once PHPRPC_ROOT . 'SimplePayloadSink.php';
require_once PHPRPC_ROOT . 'ClientOracle.php';

class SimplePayloadDecoder {

	const OBFUSCATED_CLASS_REFIX = 'Class$ ';
	private static $PRIMITIVE_TYPES = array();

	public static function init() {
		// Obfuscated
		self::$PRIMITIVE_TYPES[self::OBFUSCATED_CLASS_REFIX . BOOLEAN_TYPE] = Boolean::typeClass();
		self::$PRIMITIVE_TYPES[self::OBFUSCATED_CLASS_REFIX . BYTE_TYPE] = Byte::typeClass();
		self::$PRIMITIVE_TYPES[self::OBFUSCATED_CLASS_REFIX . CHAR_TYPE] = Character::typeClass();
		self::$PRIMITIVE_TYPES[self::OBFUSCATED_CLASS_REFIX . DOUBLE_TYPE] = Double::typeClass();
		self::$PRIMITIVE_TYPES[self::OBFUSCATED_CLASS_REFIX . FLOAT_TYPE] = Float::typeClass();
		self::$PRIMITIVE_TYPES[self::OBFUSCATED_CLASS_REFIX . INT_TYPE] = Integer::typeClass();
		self::$PRIMITIVE_TYPES[self::OBFUSCATED_CLASS_REFIX . LONG_TYPE] = Long::typeClass();
		self::$PRIMITIVE_TYPES[self::OBFUSCATED_CLASS_REFIX . SHORT_TYPE] = Short::typeClass();
		self::$PRIMITIVE_TYPES[self::OBFUSCATED_CLASS_REFIX . VOID_TYPE] = Void::typeClass();

		// Regular
		self::$PRIMITIVE_TYPES[Boolean::typeClass()->getName()] = Boolean::typeClass();
		self::$PRIMITIVE_TYPES[Byte::typeClass()->getName()] = Byte::typeClass();
		self::$PRIMITIVE_TYPES[Character::typeClass()->getName()] = Character::typeClass();
		self::$PRIMITIVE_TYPES[Double::typeClass()->getName()] = Double::typeClass();
		self::$PRIMITIVE_TYPES[Float::typeClass()->getName()] = Float::typeClass();
		self::$PRIMITIVE_TYPES[Integer::typeClass()->getName()] = Integer::typeClass();
		self::$PRIMITIVE_TYPES[Long::typeClass()->getName()] = Long::typeClass();
		self::$PRIMITIVE_TYPES[Short::typeClass()->getName()] = Short::typeClass();
		self::$PRIMITIVE_TYPES[Void::typeClass()->getName()] = Void::typeClass();
	}

	private $backRefs = array();
	private $classCache = array();
	private $clientOracle;
	private $commands = array();
	private $idx;
	private $payload;
	private $toReturn;
	private $toThrow;

	public function __construct(ClientOracle $clientOracle, $payload) {
		$this->clientOracle = $clientOracle;
		$this->payload = $payload;

		while ($this->toReturn == null && $this->idx < mb_strlen($payload)) {
			$this->decodeCommand();

			if ($this->toThrow != null) {
				return;
			}
		}
	}

	public function getThrownValue() {
		return $this->toThrow;
	}

	public function getValues() {
		return $this->toReturn == null ? array() : $this->toReturn->getValues();
	}

	private function decodeCommand() {
		$command = $this->next();
		if ($command == NL_CHAR) {
			$command = $this->next();
		}
		$token = $this->token();

		switch ($command) {
			case BOOLEAN_TYPE: {
				$this->pushScalar(new BooleanValueCommand($token == '1'));
				break;
			}
			case BYTE_TYPE: {
				$this->pushScalar(new ByteValueCommand(Byte::valueOf($token)));
				break;
			}
			case CHAR_TYPE: {
				$this->pushScalar(new CharValueCommand(Character::chr(intval($token))));
				break;
			}
			case DOUBLE_TYPE: {
				$this->pushScalar(new DoubleValueCommand(Double::valueOf($token)));
				break;
			}
			case FLOAT_TYPE: {
				$this->pushScalar(new FloatValueCommand(Float::valueOf($token)));
				break;
			}
			case INT_TYPE: {
				$this->pushScalar(new IntValueCommand(Integer::valueOf($token)));
				break;
			}
			case LONG_TYPE: {
				$this->pushScalar(new LongValueCommand(Long::valueOf($token)));
				break;
			}
			case VOID_TYPE: {
				$this->pushScalar(NullValueCommand::INSTANCE());
				break;
			}
			case SHORT_TYPE: {
				$this->pushScalar(new ShortValueCommand(Short::valueOf($token)));
				break;
			}
			case STRING_TYPE: {
				// "4~abcd
				$length = Integer::valueOf($token);
				$value = $this->nextCount($length);
				if ($this->next() != RPC_SEPARATOR_CHAR) {
					throw new RuntimeException('Overran string');
				}
				$this->pushString(new StringValueCommand($value));
				break;
			}
			case ENUM_TYPE: {
				// ETypeSeedName~IOrdinal~
				$ordinal = $this->readCommand('IntValueCommand')->getValue();
				
				$clazz = $this->findClass($token);
				
				$x = new EnumValueCommand($clazz);
				$this->pushIdentity($x);
				
				$x->setValue($ordinal);
				break;
			}
			case ARRAY_TYPE: {
				// Encoded as (leafType, dimensions, length, ...)
				$leaf = $this->findClass($token);

				$numDims = $this->readCommand('IntValueCommand')->getValue();
				$clazz = null;
				if ($numDims > 1) {
					$clazz = ArrayType::clazz($leaf, $numDims);
				}
				else {
					$clazz = $leaf;
				}

				$x = new ArrayValueCommand($clazz);
				$this->pushIdentity($x);

				$length = $this->readCommand('IntValueCommand')->getValue();
				for ($i = 0; $i < $length; $i++) {
					$x->add($this->readCommand('ValueCommand'));
				}
				break;
			}
			case OBJECT_TYPE: {
				// LTypeSeedName~3... N-many setters ...
				$clazz = $this->findClass($token);
				$x = new InstantiateCommand($clazz);
				$this->pushIdentity($x);
				$this->readSetters($clazz, $x);
				break;
			}
			case INVOKE_TYPE: {
				// !TypeSeedName~Number of objects written by CFS~...CFS objects...~
				// Number of extra fields~...N-many setters...
				$clazz = $this->findClass($token);
				$serializerClass = null;

				$manualType = $clazz;
				while ($manualType != null) {
					$serializerClass = SerializabilityUtil::hasCustomFieldSerializer($manualType);
					if ($serializerClass != null) {
						break;
					}
					$manualType = $manualType->getSuperClass();
				}
				
				if ($serializerClass == null) {
					throw new IncompatibleRemoteServiceException('Class [' . $clazz->getName() . '] has no custom serializer on server');
				}

				$x = new InvokeCustomFieldSerializerCommand($clazz, $serializerClass, $manualType);
				$this->pushIdentity($x);

				$this->readFields($x);
				$this->readSetters($clazz, $x);
				break;
			}
			case RETURN_TYPE: {
				// R4~...values...
				$this->toReturn = new ReturnCommand();
				$toRead = Integer::valueOf($token);
				for ($i=0; $i<$toRead; $i++) {
					$this->toReturn->addValue($this->readCommand('ValueCommand'));
				}
				break;
			}
			case THROW_TYPE: {
				// T...value...
				$this->toThrow = $this->readCommand('ValueCommand');
				break;
			}
			case BACKREF_TYPE: {
				// @backrefNumber~
				$x = $this->backRefs[Integer::valueOf($token)];
				assert($x != null);
				array_push($this->commands, $x);
				break;
			}
			case RPC_SEPARATOR_CHAR: {
				throw new RuntimeException('Segmentation overrun at ' + $this->idx);
			}
			default: {
				throw new RuntimeException('Unknown Command ' + $command);
			}
		}
	}

	private function findClass($token) {
		if (isset($this->classCache[$token])) {
			return $this->classCache[$token];
		}

		$className = $this->clientOracle->getTypeName($token);
		if ($className == null) {
			$className = $token;
		}

		if (strstr($className, '[]') !== false) {
			$firstIndex = -1;
			$j = -1;
			$dims = 0;
			while (($j = strpos($className, '[')) !== false) {
				if ($dims++ == 0) {
					$firstIndex = $j;
				}
			}
				
			$componentType = $this->findClass(substr($className, 0, $firstIndex));
			assert($componentType != null);
			$clazz = ArrayType::clazz($componentType, $dims);
		}
		else {
			$clazz = Classes::classOf($className);
		}
		$this->classCache[$token] = $clazz;
		return $clazz;
	}

	private function next() {
		$c = mb_substr($this->payload, $this->idx++, 1);
		//$c = $this->payload[$this->idx++];

		if ($c == '\\') {
			switch (mb_substr($this->payload, $this->idx++, 1)) {
			//switch ($this->payload[$this->idx++]) {
				case '0':
					$c = '\0';
					break;
				case '!':
					$c = '|';
					break;
				case 'b':
					$c = '\b';
					break;
				case 't':
					$c = '\t';
					break;
				case 'n':
					$c = '\n';
					break;
				case 'f':
					$c = '\f';
					break;
				case 'r':
					$c = '\r';
					break;
				case '\\':
					$c = '\\';
					break;
				case '"':
					$c = '"';
					break;
				case 'u':
					$c = chr(hexdec(mb_substr($this->payload, $this->idx, 4)));
					$this->idx += 4;
					break;
				case 'x':
					$c = chr(hexdec(mb_substr($this->payload, $this->idx, 2)));
					$this->idx += 2;
					break;
				default:
					throw new RuntimeException('Unhandled escape ' . $this->payload[$this->idx]);
			}
		}
		return $c;
	}
	
	private function nextCount($count) {
		$buffer = '';
		while ($count-- > 0) {
			$buffer .= $this->next();
		}
		return $buffer;
	}
	
	private function pushIdentity(IdentityValueCommand $x) {
		array_push($this->commands, $x);
		$this->backRefs[count($this->backRefs)] = $x;
	}
	
	private function pushScalar(ScalarValueCommand $x) {
		array_push($this->commands, $x);
	}
	
	private function pushString(StringValueCommand $x) {
		array_push($this->commands, $x);
		$this->backRefs[count($this->backRefs)] = $x;
	}
	
	private function readCommand($className) {
		$this->decodeCommand();
		$value = array_pop($this->commands);
		assert($value instanceof $className);
		return $value;
	}
	
	private function readFields(InvokeCustomFieldSerializerCommand $x) {
		$length = $this->readCommand('IntValueCommand')->getValue();
		for ($i=0; $i<$length; $i++) {
			$x->addValue($this->readCommand('ValueCommand'));
		}
	}
	
	private function readSetter(Clazz $clazz, HasSetters $x) {
		if (!$this->clientOracle->isScript()) {
			$fieldDeclClassName = $this->readCommand('StringValueCommand')->getValue();
			if ($fieldDeclClassName != null) {
				$clazz = $this->findClass($fieldDeclClassName);
			}
		}
		$fieldId = $this->readCommand('StringValueCommand')->getValue();
		
		$data = $this->clientOracle->getFieldName($clazz, $fieldId);
		$value = $this->readCommand('ValueCommand');
		
		$x->set($data->class, $data->fieldName, $value);
	}
	
	private function readSetters(Clazz $clazz, HasSetters $x) {
		$length = $this->readCommand('IntValueCommand')->getValue();
		for ($i=0; $i<$length; $i++) {
			$this->readSetter($clazz, $x);
		}
	}
	
	private function token() {
		$buffer = '';
		$n = $this->next();
		while ($n != RPC_SEPARATOR_CHAR) {
			$buffer .= $n;
			$n = $this->next();
		}
		return $buffer;
	}

}
SimplePayloadDecoder::init();

