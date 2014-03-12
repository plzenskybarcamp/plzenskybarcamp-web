<?php

namespace App\Components\Registration;

use Nette\Security as NS;

class Identity extends NS\Identity implements IIDentity, NS\IIdentity {

	private $id;
	private $isRegistered = FALSE;
	private $isSpeaker = FALSE;

	/**
	* Overriding Nette method (we need string of id)
	**/
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	* Overriding Nette method
	**/
	public function getId()
	{
		return $this->id;
	}

	/**
	* Temporary method for testing
	**/
	public function setRegistered( $value ) {
		$this->isRegistered = $value;
		return $this;
	}

	/**
	* Temporary method for testing
	**/
	public function setSpeaker( $value ) {
		$this->isSpeaker = $value;
		return $this;
	}

	public function isRegistered() {
		return $this->isRegistered;
	}

	public function isSpeaker() {
		return $this->isSpeaker;
	}
}