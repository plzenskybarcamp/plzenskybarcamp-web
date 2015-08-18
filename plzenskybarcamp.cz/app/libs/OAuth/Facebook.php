<?php

namespace App\OAuth;

use Nette\Http\Session,
	App\OAuth\Identity,
	App\OAuth\AuthenticationException,
	Nette\Diagnostics\Debugger,
	Facebook\GraphUser,
	Facebook\FacebookSession,
	Facebook\FacebookRequest,
	Facebook\FacebookRequestException,
	Facebook\FacebookRedirectLoginHelper;


class Facebook implements IClient
{
	const PLATFORM_ID = 'fb';
	private $session;

	public function __construct( $appConfig, Session $session ) {
		$this->session = $session->getSection( self::PLATFORM_ID );

		$appId = $appConfig[ 'appId' ];
		$appSecret = $appConfig[ 'secret' ];
		FacebookSession::setDefaultApplication($appId, $appSecret);
	}

	public function getAuthUrl( $redirectUrl, array $scope = array() ) {
		$this->session->redirectUrl = $redirectUrl;

		$client = new FacebookRedirectLoginHelper( $redirectUrl );
		return $client->getLoginUrl( $scope );
	}

	public function getIdentity() {
		$redirectUrl = $this->session->redirectUrl;

		$client = new FacebookRedirectLoginHelper( $redirectUrl );

		try {
			$fbSession = $client->getSessionFromRedirect( );

			if( $fbSession == NULL ) {
				throw new AuthenticationException("Facebook authentication failed", 0 );
			}

			$fbUser = (
				new FacebookRequest( $fbSession, 'GET', '/me' )
			)->execute()
			->getGraphObject( GraphUser::className());

			$fbUserPicture = (
				new FacebookRequest(
					$fbSession,
					'GET',
					'/me/picture',
					array(
						'redirect' => false,
						'height' => '200',
						'type' => 'normal',
						'width' => '200',
					)
				)
			)->execute()
			->getGraphObject();

		} catch ( FacebookRequestException $e ) {
			Debugger::log( $e );
			throw new AuthenticationException("Facebook authentication failed on getting access token", 0, $e );
		}

		$identity = new Identity (
			array(
				'id' => $fbUser->getId(),
				'name' => $fbUser->getName(),
				'email' => $fbUser->getEmail(),
				'picture_url' => $fbUserPicture->getProperty('url'),
			),
			self::PLATFORM_ID,
			$fbSession->getToken(),
			($fbUser->asArray() + array('picture'=>$fbUserPicture->asArray()))
		);

		return $identity;
	}

	public function getAppToken() {
		$params = array(
			'grant_type' => 'client_credentials'
		);
		return \Facebook\Entities\AccessToken::requestAccessToken($params);
	}
}
