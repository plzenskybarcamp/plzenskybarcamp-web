<?php

namespace App\Components\Registration;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class RegisteredSpeakers extends Control {

	/** var Nette\Application\UI\Form **/
	private $form;
	
	public function render() {
		$user = array( 'thumbnail' );
		$this->template->setFile( __DIR__ . '/templates/registeredSpeaker.latte' );
		$this->template->speakersCount = 13;
		$this->template->speakers = array( (object) $user );
		$this->template->render();
	}
}