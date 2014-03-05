<?php

namespace App\Components\Registration;

use Nette\Application\UI\Control;

class Main extends Control {

	const MAX_CAPACITY = 100;

	
	public function render() {
		$this->template->setFile( __DIR__ . '/templates/main.latte');
		$this->template->user = new FakeUser(true, true, true);
		$this->template->canBeRegistered = self::MAX_CAPACITY - 100;
		$this->template->isRegistrationOpen = true;
		$this->template->render();
	}

	public function createComponentRegisteredUsers( $name ) {
		return new RegisteredUsers( $this, $name );
	}

	public function createComponentRegisteredSpeakers( $name ) {
		return new RegisteredSpeakers( $this, $name );
	}

	public function createComponentRegistration( $name ) {
		return new UserRegistration( $this, $name );
	}

	public function createComponentSpeakerRegisteration( $name ) {
		return new SpeakerRegistration( $this, $name );
	}
}