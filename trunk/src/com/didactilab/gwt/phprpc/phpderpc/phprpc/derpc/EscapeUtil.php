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

require_once PHPRPC_ROOT . 'derpc/SerializationStreamWriter.php';

class EscapeUtil {
	const JS_ESCAPE_CHAR = '\\';
	const JS_QUOTE_CHAR = '"';
	const NON_BREAKING_HYPHEN = '\x20\x11';
	
	const NUMBER_OF_JS_ESCAPED_CHARS = 128;
	
	private static $JS_CHARS_ESCAPED = array();
	
	public static function init() {
		self::$JS_CHARS_ESCAPED = array_fill(0, self::NUMBER_OF_JS_ESCAPED_CHARS, '');
		
		self::$JS_CHARS_ESCAPED[uniord('\x00\xx')] = '0';
		self::$JS_CHARS_ESCAPED[ord("\b")] = 'b';
		self::$JS_CHARS_ESCAPED[ord("\t")] = 't';
		self::$JS_CHARS_ESCAPED[ord("\n")] = 'n';
		self::$JS_CHARS_ESCAPED[ord("\f")] = 'f';
		self::$JS_CHARS_ESCAPED[ord("\r")] = 'r';
		self::$JS_CHARS_ESCAPED[ord("\b")] = 'b';
		self::$JS_CHARS_ESCAPED[ord(self::JS_ESCAPE_CHAR)] = self::JS_ESCAPE_CHAR;
		self::$JS_CHARS_ESCAPED[ord(self::JS_QUOTE_CHAR)] = self::JS_QUOTE_CHAR;
		
	}

	public static function escape($payload) {
		// no isClient
		$quoted = self::escapeString($payload);
		return mb_substr($quoted, 1, mb_strlen($quoted) - 2);
	}

	private static function escapeString($toEscape) {
		$out = '"';
		for ($i=0, $n=mb_strlen($toEscape); $i < $n; ++$i) {
			$c = mb_substr($toEscape, $i, 1);
			if (self::needsUnicodeEscape($c))
			$out .= self::unicodeEscape($c);
			else
			$out .= $c;
		}

		$out .= '"';
		return $out;
	}

	private static function needsUnicodeEscape($ch) {
		switch ($ch) {
			case ' ':
				// ASCII space gets caught in SPACE_SEPARATOR below, but does not
				// need to be escaped
				return false;
			case self::JS_QUOTE_CHAR:
			case self::JS_ESCAPE_CHAR:
				// these must be quoted or they will break the protocol
				return true;
			case self::NON_BREAKING_HYPHEN:
				// This can be expanded into a break followed by a hyphen
				return true;
			default:
				break;
				/*default:
				 switch (Character.getType(ch)) {
					// Conservative
					case Character.COMBINING_SPACING_MARK:
					case Character.ENCLOSING_MARK:
					case Character.NON_SPACING_MARK:
					case Character.UNASSIGNED:
					case Character.PRIVATE_USE:
					case Character.SPACE_SEPARATOR:
					case Character.CONTROL:

					// Minimal
					case Character.LINE_SEPARATOR:
					case Character.FORMAT:
					case Character.PARAGRAPH_SEPARATOR:
					case Character.SURROGATE:
					return true;

					default:
					break;
					}
					break;*/
		}
		return false;
	}

	private static function unicodeEscape($ch) {
		$out = self::JS_ESCAPE_CHAR;
		$chord = uniord($ch);
		if ($chord < self::NUMBER_OF_JS_ESCAPED_CHARS && self::$JS_CHARS_ESCAPED[$chord] != '') {
			$out .= self::$JS_CHARS_ESCAPED[$chord];
		}
		else if ($chord < 256) {
			$out .= 'x' . str_pad(dechex($chord), 2, '0', STR_PAD_LEFT);
		}
		else {
			$out .= 'u' . str_pad(dechex($chord), 4, '0', STR_PAD_LEFT);
		}
		return $out;
	}

}
EscapeUtil::init();

function uniord($c) {
	$h = ord($c{0});
	if ($h <= 0x7F) {
		return $h;
	} else if ($h < 0xC2) {
		return false;
	} else if ($h <= 0xDF) {
		return ($h & 0x1F) << 6 | (ord($c{1}) & 0x3F);
	} else if ($h <= 0xEF) {
		return ($h & 0x0F) << 12 | (ord($c{1}) & 0x3F) << 6
		| (ord($c{2}) & 0x3F);
	} else if ($h <= 0xF4) {
		return ($h & 0x0F) << 18 | (ord($c{1}) & 0x3F) << 12
		| (ord($c{2}) & 0x3F) << 6
		| (ord($c{3}) & 0x3F);
	} else {
		return false;
	}
}