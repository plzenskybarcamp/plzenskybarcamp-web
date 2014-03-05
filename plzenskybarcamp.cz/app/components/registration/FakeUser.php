<?php

namespace App\Components\Registration;

class FakeUser {

	public function __construct( $isLogged, $isRegistered, $isSpeaker ) {
		$this->isLogged = $isLogged;
		$this->isRegistered = $isRegistered;
		$this->isSpeaker = $isSpeaker;
	}

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