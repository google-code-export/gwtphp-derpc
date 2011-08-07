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
 * Date: 17 juil. 2011
 * Author: Mathieu LIGOCKI
 */

/** @gwtname com.google.gwt.user.client.rpc.core.java.util.ArrayList_CustomFieldSerializer */
final class ArrayList_CustomFieldSerializer {

	public static function transform(SerializationStreamReader $streamReader) {
		return Collection_CustomFieldSerializerBase::transform($streamReader);
	}

	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		Collection_CustomFieldSerializerBase::serialize($streamWriter, $instance);
	}

	public static function deserialize(SerializationStreamReader $streamReader, $instance) {
		Collection_CustomFieldSerializerBase::deserialize($streamReader, $instance);
	}

}

/** @gwtname com.google.gwt.user.client.rpc.core.java.util.Vector_CustomFieldSerializer */
final class Vector_CustomFieldSerializer {

	public static function transform(SerializationStreamReader $streamReader) {
		return Collection_CustomFieldSerializerBase::transform($streamReader);
	}

	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		Collection_CustomFieldSerializerBase::serialize($streamWriter, $instance);
	}

}

/** @gwtname com.google.gwt.user.client.rpc.core.java.util.HashSet_CustomFieldSerializer */
final class HashSet_CustomFieldSerializer {

	public static function transform(SerializationStreamReader $streamReader) {
		return Collection_CustomFieldSerializerBase::transform($streamReader);
	}

	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		Collection_CustomFieldSerializerBase::serialize($streamWriter, $instance);
	}

}

/** @gwtname com.google.gwt.user.client.rpc.core.java.util.TreeSet_CustomFieldSerializer */
final class TreeSet_CustomFieldSerializer {

	public static function transform(SerializationStreamReader $streamReader) {
		return Collection_CustomFieldSerializerBase::transform($streamReader);
	}

	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		Collection_CustomFieldSerializerBase::serialize($streamWriter, $instance);
	}

}

/** @gwtname com.google.gwt.user.client.rpc.core.java.util.HashMap_CustomFieldSerializer */
final class HashMap_CustomFieldSerializer {

	public static function transform(SerializationStreamReader $streamReader) {
		return Map_CustomFieldSerializerBase::transform($streamReader);
	}

	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		Map_CustomFieldSerializerBase::serialize($streamWriter, $instance);
	}

}

/** @gwtname com.google.gwt.user.client.rpc.core.java.util.TreeMap_CustomFieldSerializer */
final class TreeMap_CustomFieldSerializer {

	public static function transform(SerializationStreamReader $streamReader) {
		return Map_CustomFieldSerializerBase::transform($streamReader);
	}

	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		Map_CustomFieldSerializerBase::serialize($streamWriter, $instance);
	}

}

/** @gwtname com.google.gwt.user.client.rpc.core.java.util.IdentityHashMap_CustomFieldSerializer */
final class IdentityHashMap_CustomFieldSerializer {

	public static function transform(SerializationStreamReader $streamReader) {
		return Map_CustomFieldSerializerBase::transform($streamReader);
	}

	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		Map_CustomFieldSerializerBase::serialize($streamWriter, $instance);
	}

}

final class Collection_CustomFieldSerializerBase {

	public static function transform(SerializationStreamReader $streamReader) {
		$size = $streamReader->readInt();
		$result = array();
		for ($i=0; $i<$size; $i++) {
			$result[] = $streamReader->readObject();
		}
		return $result;
	}

	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		$array = $instance->toArray();
		$size = count($array);
		$streamWriter->writeInt($size);
		foreach ($array as $element) {
			$streamWriter->writeObject($element);
		}
	}

	public static function deserialize(SerializationStreamReader $streamReader, $instance) {
		$size = $streamReader->readInt();
		for ($i=0; $i<$size; ++$i) {
			$obj = $streamReader->readObject();
			$instance->add($obj);
		}
	}

}

final class Map_CustomFieldSerializerBase {

	public static function transform(SerializationStreamReader $streamReader) {
		$size = $streamReader->readInt();
		$result = array();
		for ($i=0; $i<$size; $i++) {
			$obj = $streamReader->readObject();
			$value = $streamReader->readObject();
			$result[$obj] = $value;
		}
		return $result;
	}

	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		$streamWriter->writeInt($instance->count());
		foreach ($instance->keys() as $key) {
			$streamWriter->writeObject(Classes::toObject($key));
			$streamWriter->writeObject(Classes::toObject($instance->get($key)));
		}

		/*foreach ($instance as $key => $value) {
			$streamWriter->writeObject($key);
		$streamWriter->writeObject($value);
		}*/
	}

}