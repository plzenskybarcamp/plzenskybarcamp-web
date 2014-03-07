<?php

namespace App\Components\Registration;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class UserRegistration extends Control {

	/** var Nette\Application\UI\Form **/
	private $form;

	/** App\Model\Registration **/
	private $registrationModel;

	public function __construct( $parent, $name, $registrationModel ) {
		parent::__construct( $parent, $name );
		$this->registrationModel = $registrationModel;
	}
	
	public function render() {
		$this->template->setFile( __DIR__ . '/templates/userRegistration.latte' );
		$this->template->render();
	}

	public function createComponentForm( $name ) {
		$form = new Form( $this, $name );
		$form->addText( 'name', 'Jmeno' )
			->addRule(Form::FILLED, 'Must byt vyplneno');
		$form->addText( 'twitter', 'Twitter' );
		$form->addText( 'email', 'E-mail')
			->addRule(Form::FILLED, 'Must byt vyplneno');
		$form->addTextArea( 'bio', 'Bio' )
			->addRule(Form::FILLED, 'Must byt vyplneno');
		$form->addSubmit( 'submit', 'Odeslat' );


		$form->onSubmit[] = array( $this, 'processRegistration' );
		return $form;
	}

	public function processRegistration( Form $form ) {
		$values = (array) $form->getValues();
		$user = $this->getPresenter()->getUser();
		$this->registrationModel->updateConferree( $user->getId(), $values );
	}
} 