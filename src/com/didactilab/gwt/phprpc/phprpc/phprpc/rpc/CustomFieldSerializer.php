<?php

require_once PHPRPC_ROOT . 'classes.php';

require_once PHPRPC_ROOT . 'SerializationStream.php';

abstract class CustomFieldSerializer {
	public abstract function deserializeInstance(SerializationStreamReader $streamReader, $instance);
	
	public function hasCustomInstantiateInstance() {
		return false;
	}
	
	public function instantiateInstance(SerializationStreamReader $streamReader) {
		throw new SerializationException('instantiateInstance is not supported by ' . Classes::classOf($this)->getFullName);
	}
	
	public abstract function serializeInstance(SerializationStreamWriter $streamWriter, $instance);
	
}
