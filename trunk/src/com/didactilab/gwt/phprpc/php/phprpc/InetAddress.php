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

class UnknownHostException extends Exception {
}

abstract class InetAddress {
	const IPv4 = 1;
	const IPv6 = 2;

	protected $family;

	public abstract function isAnyLocalAddress();
	public abstract function isMulticastAddress();
	public abstract function isLoopbackAddress();
	public abstract function isLinkLocalAddress();
	public abstract function isSiteLocalAddress();

	public abstract function getRawAddress();

	public abstract function  __toString();

	public static function getByRawAddress(array $addr) {
		if (count($addr) == Inet4Address::INADDRSZ) {
			return new Inet4Address($addr);
		}
		else if (count($addr) == Inet6Address::INADDRSZ) {
			$newAddr = Inet6Address::convertFromIPv4MappedAddress($addr);
			if ($netAddr != null)
				return new Inet4Address($newAddr);
			else
				return new Inet6Address($addr);
		}
		throw new UnknownHostException('addr is of illegal length');
	}

	public static function getByAddress($address) {
		$inet = inet_pton($address);
		$count = mb_strlen($inet);
		$arr = array_fill(0, $count, 0);
		for ($i=0; $i<$count; $i++)
			$arr[$i] = ord($inet[$i]);
		return self::getByRawAddress($arr);
	}
	
	public static function isClientLocalHost() {
		$whitelist = array('localhost', '127.0.0.1', '::1');
		return in_array($_SERVER['HTTP_HOST'], $whitelist);
	}
	
	public static function getRemoteAddress() {
		$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
	
		if ($ip_address == NULL) {
			$ip_address = $_SERVER['REMOTE_ADDR']; 
		}
		
		return self::getByAddress($ip_address);
	}
}

class Inet4Address extends InetAddress {

	const INADDRSZ = 4;
	const LOOPBACK = 2130706433;
	
	private $address;
	
	public function __construct(array $addr) {
		$this->family = self::IPv4;
		if (count($addr) == self::INADDRSZ) {
			$this->address = $addr[3] & 0xFF;
			$this->address |= ($addr[2] << 8) & 0xFF00;
			$this->address |= ($addr[1] << 16) & 0xFF0000;
			$this->address |= ($addr[0] << 24) & 0xFF000000;
		}
	}
	
	public function isAnyLocalAddress() {
		return $this->address == 0;
	}
	
	public function isMulticastAddress() {
		return (($this->address & 0xF0000000) == 0xE0000000);
	}
	
	public function isLoopbackAddress() {
		$byteAddr = getRawAddress();
		return $byteAddr[0] == 127;
	}
	
	public function isLinkLocalAddress() {
		return ((($this->address >> 24) & 0xFF) == 169)
			&& ((($this->address >> 16) & 0xFF) == 254);
	}
	
	public function isSiteLocalAddress() {
		return ((($this->address >> 24) & 0xFF) == 169)
			|| (((($this->address >> 24) & 0xFF) == 172)
			&& ((($this->address >> 16) & 0xFF) == 254))
			|| (((($this->address >> 24) & 0xFF) == 192)
			&& ((($this->address >> 16) & 0xFF) == 168));
	}
	
	public function getRawAddress() {
		$addr = array_fill(0, 4, 0);
		$addr[0] = (($this->address >> 24) & 0xFF);
		$addr[1] = (($this->address >> 16) & 0xFF);
		$addr[2] = (($this->address >> 8) & 0xFF);
		$addr[3] = (($this->address) & 0xFF);
		
		return $addr;
	}
	
	public function  __toString() {
		return (($this->address >> 24) & 0xFF) . '.' . 
			(($this->address >> 16) & 0xFF) . '.' .
			(($this->address >> 8) & 0xFF) . '.' .
			(($this->address) & 0xFF);
	}
	
}

class Inet6Address extends InetAddress {
	
	const INADDRSZ = 16;
	const INT16SZ = 2;
	
	private $address;
	
	public function __construct(array $addr) {
		if (count($addr) == self::INADDRSZ) {
			$this->family = self::IPv6;
			$this->address = $addr;
		}
	}
	
	public function isAnyLocalAddress() {
		$test = 0x00;
		for ($i=0; $i<self::INADDRSZ; $i++)
			$test |= $this->address[$i];
		return ($test == 0x00);
	}
	
	public function isMulticastAddress() {
		return (($this->address[0] && 0xFF) == 0xFF);
	}
	
	public function isLoopbackAddress() {
		$test = 0x00;
		for ($i=0; $i<15; $i++)
			$test |= $this->address[$i];
		return ($test == 0x00) && ($this->address[15] == 0x01);
	}
	
	public function isLinkLocalAddress() {
		return (($this->address[0] & 0xFF) == 0xFE && ($this->address[1] & 0xC0) == 0x80);
	}
	
	public function isSiteLocalAddress() {
		return (($this->address[0] & 0xFF) == 0xFE && ($this->address[1] & 0xC0) == OxC0);
	}

	public static function isIPv4MappedAddress(array $addr) {
		if (count($addr) < self::INADDRSZ) {
			return false;
		}
		if (($addr[0] == 0x00) && ($addr[1] == 0x00) &&
				($addr[2] == 0x00) && ($addr[3] == 0x00) &&
				($addr[4] == 0x00) && ($addr[5] == 0x00) &&
				($addr[6] == 0x00) && ($addr[7] == 0x00) &&
				($addr[8] == 0x00) && ($addr[9] == 0x00) &&
				($addr[10] == 0xff) &&
				($addr[11] == 0xff))  {
			return true;
		}
		return false;
	}
	
	public static function convertFromIPv4MappedAddress(array $addr) {
		if (self::isIPv4MappedAddress($addr)) {
			$newAddr = array_fill(0, Inet4Address::INADDR4SZ, 0);
			for ($i=12; $i<=15; $i++)
				$newAddr[$i - 12] = $addr[$i];
			return $newAddr;
		}
		return null;
	}
	
	public function getRawAddress() {
		return $this->address;
	}
	
	public function  __toString() {
		/*$sb = '';
		for ($i=0; $i<(self::INADDRSZ / self::INT16SZ); $i++) {
			$sb .= dechex((($this->address[$i << 1] << 8) & 0xFF00) | ($this->address[($i << 1) + 1] & 0xFF));
			if ($i < (self::INADDRSZ / self::INT16SZ) - 1) {
				$sb .= ':';
			}
		}
		return $sb;*/
		
		$sb = '';
		for ($i=0; $i<self::INADDRSZ; $i++) {
			$sb .= chr($this->address[$i]);
		}
		return inet_ntop($sb);
	}

}