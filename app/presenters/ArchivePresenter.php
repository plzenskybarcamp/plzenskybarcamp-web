<?php

namespace App\Presenters;

use Nette,
	Nette\Http\IResponse,
	Nette\Application\BadRequestException,
	Nette\Application\Responses\TextResponse,
	App\Model\ArchiveLoader;


/**
 * Homepage presenter.
 */
class ArchivePresenter extends BasePresenter
{

	private $loader;
	private $response;

	public function __construct( ArchiveLoader $loader, IResponse $response ) {
		$this->loader = $loader;
		$this->response = $response;
	}

	public function renderDefault( $year, $path ) {
		$path = $this->preparePath( $path, $year );
		$output = $this->loader->load( $path );
		if( $output['status'] != 200 ) {
			throw new BadRequestException( 'Cannot load archived page ' . $path, $output['status']);
		}
		$this->sendResponse( new TextResponse( $output['content'] ) );
	}

	private function preparePath( $path, $year ) {
		return "/$year" . rtrim("/$path", '/') . '.html';

	}
}
