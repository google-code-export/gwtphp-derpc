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

interface Magic {
	function getMagicClassName();
	function getMagicClassFullName();
}

abstract class Clazz {
	protected $name;
	
	public function __construct($name) {
		$this->name = $name;
	}

	public function getName() {
		return $this->name;
	}
	
	public abstract function getFields();
	public abstract function getDeclaredFields();
	public abstract function hasField($fieldName);
	public abstract function hasDeclaredField($fieldName);
	public abstract function getField($fieldName);
	public abstract function getDeclaredField($fieldName);
	
	public abstract function getSuperClass();
	public abstract function getMethods();
	public abstract function getMethod($methodName);
	public abstract function hasMethod($methodName);
	
	public abstract function getConstructor();
	
	public abstract function newInstance();
	public abstract function newInstanceArgs(array $args);
	
	public function getEnclosingClass() {
		return null;
	}
	
	public function getPackage() {
		return '';
	}
	
	public function getFullName() {
		return $this->name;
	}
	
	public function getComponentType() {
		return null;
	}
	
	public function isArray() {
		return false;
	}
	
	public function isEnum() {
		return false;
	}
	
	public function getConstantNameByValue($value) {
		return null;
	}
	
	public function getEnumValues() {
		return array();
	}
	
	public function isInterface() {
		return false;
	}
	
	public function getInterfaces() {
		return array();
	}
	
	public abstract function hasGWTName();
	public abstract function getGWTName();
	
	public function isPrimitive() {
		return false;
	}
	
	public function isAssignableFrom(Clazz $clazz) {
		return $clazz === $this;
	}
	
	public function implementsInterface(Clazz $interface) {
		return false;
	}
	
	public function subClassOf(Clazz $clazz) {
		return false;
	}
	
	public static function clazz() {
		return Classes::classOf(get_called_class());
	}
}

class JavaClazz extends Clazz {
	protected $fields = array();
	protected $super = null;
	protected $methods = array();
	protected $compatibleClasses = array();
	
	public function __construct($name, Clazz $super = null) {
		parent::__construct($name);
		$this->super = $super;
	}
	
	public function getFullName() {
		$classname = $this->name;
		return $classname::CLASSNAME;
	}
	
	public function getPackage() {
		$fullname = $this->getFullName();
		$pos = mb_strrpos($fullname, '.');
		if ($pos != false) {
			return mb_substr($fullname, 0, $pos);
		}
		else {
			return '';
		}
	}
	
	public function getFields() {
		return $this->fields;
	}
	
	public function getDeclaredFields() {
		return $this->fields;
	}
	
	public function getMethods() {
		return $this->methods;
	}
	
	public function getMethod($methodName) {
		return null;
	}
	
	public function hasMethod($methodName) {
		return false;
	}
	
	public function getConstructor() {
		return null;
	}
	
	public function hasField($fieldName) {
		return in_array($fieldName, $this->fields);
	}
	
	public function hasDeclaredField($fieldName) {
		return $this->hasField($fieldName);
	}
	
	public function getField($fieldName) {
		return null;
	}
	
	public function getDeclaredField($fieldName) {
		return null;
	}
	
	public function getSuperClass() {
		return $this->super;
	}
	
	public function newInstance() {
		return null;
	}
	
	public function newInstanceArgs(array $args) {
		return null;
	}
	
	public function hasGWTName() {
		return false;
	}
	
	public function getGWTName() {
		return '';
	}
	
	public function isAssignableFrom(Clazz $clazz) {
		return parent::isAssignableFrom($clazz) ||
			in_array($clazz, $this->compatibleClasses, true);
	}
	
	public function registerAssignableFrom(array $classes) {
		foreach ($classes as $cls) {
			if (!in_array($cls, $this->compatibleClasses, true)) {
				$this->compatibleClasses[] = $cls;
			}
		}
	}
	
	public function __toString() {
		return '{jclass ' . $this->name . '}';
	}

}

class JavaPrimitiveClazz extends JavaClazz {
	
	private $signature;
	
	public function __construct($name, $signature) {
		parent::__construct($name, null);
		$this->signature = $signature;
	}
	
	public function isPrimitive() {
		return true;
	}
	
