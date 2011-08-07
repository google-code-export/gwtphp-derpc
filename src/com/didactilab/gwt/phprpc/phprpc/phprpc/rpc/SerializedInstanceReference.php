<?php

interface SerializedInstanceReference {
	const SERIALIZED_REFERENCE_SEPARATOR = '/';
	
	function getName();
	function getSignature();
}