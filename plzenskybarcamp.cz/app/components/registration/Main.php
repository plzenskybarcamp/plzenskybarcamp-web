<?php

namespace App\Components\Registration;

use Nette\Application\UI\Control;
use  Nette\Application\Responses\JsonResponse;

class Main extends Control {

	const MAX_CAPACITY = 300;

	private $registrationModel;

	private $fbLoginLink;

	public function __construct( $parent, $name, $registrationModel, $fbLoginLink ) {
		parent::__construct( $parent, $name );
		$this->registrationModel = $registrationModel;
		$this->fbLoginLink = $fbLoginLink;
	}
	
	public function render() {
		$this->createControlTemplate()->render();
	}

	private function createControlTemplate() {
		$this->template->setFile( __DIR__ . '/templates/main.latte');
		$this->template->user = $this->getPresenter()->getUser();
		$this->template->identity = new FakeUser( $this->getPresenter()->getUser(), $this->registrationModel ); //$this->getPresenter()->getUser()->getIdentity();
		$this->template->canBeRegistered = self::MAX_CAPACITY - $this->registrationModel->getConferrees()->count();
		$this->template->isRegistrationOpen = true;
		$this->template->fbLoginLink = $this->fbLoginLink;
		return $this->template;
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
		$main = $this;
		if ( $presenter->isAjax() ) {
			$registration['form']->onSuccess[] = function() use ( $presenter, $main ) {
				$data = array( 'redirect' => $main->link( 'toJSON!' ) );
				$presenter->sendResponse( new JsonResponse( $data ) );
			};
		} else {
			$registration['form']->onSuccess[] =
				function() use ( $presenter ) { $presenter->redirect( 'default' ); };
		}

		$registration;
	}

	public function redirectToHome() {
		$this->getPresenter()->redirect( 'default' );
	}

	public function handletoJSON() {
		if( $this->getPresenter()->isAjax() ) {
			$data = array( 'html' => $this->createControlTemplate()->__toString() );
			$this->getPresenter()->sendResponse( new JsonResponse( $data ) );
		}
	}
}