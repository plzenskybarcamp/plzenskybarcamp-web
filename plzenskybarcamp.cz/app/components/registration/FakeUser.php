<?php

namespace App\Components\Registration;

class FakeUser implements IIdentity {

	private $user;
	private $conferree;

	public function __construct( $user, $registrationModel ) {
		$this->user = $user;
		$this->conferree = null;
		if ( $user->isLoggedIn() ) {
			$this->conferree = $registrationModel->findCoferree( $user->getId() );
		}
	}

	public function isLoggedIn() {
		return $this->user->isLoggedIn();
	}

	public function isRegistered() {
		return (bool) $this->conferree;
	}

	public function isSpeaker() {
		return $this->conferree && isset( $this->conferree['talk'] );
	}
}