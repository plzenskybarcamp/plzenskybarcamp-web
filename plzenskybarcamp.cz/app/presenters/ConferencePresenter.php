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

		if($userId) {
			$this->conferree = $this->registrationModel->findCoferree( $userId );
		}
	}

	private function isPublicLink( $link ) {
		return (bool) $link[ 'is_public' ];
	}

	private function youtubizeLink( $link, $campainId ) {
		$matches = NULL;
		if( preg_match( '~youtu\\.?be(?:\\.com)?/(?:watch\\?v=)?([-_a-z0-9]{8,15})~i', $link[ 'url' ], $matches )) {
			$link[ 'embed' ] = $this->buildCampainUrl(
				"//www.youtube.com/embed/$matches[1]",
				'yt-video-embed',
				$campainId
			);
			$link[ 'url' ] = $this->buildCampainUrl(
				$link[ 'url' ],
				'yt-video-youtube',
				$campainId
			);
		}

		return $link;
	}

	private function buildCampainUrl( $url, $medium, $campainId ) {
		$postfix = "utm_source=pbc-web&utm_medium=$medium&utm_content=$campainId&utm_campaign=talk-detail";
		return $url . ( strpos( $url, '?' ) !== false ? '&' : '?' ) . $postfix;
	}

	public function renderTalksDetail( $talkId ) {
		$talk = $this->registrationModel->findTalk( $talkId );
		if ( ! $talk ) {
			throw new Nette\Application\BadRequestException( 'Talks not found', '404');
		}

		$this->template->publicPresentations = array();
		if( isset( $talk['presentations'] ) ) {
			$this->template->publicPresentations = array_filter(
				$talk['presentations'],
				array( $this, 'isPublicLink')
			);
		}

		$this->template->publicMovies = array();
		if( isset( $talk['movies'] ) ) {
			foreach( $talk['movies'] as $movieId => $movie ) {
				if( ! $this->isPublicLink( $movie ) ) {
					continue;
				}

				$this->template->publicMovies[ $movieId ] = $this->youtubizeLink( $movie, $movieId );
			}
		}

		$this->template->registerHelper('twitterize', array( 'App\Components\Helpers', 'twitterize'));
		$this->template->registerHelper('biggerTwitterPicture', array( 'App\Components\Helpers', 'biggerTwitterPicture'));
		$this->template->talk = $talk;
		$this->template->speaker = $talk['speaker'];
	}

	public function renderProfil( $talkId ) {
		if( ! $this->conferree ) {
			$this->flashMessage('Omlouváme se, ale profil návštěvníka je dostupný až po registraci', 'error');
			$this->redirect('Homepage:default');
		}
		$this->template->registerHelper('biggerTwitterPicture', array( 'App\Components\Helpers', 'biggerTwitterPicture'));
		$this->template->conferree = $this->conferree;
		$this->template->talk = isset( $this->conferree['talk'] )? $this->conferree['talk'] : NULL;
	}

	public function createComponentTalkForm( $name ) {
		$form = new Form( $this, $name );
		$form->setRenderer( new \App\Components\CustomFormRenderer );

		$this->speakerForm->addTalksFields( $form->addContainer( 'talk') );

		if ( isset( $this->conferree['talk'] ) ) {
			$form->setDefaults( array(
				'talk' => $this->conferree['talk']
			) );
		}

		$form->onSuccess[] = array( $this, 'processUpdate');
		return $form;
	}

	public function createComponentUserForm( $name ) {
		$form = new Form( $this, $name );
		$form->setRenderer( new \App\Components\CustomFormRenderer );
		$usersContainer = $form->addContainer( 'user' );
		$this->userForm->addUsersFields( $usersContainer );
		$this->speakerForm->addUsersFields( $usersContainer );

		$form->setDefaults( array(
			'user' => $this->conferree
		) );

		$form->onSuccess[] = array( $this, 'processUpdate');
		return $form;
	}

	public function processUpdate( Form $form ) {
		$values = (array) $form->getValues();
		$user = $this->getUser();

		if ( isset( $values['user'] ) ) {
			$this->registrationModel->updateConferree( $this->conferree['_id'], (array) $values['user'] );
		} else if ( isset( $values['talk'] ) && isset( $this->conferree['talk']['_id'] ) ) {
			$this->registrationModel->updateTalk( $this->conferree['talk']['_id'], (array) $values['talk'] );
		} else if ( isset( $values['talk'] ) ) {
			$this->registrationModel->createTalk( $user->getId(), (array) $values['talk'] );
		}

		$this->updateUserIdentity();
		$this->getPresenter()->sendResponse( new JsonResponse( array( 'updated' => true ) ) );
	}

	public function createComponentTalks( $name ) {
		return new TalksList( $this, $name, $this->registrationModel );
	}

	public function createComponentUsers( $name ) {
		return new UsersList( $this, $name, $this->registrationModel );
	}

	private function updateUserIdentity() {
		$userId = $this->getUser()->id;
		$identity = $this->getUser()->identity;

		$conferee = $this->registrationModel->findCoferree( $userId );
		$identity->conferee = $conferee;

		if( isset( $conferee['talk'] ) ) {
			$identity->talk = $conferee['talk'];
		}
	}

}