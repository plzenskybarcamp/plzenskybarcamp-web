<?php

namespace App\Components\Registration;

use Nette\Application\UI\Control;

class Main extends Control {

	const MAX_CAPACITY = 100;

	private $registrationModel;

	public function __construct( $parent, $name, $registrationModel ) {
		parent::__construct( $parent, $name );
		$this->registrationModel = $registrationModel;
	}
	
	public function render() {
		$this->template->setFile( __DIR__ . '/templates/main.latte');
		$this->template->user = new FakeUser(true, true, false);
		$this->template->canBeRegistered = self::MAX_CAPACITY - 0;
		$this->template->isRegistrationOpen = true;
		$this->template->render();
	}

	public function createComponentRegisteredUsers( $name ) {
		$cursor = $this->registrationModel->getConferrees();
		return new RegisteredUsers( $this, $name, $cursor );
	}

	public function createComponentRegisteredSpeakers( $name ) {
		$cursor = $this->registrationModel->getSpeakers();
		return new RegisteredUsers( $this, $name, $cursor );
	}

	public function createComponentRegistration( $name ) {
		return $this->compliteRegistration(
			new UserRegistration( $this, $name, $this->registrationModel )
		);
	}

	public function createComponentSpeakerRegisteration( $name ) {
		return $this->compliteRegistration(
			new SpeakerRegistration( $this, $name, $this->registrationModel )
		);
	}

	private function compliteRegistration( $registration ) {
		$presenter = $this->getPresenter();
		$registration['form']->onSuccess[] =
			function() use ( $presenter ) { $presenter->redirect( 'default' ); };

		$registration;
	}

	public function redirectToHome() {
		$this->getPresenter()->redirect( 'default' );
	}
}