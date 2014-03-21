<?php

namespace App\Presenters;

use Nette,
	App\Model,
	App\Components\Registration\Main,
	App\Components\Lists\UsersList,
	App\Components\Lists\TalksList;


/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

	private $registrationModel;
	private $configModel;

	public function __construct( Model\Registration $registrationModel, Model\Config $configModel ) {
		$this->registrationModel = $registrationModel;
		$this->configModel = $configModel;
	}

	public function renderDefault()
	{

	}

	public function createComponentRegistration( $name ) {
		$session = $this->getContext()->getService("session")->getSection("vip");
		$token = $session->token;
		return new Main( $this, $name, $this->registrationModel, $this->configModel, $this->createFbLoginLink(), $token );
	}
}
