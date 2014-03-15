<?php

namespace App\Components\Registration;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class SpeakerRegistration extends Control {

	/** var Nette\Application\UI\Form **/
	private $form;

	/** App\Model\Registration **/
	private $registrationModel;

	public function __construct( $parent, $name, $registrationModel ) {
		parent::__construct( $parent, $name );
		$this->registrationModel = $registrationModel;
	}
	
	public function render() {
		$this->template->setFile( __DIR__ . '/templates/speakerRegistration.latte' );
		$this->template->render();
	}

	public function createComponentForm( $name ) {
		$form = new Form( $this, $name );
		$form = $this->addTalksFields( $form );
		$form = $this->addUsersFields( $form );

		$form->addSubmit( 'submit', 'Chci přednášet!' );

		$form->onSuccess[] = array( $this, 'processRegistration' );
		return $form;
	}

	public function processRegistration( Form $form ) {
		$values = (array) $form->getValues();
		$talk = $this->fetchTalkData( $values );
		$speaker = $this->fetchSpeakerData( $values );
		$user = $this->getPresenter()->getUser();
		$userId = $user->getId();

		if ( isset( $talk['talk_id'] ) ) {
			$this->registrationModel->updateTalk( $talk );
		} else {
			$this->registrationModel->createTalk( $userId, $talk );
		}
		$this->registrationModel->updateConferree( $userId, $speaker );
	}

	public function addTalksFields( $container ) {
		$container->addText( 'title', 'Název přednášky' )
			->addRule(Form::FILLED, 'Název přednášky musí být vyplněn');
		$container->addTextArea( 'description', 'Popis přednášky' )
			->addRule(Form::FILLED, 'Popis přednášky musí být vyplněn');
		$container->addTextArea( 'purpose', 'Komu je určená?' )
			->addRule(Form::FILLED, 'Komu je přednáška určená musí být vyplněno');
		return $container;
	}

	public function addUsersFields( $container ) {
		$container->addText( 'linked', 'LinkedIn' );
		$container->addText( 'web', 'Web' );
		$container->addText( 'facebook', 'Facebook' );
		return $container;
	}

	private function fetchSpeakerData( array $data ) {
		return array(
			'linked' => $data['linked'],
			'web' => $data['web'],
			'facebook' => $data['facebook']
		);
	}

	private function fetchTalkData( array $data ) {
		return array_diff_assoc( $data, $this->fetchSpeakerData( $data ) );
	}
} 