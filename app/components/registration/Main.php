<?php

namespace App\Components\Registration;

use Nette\Application\UI\Control;
use  Nette\Application\Responses\JsonResponse;

class Main extends Control {

	private $registrationModel;

	private $configModel;

	private $token;

	private $sns;


	public function __construct( $parent, $name, $registrationModel, $configModel, $token, $sns ) {
		parent::__construct( $parent, $name );
		$this->registrationModel = $registrationModel;
		$this->configModel = $configModel;
		$this->token = $token;
		$this->sns = $sns;
	}
	
	public function render() {
		$this->createControlTemplate()->render();
	}

	private function createControlTemplate() {
		$registrationCapatity = $this->configModel->getConfig( 'registrationCapatity', 0 );
		$registreredUsers = $this->registrationModel->countConferrees();

		$canBeRegistered = ( $registrationCapatity - $registreredUsers ) > 0;
		if( ! $canBeRegistered && $this->token ) {
			try{
				$this->registrationModel->validateVipToken( $this->token );
				$canBeRegistered = TRUE;
			}
			catch (\App\Model\InvalidTokenException $e) {
				//void
			}
		}

		$this->template->setFile( __DIR__ . '/templates/main.latte');
		$this->template->user = $this->getPresenter()->getUser();
		$this->template->identity = $this->getPresenter()->getUser()->getIdentity();
		$this->template->canBeRegistered = $canBeRegistered;
		$this->template->isRegistrationOpen = $this->configModel->getConfig( 'isRegistrationOpen', FALSE );
		return $this->template;
	}

	public function createComponentRegisteredUsers( $name ) {
		return new RegisteredUsers( $this, $name, $this->registrationModel );
	}

	public function createComponentRegisteredSpeakers( $name ) {
		return new RegisteredSpeakers( $this, $name, $this->registrationModel );
	}

	public function createComponentRegistration( $name ) {
		return $this->compliteRegistration(
			new UserRegistration( $this, $name, $this->registrationModel, $this->token, $this->sns )
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