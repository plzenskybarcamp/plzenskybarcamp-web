<?php

namespace App\Components\Registration;

use Nette\Application\UI\Control,
	Nette\Application\UI\Form,
	App\Model\Registration;

class RegisteredSpeakers extends Control {

	/** var \Nette\Application\UI\Form **/
	private $form;

	private $registrationModel;

	public function __construct( $parent, $name, Registration $registrationModel ) {
		parent::__construct( $parent, $name );
		$this->registrationModel = $registrationModel;
	}
	
	public function render( $title ) {
		$this->template->setFile( __DIR__ . '/templates/registeredSpeaker.latte');
		$this->template->speakers = $this->registrationModel->getSpeakers();
		$this->template->speakersCount = $this->template->speakers->count(12);
		$this->template->title = $title;
		$this->template->render();
	}
}