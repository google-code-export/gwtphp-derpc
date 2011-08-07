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
	
	public static function instantiate(SerializationStreamReader $streamReader) {
		return new Boolean($streamReader->readBoolean());
	}
	
	public static function deserialize(SerializationStreamReader $streamReader, Boolean $instance) {
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
	
	public static function instantiate(SerializationStreamReader $streamReader) {
		return new Byte($streamReader->readByte());
	}
	
	public static function deserialize(SerializationStreamReader $streamReader, Byte $instance) {
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
	
	public static function instantiate(SerializationStreamReader $streamReader) {
		return new Character($streamReader->readChar());
	}
	
	public static function deserialize(SerializationStreamReader $streamReader, Character $instance) {
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
	
	public static function instantiate(SerializationStreamReader $streamReader) {
		return new Double($streamReader->readDouble());
	}
	
	public static function deserialize(SerializationStreamReader $streamReader, Double $instance) {
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
	
	public static function instantiate(SerializationStreamReader $streamReader) {
		return new Float($streamReader->readFloat());
	}
	
	public static function deserialize(SerializationStreamReader $streamReader, Float $instance) {
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
	
	public static function instantiate(SerializationStreamReader $streamReader) {
		return new Integer($streamReader->readInt());
	}
	
	public static function deserialize(SerializationStreamReader $streamReader, Integer $instance) {
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
	
	public static function instantiate(SerializationStreamReader $streamReader) {
		return new Long($streamReader->readLong());
	}
	
	public static function deserialize(SerializationStreamReader $streamReader, Long $instance) {
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
	
	public static function instantiate(SerializationStreamReader $streamReader) {
		return new Short($streamReader->readShort());
	}
	
	public static function deserialize(SerializationStreamReader $streamReader, Short $instance) {
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