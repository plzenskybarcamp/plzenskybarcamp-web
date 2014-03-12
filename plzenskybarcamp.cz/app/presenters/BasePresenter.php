<?php

namespace App\Presenters;

use Nette,
	App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

	public function beforeRender() {
		$this->template->host = $this->context->httpRequest->url->host;

		$fb = $this->getContext()->getService( 'Facebook' );
		$loginParams = array(
			'scope' => 'email',
			'redirect_uri' => $this->link('//login!', array('redirect_url'=>$this->link('//this'), 'platform'=>'fb'))
		);
		$this->template->fbLoginLink = $fb->getLoginUrl($loginParams);

		$logoutParams = array(
			'next' => $this->link('//logout!', array('redirect_url'=>$this->link('//this')))
		);
		$this->template->fbLogoutLink = $fb->getLogoutUrl($logoutParams);
	}

	public function handleLogin( $platform, $redirect_url ) {
		$user = $this->getUser();
		try {
			$user->login( $platform );
		} catch( \Nette\Security\AuthenticationException $exception ) {
			$this->flashMessage ("Vaše přihlášení selhalo, omlouváme se.");
		}
		$this->redirectUrl($redirect_url);
	}

	public function handleLogout( $platform, $redirect_url ) {
		$user = $this->getUser();
		$user->logout();
		$this->redirectUrl($redirect_url);
	}

}
