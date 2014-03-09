<?php

namespace App\Presenters;

use Nette,
	App\Model,
	App\Components\Registration\Main,
	App\Components\Lists\TalksList;


/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

	private $registrationModel;

	public function __construct( Model\Registration $registrationModel ) {
		$this->registrationModel = $registrationModel;
	}

	public function renderDefault()
	{

	}

	public function createComponentRegistration( $name ) {
		return new Main( $this, $name, $this->registrationModel );
	}

	public function createComponentTalksList( $name ) {
		return new TalksList( $this, $name, $this->registrationModel );
	}


}
