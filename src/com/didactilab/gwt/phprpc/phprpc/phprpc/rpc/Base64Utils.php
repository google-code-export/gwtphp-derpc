<?php

class Base64Utils {
	
	private static $base64Chars = array(
	  'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N',
      'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b',
      'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
      'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3',
      '4', '5', '6', '7', '8', '9', '$', '_');
	
	private static $base64Values = array();
	
	public static function init() {
		for ($i=0; $i<count(self::$base64Chars); $i++) {
			self::$base64Values[ord(self::$base64Chars[$i])] = $i;
		}
	}
	
	public static function fromBase64($data) {
		return base64_decode($data);
	}
	
	
	public static function longFromBase64($value) {
		$pos = 0;
		$longVal = self::$base64Values[ord($value[$pos++])];
		$len = strlen($value);
		while ($pos < $len) {
			$longVal = bcmul($longVal, bcpow(2, 6));
			//$longVal = $longVal << 64;
			//$longVal |= self::$base64Values[ord($value[$pos++])];
			$longVal = bcadd($longVal, self::$base64Values[ord($value[$pos++])]);
		}
		return (float) $longVal;
	}
	
	public static function toBase64($data) {
		if (is_double($data)) {
			return self::longToBase64($data);
		}
		else if (is_int($data)) {
			return self::longToBase64($data);
		}
		else {
			return self::dataToBase64($data);
		}
	}
	
	private static function longToBase64($value) {
		$low = $value & 0xFFFFFFFF;
		//$high = $value >> 32;
		//$high = $value / 4294967296;
		$high = (int) bcdiv($value, bcpow(2, 32));
		
		$sb = new Base64Utils_StringBuffer();
		$haveNonZero = self::base64Append($sb, ($high >> 28) & 0xF, false);
		$haveNonZero = self::base64Append($sb, ($high >> 22) & 0x3F, $haveNonZero);
		$haveNonZero = self::base64Append($sb, ($high >> 16) & 0x3F, $haveNonZero);
		$haveNonZero = self::base64Append($sb, ($high >> 10) & 0x3F, $haveNonZero);
		$haveNonZero = self::base64Append($sb, ($high >> 4) & 0x3F, $haveNonZero);
		$v = (($high & 0xF) << 2) | (($low >> 30) & 0x3);
		$haveNonZero = self::base64Append($sb, $v, $haveNonZero);
		$haveNonZero = self::base64Append($sb, ($low >> 24) & 0x3F, $haveNonZero);
		$haveNonZero = self::base64Append($sb, ($low >> 18) & 0x3F, $haveNonZero);
		$haveNonZero = self::base64Append($sb, ($low >> 12) & 0x3F, $haveNonZero);
		self::base64Append($sb, ($low >> 6) & 0x3F, $haveNonZero);
		self::base64Append($sb, $low & 0x3F, true);
		
		return (string) $sb;
	}
    
    private static function base64Append(Base64Utils_StringBuffer $sb, $digit, $haveNonZero) {
    	if ($digit > 0) {
    		$haveNonZero = true;
    	}
    	if ($haveNonZero) {
    		$sb->append(self::$base64Chars[$digit]);
    	}
    	return $haveNonZero;
    }
	
	private static function dataToBase64($data) {
		return base64_encode($data);
	}
	
	
}
Base64Utils::init();

class Base64Utils_StringBuffer {
	private $buffer = '';
	
	public function append($value) {
		$this->buffer .= $value;
	}
	
	public function __toString() {
		return $this->buffer;
	}
}