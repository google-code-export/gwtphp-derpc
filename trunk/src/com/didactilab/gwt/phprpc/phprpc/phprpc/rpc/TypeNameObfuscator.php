<?php

require_once PHPRPC_ROOT . 'classes.php';

interface TypeNameObfuscator {
	
	const SERVICE_INTERFACE_ID = '_';
	
	function getClassNameForTypeId($id);
	function getTypeIdForClass(Clazz $clazz); 
	
}