	public function getFullName() {
		return $this->name;
	}
	
	public function getSignature() {
		return $this->signature;
	}
	
	public function __toString() {
		return '{jprimitive ' . $this->name . '}';
	}
	
}

class AliasClass extends Clazz {
	
	private $clazz;
	private $newName;
	
	public function __construct($clazz, $newName) {
		parent::__construct($newName);
		$this->clazz = $clazz;
	}
	
	public function getFields() {
		return $this->clazz->getFields();
	}
	
	public function hasField($fieldName) {
		return $this->clazz->hasField($fieldName);
	}
	
	public function getField($fieldName) {
		return $this->clazz->getField($fieldName);
	}
	
	public function getDeclaredFields() {
		return $this->clazz->getDeclaredFields();
	}
	
	public function hasDeclaredField($fieldName) {
		return $this->clazz->hasDeclaredField($fieldName);
	}
	
	public function getDeclaredField($fieldName) {
		return $this->clazz->getDeclaredField($fieldName);
	}
	
	public function getSuperClass() {
		return $this->clazz->getSuperClass();
	}
	
	public function getMethods() {
		return $this->clazz->getMethods();
	}
	
	public function getMethod($methodName) {
		return $this->clazz->getMethod($methodName);
	}
	
	public function hasMethod($methodName) {
		return $this->clazz->hasMethod($methodName);
	}
	
	public function getConstructor() {
		return $this->clazz->getConstructor();
	}
	
	public function newInstance() {
		return $this->clazz->newInstance();
	}
	
	public function newInstanceArgs(array $args) {
		return $this->clazz->newInstanceArgs($args);
	}
	
	public function getEnclosingClass() {
		return $this->clazz->getEnclosingClass();
	}
	
	public function getPackage() {
		return $this->clazz->getPackage();
	}
	
	public function getFullName() {
		return $this->clazz->getFullName();
	}
	
	public function getComponentType() {
		return $this->clazz->getComponentType();
	}
	
	public function isArray() {
		return $this->clazz->isArray();
	}
	
	public function isEnum() {
		return $this->clazz->isEnum();
	}
	
	public function getConstantNameByValue($value) {
		return $this->clazz->getConstantNameByValue($value);
	}
	
	public function getEnumValues() {
		return $this->clazz->getEnumValues();
	}
	
	public function isInterface() {
		return $this->clazz->isInterface();
	}
	
	public function hasGWTName() {
		return $this->clazz->hasGWTName();
	}
	
	public function getGWTName() {
		return $this->clazz->getGWTName();
	}
	
	public function isPrimitive() {
		return $this->clazz->isPrimitive();
	}
	
	public function isAssignableFrom(Clazz $clazz) {
		return $this->clazz->isAssignableFrom($clazz);
	}
	
	public function implementsInterface(Clazz $interface) {
		return $this->clazz->implementsInterface($interface);
	}
	
	public function subClassOf(Clazz $clazz) {
		return $this->clazz->subClassOf($clazz);
	}
	
	public function __toString() {
		return '{alias ' . $this->getName() . '}';
	}

}

class PhpClazz extends Clazz {
	
	protected $reflect;
	protected $fields = null;
	protected $declaredFields = null;
	private $methods = array();
	private $constructor = null;
	
	private $gwtname = '';
	private $enclosing = null;
	
	public function __construct($name) {
		if (!class_exists($name) && !interface_exists($name))
			throw new ClassException('php class "' . $name . '" does not exists');
		parent::__construct($name);
		$this->reflect = new ReflectionClass($name);
		if ($this->reflect->getName() !== $name)
			throw new ClassException('php class "' . $name . '" does not exists');
		$this->init();
	}
	
	protected function init() {
		// gwtname
		$doc = $this->reflect->getDocComment();
		$found = preg_match_all('/@gwtname[\s]([\w]+([.$_][\w]+)*)/', $doc, $matches, PREG_SET_ORDER);
		if ($found ==1) {
			$this->gwtname = $matches[0][1];
		}
		
		// Enclosing
		$found = preg_match_all('/@enclosing[\s]([\w]+([.$_][\w]+)*)/', $doc, $matches, PREG_SET_ORDER);
		if ($found ==1) {
			$this->enclosing = Classes::classOf($matches[0][1]);
		}
	}
	
