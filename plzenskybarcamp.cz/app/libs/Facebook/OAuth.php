<?php

namespace App\Facebook;

use App\SessionStarter,
	App\OAuth\Identity,
	App\OAuth\AuthenticationException,
	Nette\Diagnostics\Debugger,
	Facebook\GraphUser,
	Facebook\FacebookSession,
	Facebook\FacebookRequest,
	Facebook\FacebookRequestException,
	Facebook\FacebookRedirectLoginHelper;


class OAuth
{
	const PLATFORM_ID = 'fb';
	private $sessionStarter;

	public function __construct( $appConfig, SessionStarter $sessionStarter ) {
		$this->sessionStarter = $sessionStarter;

		$appId = $appConfig[ 'appId' ];
		$appSecret = $appConfig[ 'secret' ];
		FacebookSession::setDefaultApplication($appId, $appSecret);
	}

	public function getAuthUrl( $redirectUrl, array $scope = array() ) {
		$this->sessionStarter->start();

		$client = new FacebookRedirectLoginHelper( $redirectUrl );
		return $client->getLoginUrl( $scope );
	}

	public function getIdentity( $redirectUrl ) {
		$this->sessionStarter->start();

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
}
