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
require_once PHPRPC_ROOT . 'derpc/rpcphptools.php';

class SerializabilityUtil {
	
	const JRE_SERIALIZER_PACKAGE = 'com.google.gwt.user.client.rpc.core';
	
	private static $classSerializableFieldsCache;
	private static $classCustomSerializerCache;
	
	public static function init() {
		self::$classSerializableFieldsCache = new IdentityHashMap();
		self::$classCustomSerializerCache = new IdentityHashMap();
	}
	
	public static function hasCustomFieldSerializer(Clazz $instanceType) {
		assert($instanceType != null);
		if ($instanceType->isArray()) {
			return null;
		}
		
		$result = self::$classCustomSerializerCache->get($instanceType);
		if ($result == null) {
			$result = self::computeHasCustomFieldSerializer($instanceType);
			if ($result == null) {
				$result = $instanceType;
			}
			self::$classCustomSerializerCache->put($instanceType, $result);
		}
		return ($result === $instanceType) ? null : $result;
	}
	
	public static function applyFieldSerializationPolicy(Clazz $clazz) {
		$serializableFields = self::$classSerializableFieldsCache->get($clazz);
		if (is_null($serializableFields)) {
			$fieldList = array();
			$fields = $clazz->getDeclaredFields();
			foreach ($fields as $field) {
				if (self::fieldQualifiesForSerialization($field)) {
					$fieldList[] = $field;
				}
			}
			$serializableFields = $fieldList;
			
			// sort the fields by name
			//usort($serializableFields, array('SerializabilityUtil', 'fieldComparator'));
			usort($serializableFields, array('self', 'fieldComparator'));
			
			self::$classSerializableFieldsCache->put($clazz, $serializableFields);
		}
		return $serializableFields;
	}
	
	private static function fieldQualifiesForSerialization(Field $field) {
		if ($field->getDeclaringClass() === Classes::classOf('Throwable')) {
			if ($field->getName() === 'detailMessage') {
				assert (self::isNotStaticTransientOrFinal($field));
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return self::isNotStaticTransientOrFinal($field);
		}
	}
	
	private static function isNotStaticTransientOrFinal(Field $field) {
		return (!$field->isStatic() && !$field->isTransient());
	}
	
	private static function computeHasCustomFieldSerializer(Clazz $instanceType) {
		assert($instanceType != null);
		
		$qualifiedTypeName = $instanceType->getName();
		
		$simpleSerializerName = $qualifiedTypeName . '_CustomFieldSerializer';
		$customSerializer = self::getCustomFieldSerializer($simpleSerializerName);
		if ($customSerializer != null) {
			return $customSerializer;
		}
		
		$customSerializerClass = self::getCustomFieldSerializer(self::JRE_SERIALIZER_PACKAGE . '.' . $simpleSerializerName);
		if (!is_null($customSerializerClass)) {
			return $customSerializerClass;
		}
		
		return null;
	}
	
	private static function getCustomFieldSerializer($qualifiedSerializerName) {
		try {
			return Classes::classOf($qualifiedSerializerName);
		}
		catch (ClassNotFoundException $e) {
			return null;
		}
	}
	
	public static function fieldComparator(Field $f1, Field $f2) {
		return strcmp($f1->getName(), $f2->getName());
	}
	
}
SerializabilityUtil::init();