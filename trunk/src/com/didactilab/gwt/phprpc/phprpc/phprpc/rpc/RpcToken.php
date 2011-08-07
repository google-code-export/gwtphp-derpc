<?php

require_once PHPRPC_ROOT . 'serialization.php';

/** @gwtname com.google.gwt.user.client.rpc.RpcToken */
interface RpcToken extends IsSerializable {
}

/** @gwtname com.google.gwt.user.client.rpc.XsrfToken */
class XsrfToken implements RpcToken {
	private $token;
	
	public function __construct($token = null) {
		$this->token = $token;
	}
	
	public function getToken() {
		return $this->token;
	}
	
	public function setToken($token) {
		$this->token = $token;
	}
}
