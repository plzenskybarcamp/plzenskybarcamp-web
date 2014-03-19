<?php

namespace App\Components\Lists;

use Nette\Application\UI\Control;
use Nette\Application\Responses\JsonResponse;

class TalksList extends Control {

	private $registrationModel;

	public function __construct( $parent, $name, $registrationModel ) {
		$this->registrationModel = $registrationModel;
	}

	public function render() {
		$this->template->registerHelper('twitterize', array( 'App\Components\Helpers', 'twitterize'));
		$this->template->registerHelper('biggerTwitterPicture', array( 'App\Components\Helpers', 'biggerTwitterPicture'));
		$this->template->setFile( __DIR__ . '/templates/talksList.latte');
		$talks = $this->registrationModel->getTalks();
		$this->template->talks = $talks;
		$this->template->talksCount = $talks->count();
		$this->template->currentUser = $this->getPresenter()->getUser();
		$this->template->render();
	}

	public function handleaddVote() {
		throw new \Nette\NotImplementedException('Security issue: Method is not available now');

		$talkId = $this->getPresenter()->getParameter( 'talkId' );
		$this->validRequest( $talkId );
		$this->registrationModel->addVote( $talkId, $this->getPresenter()->getUser()->getId() );
		$this->sendAjaxResponse( array( 'votes_count' => $this->registrationModel->getVotesCount( $talkId ) ) );
	}

	public function handleremoveVote() {
		throw new \Nette\NotImplementedException('Security issue: Method is not available now');
		
		$talkId = $this->getPresenter()->getParameter( 'talkId' );
		$this->validRequest( $talkId );
		$this->registrationModel->removeVote( $talkId, $this->getPresenter()->getUser()->getId() );
		$this->sendAjaxResponse( array( 'votes_count' => $this->registrationModel->getVotesCount( $talkId ) ) );
	}

	private function sendAjaxResponse( $data ) {
		$this->getPresenter()->sendResponse( new JsonResponse( $data ) );
	}

	private function validRequest( $talkId ) {
		if ( !$this->getPresenter()->isAjax() || !$this->registrationModel->hasTalk( $talkId ) ) {
			throw new \Nette\Application\BadRequestException( 'Not valid request', '404');
		}
	}
}