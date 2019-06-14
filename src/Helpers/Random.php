<?php

class Random {
	protected $code;

	public function __construct(int $length){
		$this->setCode($length);
	}

	public function setCode(int $legth){
		$this->code = random_bytes($legth);
	}

	public function getCode(){
		return bin2hex($this->code);
	}
}
