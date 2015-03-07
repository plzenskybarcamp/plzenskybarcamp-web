<?php

namespace App\Components\Lists;

use Nette\Application\UI\Control;
use Nette\Application\Responses\JsonResponse;

class TalksList extends Control {

	private $registrationModel;

	public function __construct( $parent, $name, $registrationModel ) {
		parent::__construct($parent, $name);
		$this->registrationModel = $registrationModel;
	}

	public function render( $ranking ) {
		$this->template->registerHelper('twitterize', array( 'App\Components\Helpers', 'twitterize'));
		$this->template->registerHelper('biggerTwitterPicture', array( 'App\Components\Helpers', 'biggerTwitterPicture'));
		$this->template->setFile( __DIR__ . '/templates/talksList.latte');

		$this->template->robot = (($_SERVER['REMOTE_ADDR'] == '31.170.164.27') || isset($_GET['try']));

		$sort = NULL;
		if( $ranking ) {
			$sort = array( 'votes_count' => -1 );
		}
		$talks = $this->registrationModel->getTalks( $sort );
		$this->template->ranking = $ranking;
		$this->template->talks = $talks;
		$this->template->talksCount = $talks->count();
		$this->template->currentUser = $this->getPresenter()->getUser();
		$this->template->render();
	}

	public function handleaddVote() {
		$talkId = $this->getPresenter()->getParameter( 'talkId' );
		$this->validRequest( $talkId );
		$this->registrationModel->addVote( $talkId, $this->getPresenter()->getUser()->getId() );
		$this->sendAjaxResponse( array( 'votes_count' => $this->registrationModel->getVotesCount( $talkId ) ) );
	}

	public function handleremoveVote() {
		$talkId = $this->getPresenter()->getParameter( 'talkId' );
		$this->validRequest( $talkId );
		$this->registrationModel->removeVote( $talkId, $this->getPresenter()->getUser()->getId() );
		$this->sendAjaxResponse( array( 'votes_count' => $this->registrationModel->getVotesCount( $talkId ) ) );
	}

	private function sendAjaxResponse( $data ) {
		$this->getPresenter()->sendResponse( new JsonResponse( $data ) );
	}

	private function validRequest( $talkId ) {
		if ( $this->getPresenter()->getUser()->isLoggedIn() && !$this->getPresenter()->isAjax() || !$this->registrationModel->hasTalk( $talkId ) ) {
			throw new \Nette\Application\BadRequestException( 'Not valid request', '404');
		}
	}
}