	protected function createFields() {
		$fields = array();
		$decl = array();
		foreach ($this->reflect->getProperties() as $prop) {
			if ($prop->getDeclaringClass()->getShortName() !== $this->reflect->getShortName()) {
				$field = $this->getSuperClass()->getField($prop->getName());
			}
			else {
				$field = new PhpField($this, $prop);
				$decl[$prop->getName()] = $field;
			}
			$fields[$prop->getName()] = $field;
		}
		return array($fields, $decl);
	}
	
	private function checkFields() {
		if (is_null($this->fields)) {
			list($this->fields, $this->declaredFields) = $this->createFields();
		}
	}
	
	public function getFullName() {
		if (empty($this->gwtname))
			return parent::getFullName();
		else
			return $this->gwtname;
	}
	
	public function getPackage() {
		if (empty($this->gwtname)) {
			return self::getPackage();
		}
		else {
			$gwtname = $this->gwtname;
			$pos = mb_strpos($gwtname, '$');
			if ($pos != false) {
				$gwtname = mb_substr($gwtname, 0, $pos);
			}
			$pos = mb_strrpos($gwtname, '.');
			if ($pos != false) {
				return mb_substr($gwtname, 0, $pos);
			}
			else {
				return '';
			}
		}
	}
	
	public function getEnclosingClass() {
		return $this->enclosing;
	}
	
	public function getFields() {
		$this->checkFields();
		return $this->fields;
	}
	
	public function getDeclaredFields() {
		$this->checkFields();
		return $this->declaredFields;
	}
	
	public function hasField($fieldName) {
		return $this->reflect->hasProperty($fieldName);
	}
	
	public function hasDeclaredField($fieldName) {
		$this->checkFields();
		foreach ($this->declaredFields as $field) {
			if ($field->getName() === $fieldName) {
				return true;
			}
		}
		return false;
	}
	
	public function getField($fieldName) {
		$this->checkFields();
		if (!$this->hasField($fieldName)) {
			throw new ClassException('The field "' . $fieldName . '" does not exists in ' . $this);
		}
		return $this->fields[$fieldName];
	}
	
	public function getDeclaredField($fieldName) {
		$this->checkFields();
		if (!$this->hasDeclaredField($fieldName)) {
			throw new ClassException('The field "' . $fieldName . '" does not exists in ' . $this);
		}
		return $this->declaredFields[$fieldName];
	}
	
	public function getMethods() {
		$ms = get_class_methods($this->name);
		foreach ($ms as $m) {
			if (!isset($this->methods[$m])) {
				$this->methods[$m] = new PhpMethod($this, $m);
			}
		}
		return array_values($this->methods);
	}
	
	public function getMethod($methodName) {
		if (!method_exists($this->name, $methodName))
			return null;
		else {
			if (!isset($this->methods[$methodName])) 
				$this->methods[$methodName] = new PhpMethod($this, $methodName);
			return $this->methods[$methodName];
		}
	}
	
	public function hasMethod($methodName) {
		return method_exists($this->name, $methodName);
	}
	
	public function getConstructor() {
		if (is_null($this->constructor)) {
			$method = $this->reflect->getConstructor();
			if (is_null($method)) {
				$this->constructor = new PhpEmptyConstructor($this);
			}
			else {
				$this->constructor = new PhpConstructor($this, $method);
			}
		}
		return $this->constructor;
	}
	
	public function getSuperClass() {
		$parentName = get_parent_class($this->name);
		if ($parentName !== false) {
			return Classes::classOf($parentName);
		}
		else {
			return Object::clazz();
		}
	}
	
	public function newInstance() {
		if (func_num_args() == 0) {
			return $this->reflect->newInstance();
		}
		else {
			return $this->newInstanceArgs(func_get_args());
		}
	}
	
	public function newInstanceArgs(array $args) {
		return $this->reflect->newInstanceArgs($args);
	}
	
	public function isInterface() {
		return $this->reflect->isInterface();
	}
	
