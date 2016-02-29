<?php

namespace App\Presenters;

use Nette,
	App\Model,
	App\Model\Registration,
	App\Components\Registration\Identity,
	App\OAuth\Facebook,
	App\OAuth\Twitter,
	App\OAuth\Exception as OAuthException,
	App\OAuth\AuthenticationException,
	MongoDB\Model\MongoDbSanitizer;


/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{

	private $facebook;
	private $twitter;
	private $registration;

	public function __construct( Facebook $facebook, Twitter $twitter, Registration $registration ){
		$this->facebook = $facebook;
		$this->twitter = $twitter;
		$this->registration = $registration;
	}

	public function actionInFb( $redirect = NULL ) {
		$redirectUrl = $this->link("//processFb" );
		$this->redirectUrl( $this->facebook->getAuthUrl( $redirectUrl, array( 'email' ) ) );
	}

	public function actionProcessFb( ) {
		$redirectUrl = $this->link("//processFb");

		try {
			$oAuthIdentity = $this->facebook->getIdentity( );
		} catch ( AuthenticationException $e ) {
			$this->flashMessage("Omlouváme se, ale tvoje přihlášení se nepovedlo. Zkus to znovu, nebo nám dej vědět.", "error");
			$this->redirect("in");
		}

		$conferee = $this->getUserRegistration( 'fb', $oAuthIdentity->plartformId );
		$profile = $this->getUserIdentity( $conferee );

		if( ! $profile ) {
			$id = hash("crc32b", uniqid("fb", TRUE));
			$name = $oAuthIdentity->name;
			$email = $oAuthIdentity->email;
			$picture_url = $oAuthIdentity->getPictureUrl();

			$profile = $oAuthIdentity->toArray();
			$profile['id'] = $id;
		}

		$identity = new Identity( $profile['id'], NULL, $profile );
		$this->user->login( $identity );

		if( $conferee ) {
			$identity = $this->user->identity;
			$identity->conferee = $conferee;
			$identity->talk = $this->getUserTalk( $conferee );
			$this->flashMessage("Vítej zpět, ty jsi už registrovaný, tešíme se na Tebe v Plzni", "success");
		}
		else {
			$this->flashMessage("Yep. Pro účast se nezapomeň ještě registrovat tlačítkem „Potvrzuji svou účast“", "success");
		}

		$this->redirect("Homepage:default");
	}

	public function actionInTw( $redirect_url = NULL ) {
		$this->redirectUrl( $this->twitter->getAuthUrl(
			$this->link("//processTw")
		) );
	}

	public function actionProcessTw( $oauth_token, $oauth_verifier ) {

		try {
			$this->twitter->verifyAuthentication( $oauth_token, $oauth_verifier );
			$oAuthIdentity = $this->twitter->getIdentity( );
		} catch ( OAuthException $e ) {
			$this->flashMessage("Omlouváme se, ale tvoje přihlášení se nepovedlo. Zkus to znovu, nebo nám dej vědět.", "error");
			$this->redirect("in");
		}

		$conferee = $this->getUserRegistration( 'tw', $oAuthIdentity->plartformId );
		$profile = $this->getUserIdentity( $conferee );

		if( ! $profile ) {
			$id = hash("crc32b", uniqid("fb", TRUE));
			$name = $oAuthIdentity->name;
			$email = $oAuthIdentity->email;
			$picture_url = $oAuthIdentity->getPictureUrl();

			$profile = $oAuthIdentity->toArray();
			$profile['id'] = $id;
		}

		$identity = new Identity( $profile['id'], NULL, $profile );
		$this->user->login( $identity );

		if( $conferee ) {
			$identity = $this->user->identity;
			$identity->conferee = $conferee;
			$identity->talk = $this->getUserTalk( $conferee );
			$this->flashMessage("Vítej zpět, ty jsi už registrovaný, tešíme se na Tebe v Plzni", "success");
		}
		else {
			$this->flashMessage("Yep. Pro účast se nezapomeň ještě registrovat tlačítkem „Potvrzuji svou účast“", "success");
		}

		$this->redirect("Homepage:default");
	}

	private function getUserRegistration( $platform, $id ) {
		$conferee = $this->registration->findCoferreeByPlatform( $platform, $id );

		if( isset( $conferee[ 'identity' ] ) ) {
			return MongoDbSanitizer::sanitizeDocument( $conferee );
		}
		else return NULL;
	}

	private function getUserIdentity( $conferee ) {
		if( isset( $conferee[ 'identity' ] ) ) {
			return $conferee[ 'identity' ];
		}
		else return NULL;
	}

	private function getUserTalk( $conferee ) {
		if( isset( $conferee[ 'talk' ] ) ) {
			return $conferee[ 'talk' ];
		}
		else return NULL;
	}

	private function buildProfile( $id, $name, $email, $picture_url, $platform, $platform_profile ) {
		return array(
			'id' => $id,
			'name' => $name,
			'email' => $email,
			'picture_url' => $picture_url,
			'current_platform' => $platform,
			'platforms' => array(
				$platform => $platform_profile,
			)
		);
	}


	public function actionOut()
	{
		$this->getUser()->logout( TRUE );
		$this->flashMessage('Jsi odhlášen');
		$this->redirect('Homepage:default');
	}

}
