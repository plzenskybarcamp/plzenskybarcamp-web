<?php

namespace App\Components\Registration;

use Nette\Security as NS;

class Identity extends NS\Identity implements IIDentity, NS\IIdentity {

	public function isLoggedIn() {
		return $this->isLogged;
	}

	public function isRegistered() {
		return $this->isRegistered;
	}

	public function isSpeaker() {
		return $this->isSpeaker;
	}
}