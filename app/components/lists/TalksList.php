<?php

namespace App\Components\Lists;

use Nette\Application\UI\Control;
use Nette\Application\Responses\JsonResponse;

class TalksList extends Control {

	private $registrationModel;
	private $configModel;

	public function __construct( $parent, $name, $registrationModel, $configModel ) {
		parent::__construct($parent, $name);
		$this->registrationModel = $registrationModel;
		$this->configModel = $configModel;
	}

	public function render( $ranking ) {
		$this->template->registerHelper('twitterize', array( 'App\Components\Helpers', 'twitterize'));
		$this->template->registerHelper('biggerTwitterPicture', array( 'App\Components\Helpers', 'biggerTwitterPicture'));
		$this->template->setFile( __DIR__ . '/templates/talksList.latte');

		$sort = NULL;
		if( $ranking ) {
			$sort = array( 'votes_count' => -1 );
		}
		$talks = $this->registrationModel->getTalks( $sort );
		$this->template->ranking = $ranking;
		$this->template->talks = $talks->toArray();
		$this->template->talksCount = count($this->template->talks);
		$this->template->currentUser = $this->getPresenter()->getUser();
		$this->template->isVotingOpen = $this->configModel->getConfig('isVotingOpen');
		$this->template->isVoteShows = $this->configModel->getConfig('isVoteShows');
		$this->template->talksCapatity = $this->configModel->getConfig('talksCapatity');
		$this->template->render();
	}

	public function handleAddVote() {
		$talkId = $this->getPresenter()->getParameter( 'talkId' );
		$userId = $this->getPresenter()->getUser()->getId();
		$this->validRequest( $talkId );
		if( $this->registrationModel->isVoted( $talkId, $userId ) ) {
			throw new \Nette\Application\ForbiddenRequestException( 'Talk already voted' );
		}
		$this->registrationModel->addVote( $talkId, $userId );
		$this->sendAjaxResponse( array( 'votes_count' => $this->registrationModel->getVotesCount( $talkId ) ) );
	}

	public function handleRemoveVote() {
		$talkId = $this->getPresenter()->getParameter( 'talkId' );
		$userId = $this->getPresenter()->getUser()->getId();
		$this->validRequest( $talkId );
		if( ! $this->registrationModel->isVoted( $talkId, $userId ) ) {
			throw new \Nette\Application\ForbiddenRequestException( 'Talk is not voted' );
		}
		$this->registrationModel->removeVote( $talkId, $userId );
		$this->sendAjaxResponse( array( 'votes_count' => $this->registrationModel->getVotesCount( $talkId ) ) );
	}

	private function sendAjaxResponse( $data ) {
		$this->getPresenter()->sendResponse( new JsonResponse( $data ) );
	}

	private function validRequest( $talkId ) {

		if( !$this->configModel->getConfig('isVotingOpen')) {
			throw new \Nette\Application\ForbiddenRequestException( 'Sorry, hlasování skončilo.' );
		}

		if ( !$this->getPresenter()->getUser()->isLoggedIn() ) {
			throw new \Nette\Application\ForbiddenRequestException( 'User must be logged' );
		}
		if ( 0 && !$this->getPresenter()->isAjax() ) {
			throw new \Nette\Application\ForbiddenRequestException( 'Non Ajax request' );
		}
		if ( !$this->registrationModel->hasTalk( $talkId ) ) {
			throw new \Nette\Application\BadRequestException( 'Unknown talk ID' );
		}
	}
}