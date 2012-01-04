<?php

/** @gwtname com.google.gwt.user.client.rpc.SerializationStreamReader */
interface SerializationStreamReader {
	function readBoolean();
	function readByte();
	function readChar();
	function readDouble();
	function readFloat();
	function readInt();
	function readLong();
	function readObject();
	function readShort();
	function readString();
}

/** @gwtname com.google.gwt.user.client.rpc.SerializationStreamWriter */
interface SerializationStreamWriter {
	function __toString();
	function writeBoolean($value);
	function writeByte($value);
	function writeChar($value);
	function writeDouble($value);
	function writeFloat($value);
	function writeInt($value);
	function writeLong($value);
	function writeObject($value);
	function writeShort($value);
	function writeString($value);

	function writeValue(Clazz $clazz, $value);
	function writeEnum(Clazz $clazz, $value);
	function writeObject2(Clazz $clazz, $value);
}