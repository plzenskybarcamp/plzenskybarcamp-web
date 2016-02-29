<?php

namespace App\Components\Registration;

use Nette\Application\UI\Control,
	Nette\Application\UI\Form,
	MongoDB\Model\MongoDbSanitizer;

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
		$form->setRenderer( new \App\Components\CustomFormRenderer );
		$form->addGroup('Zapište svou přednášku');
		$form = $this->addTalksFields( $form );
		$form->addGroup('Doplňující info');
		$form = $this->addUsersFields( $form );

		$form->addSubmit( 'submit', 'Odeslat přednášku' );

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

		$conferee = $this->registrationModel->findCoferree( $user->getId() );
		$user->getIdentity()->talk = MongoDbSanitizer::sanitizeDocument( $conferee['talk'] );

	}

	public function addTalksFields( $container ) {
		$container->addText( 'title', 'Název přednášky' )
			->addRule(Form::FILLED, 'Název přednášky musí být vyplněn')
			->setAttribute('placeholder', 'Zadej název přednášky');
		$container->addTextArea( 'description', 'Popis přednášky' )
			->addRule(Form::FILLED, 'Popis přednášky musí být vyplněn');
		$container->addTextArea( 'purpose', 'Komu je určená?' )
			->addRule(Form::FILLED, 'Komu je přednáška určená musí být vyplněno');
		return $container;
	}

	public function addUsersFields( $container ) {
		$container->addText( 'linked', 'LinkedIn' )
			->setAttribute('placeholder', 'https://www.linkedin.com/in/grudl')
			->addCondition(Form::FILLED)
				->addRule(Form::URL, "Adresa v LinkedIn nevypadá jako webová adresa, překontroluj to, prosím");
		$container->addText( 'web', 'Web' )
			->setAttribute('placeholder', 'https://davidgrudl.com')
			->addCondition(Form::FILLED)
				->addRule(Form::URL, "Adresa v poli Web nevypadá jako webová adresa, překontroluj to, prosím");
		$container->addText( 'facebook', 'Facebook' )
			->setAttribute('placeholder', 'https://www.facebook.com/davidgrudl')
			->addCondition(Form::FILLED)
				->addRule(Form::URL, "Adresa pro Facebook nevypadá jako webová adresa, překontroluj to, prosím");

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