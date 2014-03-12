<?php

namespace App\Components\Registration;

use Nette\Security as NS;

class Identity extends NS\Identity implements IIDentity, NS\IIdentity {

	private $isRegistered = FALSE;
	private $isSpeaker = FALSE;

	/**
	* Temporary method for testing
	**/
	public function setRegistered( $value ) {
		$this->isRegistered = $value;
	}

	/**
	* Temporary method for testing
	**/
	public function setSpeaker( $value ) {
		$this->isSpeaker = $value;
	}

	public function isRegistered() {
		return $this->isRegistered;
	}

	public function isSpeaker() {
		return $this->isSpeaker;
	}
}