	public function getInterfaces() {
		$interfaces = $this->reflect->getInterfaces();
		$result = array();
		foreach ($interfaces as $name => $reflect) {
			$result[] = Classes::classOf($name);
		}
		return $result;
	}
	
	public function isAssignableFrom(Clazz $clazz) {
		return parent::isAssignableFrom($clazz) ||
			($this->isInterface() && $clazz->implementsInterface($this)) ||
			(!$this->isInterface() && $clazz->subClassOf($this));
	}
	
	public function __toString() {
		return '{class ' . $this->name . '}';
	}
	
	public function hasGWTName() {
		return !empty($this->gwtname);
	}
	
	public function getGWTName() {
		return $this->gwtname;
	}
	
	public function implementsInterface(Clazz $interface) {
		if (!interface_exists($interface->getName()))
			return false;
		return $this->reflect->implementsInterface($interface->getName());
	}
	
	public function subClassOf(Clazz $clazz) {
		return $this->reflect->isSubclassOf($clazz->getName());
	}
}

class PhpEnumClazz extends PhpClazz {
	
	protected function createFields() {
		list($fields, $decl) = parent::createFields();
		
		$temp = array();
		$constants = $this->getEnumValues();
		foreach ($constants as $value) {
			$temp[$value] = new VirtualField($this, $value, $this, 0, VirtualField::FLAG_STATIC);
		}
		
		$temp['ENUM$VALUES'] = new VirtualField($this, 'ENUM$VALUES', ArrayType::clazz($this), 0, VirtualField::FLAG_STATIC);
		
		return array(array_merge($fields, $temp), array_merge($decl, $temp));
	}
	
	public function isEnum() {
		return true;
	}
	
	public function getConstantNameByValue($value) {
		if (!$this->isEnum()) {
			return null;
		}
		$res = array_search($value, $this->reflect->getConstants());
		if ($res === false) {
			return null;
		}
		else {
			return $res;
		}
	}
	
	public function getEnumValues() {
		return array_values($this->reflect->getConstants());
	}
	
}

class MagicClazz extends PhpClazz {
	
	private $magicClassName;
	private $magicClassFullName;
	
	public function __construct($name, Magic $instance) {
		if (!($instance instanceof Magic)) {
			throw new ClassException('php class "' . $name . '" is not Magic implemented');
		}
		parent::__construct($name);
		$this->magicClassName = $instance->getMagicClassName();
		$this->magicClassFullName = $instance->getMagicClassFullName();
	}
	
	public function getName() {
		return $this->magicClassName;
	}
	
	public function getFullName() {
		return $this->magicClassFullName;
	}
	
}

abstract class Field {
	protected $clazz;
	
	public function __construct(Clazz $clazz) {
		$this->clazz = $clazz;
	}
	
	public function getDeclaringClass() {
		return $this->clazz;
	}
	
	public abstract function getName();
	
	public abstract function setValue($instance, $value);
	public abstract function getValue($instance);
	
	public abstract function hasGWTType();
	public abstract function getGWTType();
	
	public abstract function hasType();
	public abstract function getType();
	
	public abstract function isStatic();
	public abstract function isTransient();
	public abstract function isPrivate();
	public abstract function isProtected();
	public abstract function isPublic();
	
	public abstract function setAccessible($accessible); 
	public abstract function isAccessible();
}

class PhpField extends Field {
	private $reflect;
	private $name;
	
	private $type;
	private $transient = false;
	
	public function __construct(Clazz $clazz, $reflect) {
		parent::__construct($clazz);
		$this->reflect = $reflect;
		$this->name = $reflect->getName();
		$this->init();
	}
	
