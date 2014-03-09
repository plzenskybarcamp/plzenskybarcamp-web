<?php

namespace App\Components\Lists;

use Nette\Application\UI\Control;
use Nette\Application\Responses\JsonResponse;
use Nette\Utils\Paginator;

class UsersList extends Control {

	private $registrationModel;

	public function __construct( $parent, $name, $registrationModel ) {
		$this->registrationModel = $registrationModel;
	}

	public function render() {
		$this->createControlTemplate()->render();
	}

	private function createControlTemplate() {
		$this->template->setFile( __DIR__ . '/templates/list.latte');
		$users = $this->registrationModel->getConferrees();

		$currentPage = $this->getParameter( 'page', 1 );
		$limit = 10;
		$paginator = $this->createPaginator( $currentPage, $limit, $users->count() );

		$this->template->isAjax = $this->getPresenter()->isAjax();
		$this->template->users = $users->skip( $paginator->getOffset() )->limit( $limit );
		$this->template->pagesCount = $paginator->getPageCount();
		return $this->template;
	}

	private function createPaginator( $currentPage, $limit, $totalCount ) {
		$paginator = new Paginator();
		$paginator->setPage( $currentPage );
		$paginator->setItemCount( $totalCount );
		$paginator->setItemsPerPage( $limit );
		return $paginator;
	}

	public function handlertoJson() {
		if( $this->getPresenter()->isAjax() ) {
			$data = array( 'html' => $this->createControlTemplate()->__toString() );
			$this->getPresenter()->sendResponse( new JsonResponse( $data ) );
		}
	}
}