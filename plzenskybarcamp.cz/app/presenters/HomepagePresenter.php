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

	public function createComponentRegistration( $name ) {
		$session = $this->getContext()->getService("session")->getSection("vip");

		$token = NULL;
		if( isset( $session->token ) ){
			$token = $session->token;
		}

		return new Main( $this, $name, $this->registrationModel, $this->configModel, $token );
	}

	public function renderNoTrack() {
		setcookie('DoNotGaTrack', '1', time()+(60*60*24*60), '/', $_SERVER['HTTP_HOST'], TRUE, FALSE);
		$this->flashMessage('DEVELOPER MODE: Your browser is now excluded from Google Analytics tracking');
		$this->redirect('Homepage:');
	}
}
