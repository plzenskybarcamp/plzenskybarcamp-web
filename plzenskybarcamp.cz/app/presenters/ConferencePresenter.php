<?php

namespace App\Presenters;

use Nette,
	App\Model,
	App\Components\Registration\UserRegistration,
	App\Components\Registration\SpeakerRegistration,
	Nette\Application\UI\Form,
	App\Components\Lists\UsersList,
	App\Components\Lists\TalksList,
	Nette\Application\Responses\JsonResponse;


class ConferencePresenter extends BasePresenter
{

	private $registrationModel;

	private $conferree;

	private $speakerForm;

	private $userForm;

	public function __construct( Model\Registration $registrationModel ) {
		$this->registrationModel = $registrationModel;
		$this->speakerForm = new SpeakerRegistration( $this, 'speaker', $registrationModel );
		$this->userForm = new UserRegistration( $this, 'user', $registrationModel );
	}

	public function startup() {
		parent::startup();
		$userId = $this->getUser()->getId();
		$this->conferree = $this->registrationModel->findCoferree( $userId );
	}

	public function renderTalksDetail( $talkId ) {
		$talk = $this->registrationModel->findTalk( $talkId );
		if ( ! $talk ) {
			throw new Nette\Application\BadRequestException( 'Talks not found', '404');
		}

		$this->template->talk = $talk;
		$this->template->speaker = $talk['speaker'];
	}

	public function renderProfil( $talkId ) {
		$this->template->conferree = $this->conferree;
		$this->template->talk = isset( $this->conferree['talk'] )? $this->conferree['talk'] : NULL;
	}

	public function createComponentTalkForm( $name ) {
		$form = new Form( $this, $name );
		$this->speakerForm->addTalksFields( $form->addContainer( 'talk') );

		if ( isset( $this->conferree['talk'] ) ) {
			$form->setDefaults( array(
				'talk' => $this->conferree['talk']
			) );
		}

		$form->onSubmit[] = array( $this, 'processUpdate');
		return $form;
	}

	public function createComponentUserForm( $name ) {
		$form = new Form( $this, $name );
		$usersContainer = $form->addContainer( 'user' );
		$this->userForm->addUsersFields( $usersContainer );
		$this->speakerForm->addUsersFields( $usersContainer );

		$form->setDefaults( array(
			'user' => $this->conferree
		) );

		$form->onSubmit[] = array( $this, 'processUpdate');
		return $form;
	}

	public function processUpdate( Form $form ) {
		$values = (array) $form->getValues();
		$user = $this->getUser();

		if ( isset( $values['user'] ) ) {
			$this->registrationModel->updateConferree( $this->conferree['user_id'], (array) $values['user'] );
		} else if ( isset( $values['talk'] ) && isset( $this->conferree['talk']['talk_id'] ) ) {
			$this->registrationModel->updateTalk( $this->conferree['talk']['talk_id'], (array) $values['talk'] );
		} else if ( isset( $values['talk'] ) ) {
			$this->registrationModel->createTalk( $user->getId(), (array) $values['talk'] );
		}
		$this->getPresenter()->sendResponse( new JsonResponse( array( 'updated' => true ) ) );
	}

	public function createComponentTalks( $name ) {
		return new TalksList( $this, $name, $this->registrationModel );
	}

	public function createComponentUsers( $name ) {
		return new UsersList( $this, $name, $this->registrationModel );
	}

}