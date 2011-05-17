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
require_once PHPRPC_ROOT . 'rpcphptools.php';

class SerializabilityUtil {
	
	private static $classSerializableFieldsCache = null;
	private static $classCustomSerializerCache = null;
	
	public static function hasCustomFieldSerializer(Clazz $instanceType) {
		assert($instanceType != null);
		if ($instanceType->isArray()) {
			return null;
		}
		
		if (self::$classCustomSerializerCache == null)
			self::$classCustomSerializerCache = new ObjectMap();
		
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
		if (self::$classSerializableFieldsCache == null)
			self::$classSerializableFieldsCache = new ObjectMap();
		
		$serializableFields = self::$classSerializableFieldsCache->get($clazz);
		if ($serializableFields == null) {
			$fieldList = array();
			foreach ($clazz->getFields() as $field) {
				if (self::fieldQualifiesForSerialization($field)) {
					$fieldList[] = $field;
				}
			}
			$serializableFields = $fieldList;
			
			asort($serializableFields);
			
			self::$classSerializableFieldsCache->put($clazz, $serializableFields);
		}
		return $serializableFields;
	}
	
	private static function fieldQualifiesForSerialization(Field $field) {
		if ($field->getDeclaringClass() == Classes::classOf('Throwable')) {
			if ($field->getName() == 'detailMessage') {
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
		
		// TODO: miss
		
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
	
}