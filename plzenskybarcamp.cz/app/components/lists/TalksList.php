<?php

namespace App\Components\Lists;

use Nette\Application\UI\Control;

class TalksList extends Control {

	private $registrationModel;

	public function __construct( $parent, $name, $registrationModel ) {
		$this->registrationModel = $registrationModel;
	}

	public function render() {
		$this->template->setFile( __DIR__ . '/templates/talksList.latte');
		$talks = $this->registrationModel->getTalks();
		$this->template->talks = $talks;
		$this->template->currentUser = $this->getPresenter()->getUser();
		$this->template->render();
	}
}