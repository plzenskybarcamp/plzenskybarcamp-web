<?php

namespace App\OAuth;

use TwitterOAuth,
	App\OAuth\Identity,
	App\OAuth\AuthenticationException,
	Nette\Diagnostics\Debugger,
	Nette\Http\Session;


class Twitter implements IClient
{
	const PLATFORM_ID = 'tw';

	private $client;
	private $session;

	private $accessToken;

	public function __construct( $appConfig, Session $session ) {
		$consumerKey = $appConfig[ 'consumerKey' ];
		$consumerSecret = $appConfig[ 'consumerSecret' ];
		$this->client = new TwitterOAuth( $consumerKey, $consumerSecret );
		$this->session = $session->getSection( self::PLATFORM_ID );
	}

	public function getAuthUrl( $redirectUrl, array $scope = array() ) {
		$requestToken = $this->client->getRequestToken( $redirectUrl );

		$this->session->oauthToken = $requestToken['oauth_token'];
		$this->session->oauthTokenSecret = $requestToken['oauth_token_secret'];

		if($this->client->http_code != 200) {
			Debugger::log( "Unable to connect to Twitter OAuth API" );
			throw new Exception("Unable to connect to Twitter OAuth API", 1);
		}
		/* Build authorize URL and redirect user to Twitter. */
		return $this->client->getAuthorizeURL( $requestToken['oauth_token'], TRUE );
	}

	public function verifyAuthentication( $oauthToken, $oauthVerifier ) {
		if ( $oauthToken !== $this->session->oauthToken ) {
			Debugger::log( "Twitter authentication failed because OAuth token not match" );
			throw new AuthenticationException( "Twitter authentication failed because OAuth token not match", 0 );
		}

		$this->client->setOAuthToken( $this->session->oauthToken, $this->session->oauthTokenSecret );

		/* Request access tokens from twitter */
		$this->accessToken = $this->client->getAccessToken( $oauthVerifier );

		if( $this->client->http_code != 200) {
			Debugger::log( "Unable to getAccessToken to Twitter OAuth API" );
			throw new Exception("Unable to getAccessToken to Twitter OAuth API", 1);
		}

		unset( $this->session->oauthToken );
		unset( $this->session->oauthTokenSecret );
	}

	public function getIdentity() {
		$content = $this->client->get('account/verify_credentials');

		if (property_exists($content, "error")) {
			Debugger::log( "Unable to get user info from Twitter API ($content->error)" );
				throw new Exception("Unable to get user info from Twitter API ($content->error)", 1);
		}

		//Fix id type
		$content->id = (string) $content->id;
		$content->email = isset($content->email) ? $content->email : NULL;

		$identity = new Identity (
			array(
				'id' => $content->id,
				'name' => $content->name,
				'email' => $content->email,
				'picture_url' => $content->profile_image_url_https,
			),
			self::PLATFORM_ID,
			$this->accessToken,
			$this->object_to_array( $content )
		);

		return $identity;
	}

	private function object_to_array($object) {
		if (is_object($object)) {
			return array_map(array( $this, __FUNCTION__), get_object_vars($object));
		} else if (is_array($object)) {
			return array_map(array( $this, __FUNCTION__), $object);
		} else {
			return $object;
		}
	}
}