	private function init() {
		$doc = $this->reflect->getDocComment();
		$found = preg_match_all('/@var[\s]([\w\[\]]+)/', $doc, $matches, PREG_SET_ORDER);
		
		if ($found ==1) {
			$this->type = $matches[0][1];
		}
		
		$found = preg_match_all('/@transient/', $doc, $matches, PREG_SET_ORDER);
		$this->transient = $found >= 1;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setValue($instance, $value) {
		$field = $this->name;
		$instance->$field = $value;
	}
	
	public function getValue($instance) {
		$field = $this->name;
		return $instance->$field;
	}
	
	public function hasGWTType() {
		return !empty($this->type);
	}
	
	public function getGWTType() {
		return $this->type;
	}
	
	public function hasType() {
		return !empty($this->type);
	}
	
	public function getType() {
		if (!$this->hasGWTType()) {
			throw new ClassException('The field ' . 
				$this->getDeclaringClass()->getName() . '.' . $this->getName() . ' have not type information');
		}
		return Classes::classOf($this->type);
	}
	
	public function isStatic() {
		return $this->reflect->isStatic();
	}
	
	public function isTransient() {
		return $this->transient;
	}
	
	public function isPrivate() {
		return $this->reflect->isPrivate();
	}
	
	public function isProtected() {
		return $this->reflect->isProtected();
	}
	
	public function isPublic() {
		return $this->reflect->isPublic();
	}
	
	public function setAccessible($accessible) {
		$this->reflect->setAccessible($accessible);
	}
	
	public function isAccessible() {
		return $this->reflect->isPrivate() || $this->reflect->isProtected();
	}
}

class VirtualField extends Field {
	
	const FLAG_TRANSIENT = 1;
	const FLAG_STATIC = 2;
	const VISIBILITY_PUBLIC = 0;
	const VISIBILITY_PROTECTED = 1;
	const VISIBILITY_PRIVATE = 2;
	
	private $name;
	private $type;
	private $visiblity;
	private $flags;
	
