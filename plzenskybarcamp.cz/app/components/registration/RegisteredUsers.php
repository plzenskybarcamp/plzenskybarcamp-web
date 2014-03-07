<?php

namespace App\Components\Registration;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class RegisteredUsers extends Control {

	/** var \Nette\Application\UI\Form **/
	private $form;

	/** var \MongoCursor **/
	private $conferreeCursor;

	public function __construct( $parent, $name, \MongoCursor $conferreeCursor ) {
		parent::__construct( $parent, $name );
		$this->conferreeCursor = $conferreeCursor;
	}
	
	public function render( $title ) {
		$this->template->setFile( __DIR__ . '/templates/registeredUsers.latte');
		$this->template->usersCount = $this->conferreeCursor->count();
		$this->template->users = $this->conferreeCursor;
		$this->template->title = $title;
		$this->template->render();
	}
}