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

/** @gwtname com.google.gwt.user.client.rpc.core.java.lang.Boolean_CustomFieldSerializer */
class Boolean_CustomFieldSerializer {
	
	public static function transform(SerializationStreamReader $streamReader) {
		return $streamReader->readBoolean();
	}
	
	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		$streamWriter->writeBoolean($instance->getValue());
	}
	
}

/** @gwtname com.google.gwt.user.client.rpc.core.java.lang.Byte_CustomFieldSerializer */
class Byte_CustomFieldSerializer {
	
	public static function transform(SerializationStreamReader $streamReader) {
		return $streamReader->readByte();
	}
	
	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		$streamWriter->writeByte($instance->getValue());
	}
	
}

/** @gwtname com.google.gwt.user.client.rpc.core.java.lang.Character_CustomFieldSerializer */
class Character_CustomFieldSerializer {
	
	public static function transform(SerializationStreamReader $streamReader) {
		return $streamReader->readChar();
	}
	
	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		$streamWriter->writeChar($instance->getValue());
	}
	
}

/** @gwtname com.google.gwt.user.client.rpc.core.java.lang.Double_CustomFieldSerializer */
class Double_CustomFieldSerializer {
	
	public static function transform(SerializationStreamReader $streamReader) {
		return $streamReader->readDouble();
	}
	
	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		$streamWriter->writeDouble($instance->getValue());
	}
	
}

/** @gwtname com.google.gwt.user.client.rpc.core.java.lang.Float_CustomFieldSerializer */
class Float_CustomFieldSerializer {
	
	public static function transform(SerializationStreamReader $streamReader) {
		return $streamReader->readFloat();
	}
	
	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		$streamWriter->writeFloat($instance->getValue());
	}
	
}

/** @gwtname com.google.gwt.user.client.rpc.core.java.lang.Integer_CustomFieldSerializer */
class Integer_CustomFieldSerializer {
	
	public static function transform(SerializationStreamReader $streamReader) {
		return $streamReader->readInt();
	}
	
	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		$streamWriter->writeInt($instance->getValue());
	}
	
}

/** @gwtname com.google.gwt.user.client.rpc.core.java.lang.Long_CustomFieldSerializer */
class Long_CustomFieldSerializer {
	
	public static function transform(SerializationStreamReader $streamReader) {
		return $streamReader->readLong();
	}
	
	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		$streamWriter->writeLong($instance->getValue());
	}
	
}

/** @gwtname com.google.gwt.user.client.rpc.core.java.lang.Short_CustomFieldSerializer */
class Short_CustomFieldSerializer {
	
	public static function transform(SerializationStreamReader $streamReader) {
		return $streamReader->readShort();
	}
	
	public static function serialize(SerializationStreamWriter $streamWriter, $instance) {
		$streamWriter->writeShort($instance->getValue());
	}
	
}

/** @gwtname com.google.gwt.user.client.rpc.core.java.lang.String_CustomFieldSerializer */
class String_CustomFieldSerializer {
	
	public static function transform(SerializationStreamReader $streamReader) {
		return $streamReader->readString();
	}
	
}

/** @gwtname com.google.gwt.user.client.rpc.core.java.lang.Void_CustomFieldSerializer */
class Void_CustomFieldSerializer {
	
	public static function transform(SerializationStreamReader $streamReader) {
		return null;
	}
	
}