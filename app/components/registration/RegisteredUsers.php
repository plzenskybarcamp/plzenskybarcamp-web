<?php

namespace App\Components\Registration;

use Nette\Application\UI\Control,
	Nette\Application\UI\Form,
	App\Model\Registration;

class RegisteredUsers extends Control {

	/** var \Nette\Application\UI\Form **/
	private $form;

	private $registrationModel;

	public function __construct( $parent, $name, Registration $registrationModel ) {
		parent::__construct( $parent, $name );
		$this->registrationModel = $registrationModel;
	}

	public function render( $title ) {
		$this->template->setFile( __DIR__ . '/templates/registeredUsers.latte');
		$this->template->users = $this->registrationModel->getPublicConferrees(12)->toArray();
		$this->template->usersCount = $this->registrationModel->countConferrees();
		$this->template->title = $title;
		$this->template->render();
	}
}