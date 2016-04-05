<?php

namespace App\Presenters;

use Nette,
	Nette\Application\BadRequestException,
	App\Model\Shortlink,
	App\Model\ShortlinkNotFoundException;


/**
 * Homepage presenter.
 */
class ShortlinkPresenter extends BasePresenter
{

	private $shortlinkModel;

	public function __construct( Shortlink $shortlinkModel ) {
		$this->shortlinkModel = $shortlinkModel;
	}

	public function actionGo( $key, $utm = NULL )
	{
		try {
			$url = $this->shortlinkModel->getUrl( $key, $utm );
		}
		catch( ShortlinkNotFoundException $e) {
			throw new BadRequestException( "Shortlink \"$key\" not found", 404, $e );
		}

		$this->redirectUrl( $url );
	}

}