	public function __construct(Clazz $clazz, $name, Clazz $type, $visibility = self::VISIBILITY_PUBLIC, $flags = 0) {
		parent::__construct($clazz);
		$this->name = $name;
		$this->type = $type;
		$this->visibility = $visibility;
		$this->flags = $flags;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setValue($instance, $value) {
	}
	
	public function getValue($instance) {
		return null;
	}
	
	public function hasGWTType() {
		return true;
	}
	
	public function getGWTType() {
		return $this->type->getName();
	}
	
	public function hasType() {
		return true;
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function isStatic() {
		return ($this->flags & self::FLAG_STATIC) != 0;
	}
	
	public function isTransient() {
		return ($this->flags & self::FLAG_TRANSIENT) != 0;
	}
	
	public function isPrivate() {
		return $this->visibility == self::VISIBLITY_PRIVATE;
	}
	
	public function isProtected() {
		return $this->visibility == self::VISIBLITY_PROTECTED;
	}
	
	public function isPublic() {
		return $this->visibility == self::VISIBLITY_PUBLIC;
	}
	
	public function setAccessible($accessible) {
	}
	
	public function isAccessible() {
		return true;
	}
}

abstract class Method {
	protected $clazz;
	protected $name;
	
	public function __construct(Clazz $clazz, $name) {
		$this->clazz = $clazz;
		$this->name = $name;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getDeclaringClass() {
		return $this->clazz;
	}
	
	public abstract function invoke();
	public abstract function invokeArgs();
	public abstract function isStatic();
	public abstract function hasReturnType();
	public abstract function getReturnType();
	public abstract function getExceptionTypes();
	
	public function __toString() {
		$result = '';
		if ($this->hasReturnType()) {
			$returnType = $this->getReturnType();
			if (!empty($returnType)) {
				$returnType = Classes::classOf($returnType)->getFullName();
			}
			$result .= $returnType . ' ';
		}
		return $result . $this->clazz->getFullName() . '.' . $this->name . '()';
	}
}

class PhpMethod extends Method {
	
	private $reflect;
	private $returnType = '';
	private $exceptionTypes = array();
	
	public function __construct(Clazz $clazz, $name) {
		parent::__construct($clazz, $name);
		$this->reflect = new ReflectionMethod($clazz->getName(), $name);
		$this->init();
	}
	
	private function init() {
		$doc = $this->reflect->getDocComment();
		$found = preg_match_all('/@return[\s]([\w\[\]]+)/', $doc, $matches, PREG_SET_ORDER);
		if ($found == 1) {
			$this->returnType = $matches[0][1];
		}
		
		$found = preg_match_all('/@throw[\s]([\w\[\]]+)/', $doc, $matches, PREG_SET_ORDER);
		if ($found >= 1) {
			foreach ($matches as $match) {
				$this->exceptionTypes[] = Classes::classOf($match[1]);
			}
		}
	}
	
	public function invoke() {
		if ($this->reflect->isStatic()) {
			return $this->reflect->invokeArgs(null, func_get_args());
		}
		else {
			$args = func_get_args();
			$instance = array_shift($args);
			return $this->reflect->invokeArgs($instance, $args);
		}
	}
	
	public function invokeArgs() {
		if ($this->reflect->isStatic()) {
			return $this->reflect->invokeArgs(null, func_get_arg(0));
		}
		else {
			return $this->reflect->invokeArgs(func_get_arg(0), func_get_arg(1));
		}
	}
	
	public function isStatic() {
		return $this->reflect->isStatic();
	}
	
	public function  hasReturnType() {
		return !empty($this->returnType);
	}
	
	public function getReturnType() {
		return $this->returnType;
	}
	
	public function getExceptionTypes() {
		return $this->exceptionTypes;
	}
	
	public function __toString() {
		$result = '';
		if ($this->reflect->isPrivate()) {
			$result .= 'private ';
		}
		else if ($this->reflect->isProtected()) {
			$result .= 'protected ';
		}
		else if ($this->reflect->isPublic()) {
			$result .= 'public ';
		}
		
		if ($this->reflect->isStatic()) {
			$result .= 'static ';
		}
		
		if ($this->reflect->isAbstract()) {
			$result .= 'abstract ';
		}
		
		if ($this->reflect->isFinal()) {
			$result .= 'final ';
		}
		
		return $result . parent::__toString();
	}
	
}

abstract class Constructor {
	protected $clazz;
	
	public function __construct(Clazz $clazz) {
		$this->clazz = $clazz;
	}
	
	public function getDeclaringClass() {
		return $this->clazz;
	}
	
	public function newInstance() {
		if (func_num_args() == 0) {
			return $this->clazz->newInstance();
		}
		else {
			return $this->newInstanceArgs(func_get_args());
		}
	}
	
	public function newInstanceArgs(array $args) {
		return $this->clazz->newInstance($args);
	}
	
	public abstract function isPrivate();
	public abstract function isProtected();
	public abstract function isPublic();
	
	public abstract function isAccessible();
	public abstract function setAccessible($accessible);
	
	public function __toString() {
		return $this->clazz->getName() . '__construct()';
	}
}

class PhpConstructor extends Constructor {
	
	private $reflect;
	
	public function __construct(Clazz $clazz, ReflectionMethod $constructor) {
		parent::__construct($clazz);
		$this->reflect = $constructor;
	}
	
	public function isAccessible() {
		return $this->reflect->isPrivate() || $this->reflect->isProtected();
	}
	
	public function setAccessible($accessible) {
		//$this->reflect->setAccessible($accessible);
	}
	
	public function isPrivate() {
		return $this->reflect->isPrivate();
	}
	
	public function isProtected() {
		return $this->reflect->isProtected();
	}
	
	public function isPublic() {
		return $this->reflect->isPublic();
	}
	
}

class PhpEmptyConstructor extends Constructor {

	public function __construct(Clazz $clazz) {
		parent::__construct($clazz);
	}

	public function isAccessible() {
		return true;
	}

	public function setAccessible($accessible) {
	}

	public function isPrivate() {
		return false;
	}

	public function isProtected() {
		return false;
	}

	public function isPublic() {
		return true;
	}

}

class ArrayClazz extends JavaClazz {
	private $dim;
	private $componentType;
	private $fullname;
	
	public function __construct(Clazz $super, $componentType, $dim) {
		parent::__construct($componentType->getName() . '[]');
		$this->dim = $dim;
		$this->componentType = $componentType;
		$this->createFullName();
	}
	
	private function createFullName() {
		if ($this->componentType->isArray()) {
			$this->fullname = '[' . $this->componentType->getFullName();
		}
		else {
			if (!$this->componentType->isPrimitive()) {
				$this->fullname = '[L' . $this->componentType->getFullName() . ';';
			}
			else {
				$this->fullname = '[' . $this->componentType->getSignature();
			}
		}
	}
	
	public function getDimension() {
		return $this->dim;
	}
	
	public function getComponentType() {
		return $this->componentType;
	}
	
	public function isArray() {
		return true;
	}
	
	public function getFullName() {
		return $this->fullname;
	}
	
	public function __toString() {
		return '{jarray ' . $this->getName() . '}';
	}
}

class ArrayType {
	
	const TYPE = 'Array';
	const SIGNATURE = '[';
	
	private function __construct() {
	}
	
	public static function newInstance() {
		$fill_value = func_get_arg(0);
	   
	    for ($arg_index = func_num_args() - 1; $arg_index >= 1; $arg_index--) {
	        $dim_size = func_get_arg($arg_index);
	        $fill_value = array_fill(0, $dim_size, $fill_value);
	    }
	   
	    return $fill_value;
	}
	
	public static function clazz($componentType, $dim = 1) {
		$className = $componentType->getName() . str_repeat('[]', $dim);
		return Classes::classOf($className);
	}
	
	public static function autoClass(array &$value) {
		$type = null;
		for ($i=0; $i<count($value); $i++) {
			$itype = Classes::classOfValue($value[$i]);
			if (is_null($type)) {
				$type = $itype;
			}
			else {
				if ($type != $itype) {
					return self::clazz(Object::clazz(), 1);
				}
			}
		}
		
		return self::clazz($type, 1);
	}
	
	public static function getLength(array &$value) {
		return count($value);
	}
	
	public static function &get(array &$value, $index) {
		return $value[$index];
	}
	
}

/** @gwtname java.lang.Enum */
class Enum {

	/** @var string */
	private $name;
	
	/** @var int */
	private $ordinal;
	
	private function __construct() {
		
	}

}

class Classes {
	
	private static $CLASSES = array();
	private static $SIGNATURES = array();
	
	private static function exists($className) {
		return isset(self::$CLASSES[$className]);
	}
	
	private static function getClass($className) {
		return self::$CLASSES[$className];
	}
	
	private static function registerClass($className, $class) {
		self::$CLASSES[$className] = $class;
	}
	
	public static function register($className, $class) {
		self::$CLASSES[$className] = $class;
	}
	
	public static function registerSignature($signature, $class) {
		self::$SIGNATURES[$signature] = $class;
	}
	
	public static function classOfValue($value) {
		$className = '';
		$type = gettype($value);
		if ($type == 'boolean') {
			$className = 'boolean';
		}
		else if ($type == 'integer') {
			$className = 'int';
		}
		else if ($type == 'double') {
			$className = 'double';
		}
		else if ($type == 'string') {
			$className = 'String';
		}
		else if ($type == 'array') {
			return ArrayType::autoClass($value);
		}
		else if ($type == 'object') {
			if ($value instanceof Magic) {
				return new MagicClazz(get_class($value), $value);
			}
			else {
				$className = get_class($value);
			}
		}
		else if ($type == 'resource') {
			throw new ClassNotFoundException('Resource have no class');
		}
		else if ($type == 'NULL') {
			$className = 'Void';
		}
		else {
			throw new ClassNotFoundException('Type of value is unknown');
		}
		
		return self::classOf($className);
	}
	
	public static function classOf($classNameOrObject) {
		$className = '';
		if (is_object($classNameOrObject)) {
			if ($classNameOrObject instanceof Magic) {
				return new MagicClazz(get_class($classNameOrObject), $classNameOrObject);
			}
			else {
				$className = get_class($classNameOrObject);
			}
		}
		else if (is_string($classNameOrObject)) {
			$className = $classNameOrObject;
		}
		else {
			debug_print_backtrace();
			throw new ClassNotFoundException('The argument of method ClassOf is not string or object');
		}
		
		/*$type = gettype($classNameOrObject);
		if ($type == 'boolean') {
			$className = 'boolean';
		}
		else if ($type == 'integer') {
			$className = 'int';
		}
		else if ($type == 'double') {
			$className = 'double';
		}
		else if ($type == 'string') {
			$className = $classNameOrObject;
		}
		else if ($type == 'array') {
			return ArrayType::autoClass($classNameOrObject);
		}
		else if ($type == 'object') {
			if ($classNameOrObject instanceof Magic) {
				return new MagicClazz(get_class($classNameOrObject), $classNameOrObject);
			}
			else {
				$className = get_class($classNameOrObject);
			}
		}
		else if ($type == 'resource') {
			return null;
		}
		else if ($type == 'NULL') {
			$className = Void;
		}
		else {
			return null;
		}*/
		
		if (self::isArrayClassName($className)) {
			return self::classOfArray($className);
		}
		
		$className = self::cleanClassName($className);
		
		/*if (strpos($className, '$') !== false) {
			$className = substr(strrchr($className, '$'), 1);
		}
		
		if (strpos($className, '.') !== false) {
			$className = substr(strrchr($className, '.'), 1);
		}*/
		
		if (!self::exists($className)) {
			$clazz = null;
			try {
				if (self::isEnumClass($className)) {
					$clazz = new PhpEnumClazz($className);
				}
				else {
					$clazz = new PhpClazz($className);
				}
			}
			catch (Exception $e) {
				throw new ClassNotFoundException('Class not found [' . $className . ']');
			}
			self::registerClass($className, $clazz);
		}
		return self::getClass($className);
	}
	
	private static function cleanClassName($className) {
		$pos = strpos($className, '<');
		if ($pos !== false) {
			$className = substr($className, 0, $pos);
		}
		
		if (strpos($className, '$') !== false) {
			$className = str_replace('$', '_', $className);
			//$className = substr(strrchr($className, '$'), 1);
		}
		
		if (strpos($className, '.') !== false) {
			$className = substr(strrchr($className, '.'), 1);
		}
		
		return $className;
	}
	
	private static function isEnumClass($className) {
		if (class_exists($className)) {
			return is_subclass_of($className, 'Enum');
		}
		else {
			return false;
		}
	}
	
	private static function isArrayClassName($className) {
		return strpos($className, '[') !== false;
	}
	
	private static function classOfArray($className) {
		// Java array style
		if ($className[0] == ArrayType::SIGNATURE) {
			if (self::exists($className)) {
				return self::getClass($className);
			}
			else {
				$count = substr_count($className, '[');
				$type = mb_substr($className, $count);
				if ($type[0] == Object::SIGNATURE) {
					$clazz = Classes::classOf(mb_substr($type, 1, mb_strlen($type) - 2));
				}
				else {
					$clazz = self::$SIGNATURES[$type[0]];
				}
				$reformed = $clazz->getName() . str_repeat('[]', $count);
				$wanted = self::classOfArray($reformed);
				self::registerClass($className, $wanted);
				return $wanted;
			}
		}
		else {
			$count = substr_count($className, '[');
			$pos = strpos($className, '[');
			$type = substr($className, 0, $pos);
			$className = $type . str_repeat('[]', $count);
			
			if (self::exists($className)) {
				return self::getClass($className);
			}
			else {
				$compType = $type . str_repeat('[]', $count - 1);
				$class = new ArrayClazz(Object::clazz(), Classes::classOf($compType), $count);
				self::registerClass($class->getName(), $class);
				return $class;
			}
		}
	}
	
	public static function printClasses() {
		foreach (self::$CLASSES as $className => $clazz) {
			echo $className . ' => ' . $clazz . '<br />';
		}
	}
	
	public static function registerAlias($className, Clazz $existingClass) {
		if (self::exists($className)) {
			return false;
		}
		else {
			//self::registerClass($className, new AliasClass($existingClass, $className));
			self::registerClass($className, $existingClass);
			return true;
		}
	}
	
	public static function toObject($value) {
		$type = gettype($value);
		if ($type == 'boolean') {
			return new Boolean($value);
		}
		else if ($type == 'integer') {
			return new Integer($value);
		}
		else if ($type == 'double') {
			return new Double($value);
		}
		else {
			return $value;
		}
	}

}


/** @gwtname java.lang.ClassNotFoundException */
class ClassNotFoundException extends Exception {
}

class ClassException extends Exception {
}

/** @gwtname java.lang.NoSuchMethodException */
class NoSuchMethodException extends Exception {
}

/** @gwtname java.lang.NoSuchFieldException */
class NoSuchFieldException extends ClassException {
}

class NotImplemented extends Exception {
}

class NumberFormatException extends Exception {
}