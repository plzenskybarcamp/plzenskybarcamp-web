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

		$this->template->fbLoginLink = $this->createFbLoginLink();

		$fb = $this->getContext()->getService( 'Facebook' );
		$logoutParams = array(
			'next' => $this->link('//logout!', array('redirect_url'=>$this->link('//this')))
		);
		$this->template->fbLogoutLink = $fb->getLogoutUrl($logoutParams);
	}

	public function handleLogin( $platform, $redirect_url ) {
		$user = $this->getUser();
		try {
			$user->login( $platform );
			$this->flashMessage ("Jsi přihlášen", 'success');
		} catch( \Nette\Security\AuthenticationException $exception ) {
			$this->flashMessage ("Tvoje přihlášení selhalo, omlouváme se.", 'error');
		}
		if($redirect_url) {
			$this->redirectUrl($redirect_url);
		}
		else {
			$this->redirect('Homepage:default');
		}
	}

	public function handleLogout( $platform, $redirect_url ) {
		$user = $this->getUser();
		$user->logout();
		$this->flashMessage ("Jsi odhlášen", 'success');
		if($redirect_url) {
			$this->redirectUrl($redirect_url);
		}
		else {
			$this->redirect('Homepage:default');
		}
	}

	protected function createFbLoginLink() {
		$fb = $this->getContext()->getService( 'Facebook' );
		$loginParams = array(
			'scope' => 'email',
			'redirect_uri' => $this->link('//login!', array('redirect_url'=>$this->link('//this'), 'platform'=>'fb'))
		);
		return $fb->getLoginUrl($loginParams);
	}

}
