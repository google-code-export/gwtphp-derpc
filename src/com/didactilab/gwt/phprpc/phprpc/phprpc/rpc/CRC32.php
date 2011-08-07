<?php

interface Checksum {
	function update($value);
	function reset();
	function getValue();
}

class CRC32 implements Checksum {
	
	const ALGO = 'crc32b';
	
	private $context;
	
	public function __construct() {
		$this->context = hash_init(self::ALGO);
	}
	
	public function update($value) {
		hash_update($this->context, &$value);
	}
	
	public function reset() {
		$this->context = hash_init(self::ALGO);
	}
	
	public function getValue() {
		$res = hash_final($this->context);
		return hexdec($res);
	}
	
}