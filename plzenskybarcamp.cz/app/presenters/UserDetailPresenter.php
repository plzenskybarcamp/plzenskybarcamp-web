<?php

namespace App\Presenters;

use Nette,
	App\Model,
	App\Components\Registration\UserRegistration,
	App\Components\Registration\SpeakerRegistration,
	Nette\Application\UI\Form;


class UserDetailPresenter extends BasePresenter
{

	private $registrationModel;

	private $conferree;

	private $speakerForm;

	private $userForm;

	public function __construct( Model\Registration $registrationModel ) {
		$this->registrationModel = $registrationModel;
		$this->speakerForm = new SpeakerRegistration( $this, 'speaker', $registrationModel );
		$this->userForm = new UserRegistration( $this, 'user', $registrationModel );
		$userId = null;//$this->getPresenter()->getUser()->getId();
		$this->conferree = $registrationModel->findCoferree( $userId );
	}

	public function createComponentForm( $name ) {
		$form = new Form( $this, $name );
		$form->addGroup( 'User' );
		$usersContainer = $form->addContainer( 'user' );
		$this->userForm->addUsersFields( $usersContainer );
		$this->speakerForm->addUsersFields( $usersContainer );

		if ( isset( $this->conferree['talk'] ) ) {
			$form->addGroup( 'Talk' );
			$this->speakerForm->addTalksFields( $form->addContainer( 'talk') );	
		}

		$form->setDefaults( array(
			'user' => $this->conferree,
			'talk' => $this->conferree['talk']
		) );

		$form->onSubmit[] = array( $this, 'processUpdate');
		$form->addSubmit( 'Save' );
		return $form;
	}

	public function processUpdate( Form $form ) {
		$values = (array) $form->getValues();

		$user = $this->getUser();

		$this->registrationModel->updateConferree( $this->conferree['user_id'], (array) $values['user'] );
		if ( isset( $this->conferree['talk']['talk_id'] ) ) {
			$this->registrationModel->updateTalk( $this->conferree['talk']['talk_id'], (array) $values['talk'] );
		}

		$this->redirect( 'default' );
	}

}