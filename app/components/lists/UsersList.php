<?php

namespace App\Components\Lists;

use App\Model\Registration;
use Nette\Application\UI\Control;
use App\Components\Helpers;

class UsersList extends Control {

	private $registrationModel;

	public function __construct( $parent, $name, Registration $registrationModel ) {
		$this->registrationModel = $registrationModel;
	}

	public function render() {
		$this->template->getLatte()->addFilter('twitterize', [Helpers::class, 'twitterize']);
		$this->template->getLatte()->addFilter('biggerTwitterPicture', [Helpers::class, 'biggerTwitterPicture']);
		$this->template->setFile( __DIR__ . '/templates/usersList.latte');
		$users = $this->registrationModel->getPublicConferrees();
		$count = $this->registrationModel->countConferrees();
		$this->template->users = $users->toArray();
		$this->template->usersCount = $count;
		$this->template->currentUser = $this->getPresenter()->getUser();
		$this->template->render();
	}
}