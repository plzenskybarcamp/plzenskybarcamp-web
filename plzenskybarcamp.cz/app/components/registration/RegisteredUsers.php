<?php

namespace App\Components\Registration;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class RegisteredUsers extends Control {

	/** var Nette\Application\UI\Form **/
	private $form;
	
	public function render() {
		$user = array( 'thumbnail' );
		$this->template->setFile( __DIR__ . '/templates/registeredUsers.latte');
		$this->template->usersCount = 65;
		$this->template->users = array( (object) $user );
		$this->template->render();
	}
}