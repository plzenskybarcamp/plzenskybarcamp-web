<?php

namespace App\Presenters;

use Nette,
	App\Model,
	App\Model\Registration,
	App\Components\Registration\Identity,
	App\Facebook\OAuth as Facebook,
	App\OAuth\AuthenticationException;


/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{

	private $facebook;
	private $twitter;
	private $registration;

	public function __construct( Facebook $facebook, \TwitterOAuth $twitter, Registration $registration ){
		$this->facebook = $facebook;
		$this->twitter = $twitter;
		$this->registration = $registration;
	}

	public function actionInFb( $redirect = NULL ) {
		$redirectUrl = $this->link("//processFb", array( 'redirect'=> $redirect ) );
		$this->redirectUrl( $this->facebook->getAuthUrl( $redirectUrl, array( 'email' ) ) );
	}

	public function actionProcessFb( $redirect = NULL ) {
		$redirectUrl = $this->link("//processFb", array( 'redirect'=> $redirect ) );

		try {
			$oAuthIdentity = $this->facebook->getIdentity( $redirectUrl );
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
		$request_token = $this->twitter->getRequestToken(
			$this->link("//processTw")
		);
		$session = $this->getContext()->getService("session")->getSection("twitter");

		$session->oauth_token = $token = $request_token['oauth_token'];
		$session->oauth_token_secret = $request_token['oauth_token_secret'];
		$session->redirect_url = $redirect_url;

		/* If last connection failed don't display authorization link. */
		switch ($this->twitter->http_code) {
			case 200:
				/* Build authorize URL and redirect user to Twitter. */
				$url = $this->twitter->getAuthorizeURL( $token, TRUE );
				$this->redirectUrl( $url );
				break;
			default:
				/* Show notification if something went wrong. */
				$this->flashMessage('Could not connect to Twitter.', "error");
				$this->redirect("in");
		}
	}

	public function actionProcessTw( $oauth_token, $oauth_verifier ) {
		$session = $this->getContext()->getService("session")->getSection("twitter");

		$redirect_url = $session->redirect_url;

		if ( $oauth_token !== $session->oauth_token ) {
			$this->flashMessage("Athentication error: token is too old", "error");
			$this->redirect("in");
		}

		$params = $this->getContext()->getParameters();

		/* Create TwitteroAuth object with app key/secret
		* and token key/secret from default phase */
		$twitter = new \TwitterOAuth(
			$params["twitter"]["appConfig"]["key"],
			$params["twitter"]["appConfig"]["secret"],
			$session->oauth_token,
			$session->oauth_token_secret
		);

		/* Request access tokens from twitter */
		$access_token = $twitter->getAccessToken($oauth_verifier);

		/* Save the access tokens */
		$session->access_token = $access_token;

		/* Remove no longer needed request tokens */
		unset($session->oauth_token);
		unset($session->oauth_token_secret);

		if (200 != $twitter->http_code) {
			/* Save HTTP status for error dialog on connnect page.*/
			$this->flashMessage("Autentication error: get access token failed.", "error");
			$this->redirect("in");
		}

		$user_id = $access_token['user_id'];

		$conferee = $this->getUserRegistration( 'tw', $user_id );
		$profile = $this->getUserIdentity( $conferee );

		if( ! $profile ) {

			$content = $twitter->get('account/verify_credentials');

			$id = hash("crc32b", uniqid("fb", TRUE));

			if (property_exists($content, "error")) {
				$this->flashMessage("Twitter error: " . $content->error, "error");
				$this->redirect("in");
			} else {

				//Fix id type
				$content->id = (string) $content->id;

				$profile = $this->buildProfile(
					$id,
					$content->name,
					NULL,
					$content->profile_image_url_https,
					'tw',
					$content
				);
			}
		}

		$this->user->login(array('id'=>$profile['id'], 'data'=>$profile));

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
			return $conferee;
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
