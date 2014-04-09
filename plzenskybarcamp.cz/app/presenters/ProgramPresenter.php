<?php

namespace App\Presenters;


/**
 * Homepage presenter.
 */
class ProgramPresenter extends BasePresenter
{


	public function renderList( )
	{

	}

	public function actionPdf( )
	{
		$response = new \Nette\Application\Responses\FileResponse( __DIR__.'/../../www/files/program_06_04.pdf', 'program.pdf', 'application/pdf' );
		$this->sendResponse( $response );
	}

}
