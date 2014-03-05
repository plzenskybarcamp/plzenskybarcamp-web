<?php

namespace App\Components\Registration;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class SpeakerRegistration extends Control {

	/** var Nette\Application\UI\Form **/
	private $form;
	
	public function render() {
		$this->template->setFile( __DIR__ . '/templates/speakerRegistration.latte' );
		$this->template->render();
	}

	public function createComponentForm( $name ) {
		$form = new Form( $this, $name );
		$form->addText( 'title', 'Nazev prednasky' )
			->addRule(Form::FILLED, 'Musi byt vyplneno');
		$form->addTextArea( 'description', 'Popis prednasky' )
			->addRule(Form::FILLED, 'Musi byt vyplneno');
		$form->addTextArea( 'purpose', 'Komu je urceno?' )
			->addRule(Form::FILLED, 'Musi byt vyplneno');

		$form->addText( 'linked', 'linked in' );
		$form->addText( 'web', 'Web' );
		$form->addText( 'facebook', 'Facebook' );

		$form->addSubmit( 'submit', 'Odeslat' );

		$form->onSubmit[] = array( $this, 'processRegistration' );
		return $form;
	}

	public function processRegistration( From $form ) {
		$values = $form->getValues();
	}
} 