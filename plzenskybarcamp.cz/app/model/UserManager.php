<?php

namespace App;

use Nette,
	Nette\Utils\Strings,
	App\Model;


/**
 * Users management.
 */
class UserManager extends Nette\Object implements Nette\Security\IAuthenticator
{

	private $fb;
	private $registration;

	const PLATFORM_FACEBOOK = 'fb';
	const PLATFORM_TWITTER = 'tw';

	public function __construct(\Facebook $fb, Model\Registration $registration)
	{
		$this->fb = $fb;
		$this->registration = $registration;
	}


	public function authenticate(array $credentials)
	{
		die("tady to nemělo být!");
		list($platform) = $credentials;
		if($platform == self::PLATFORM_FACEBOOK) {
			$user_id = $this->fb->getUser();
			if( ! $user_id) {
				throw new Nette\Security\AuthenticationException('No user FB identity');
			}

			$conferee = $this->getUserRegistration( $platform, $user_id );
			$profile = $this->getUserIdentity( $conferee );

			$profile = $profile ?: $this->getUserFbData( $user_id );

			$profile['currentPlatform'] = $platform;
		}
		else {
			throw new Nette\Security\AuthenticationException('Unknown platform ' . $platform);
		}

		$identity = new \App\Components\Registration\Identity($profile['id'], NULL, $profile);

		if( $conferee ) {
			$identity->conferee = $conferee;
			$identity->talk = $this->getUserTalk( $conferee );
		}
		return $identity;
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

	private function getUserFbData( $user_id ) {
		$platform_profile = $this->fb->api('/me','GET');
		if( ! $platform_profile) {
			throw new Nette\Security\AuthenticationException('User info request failed');
		}
		$id = hash("crc32b", uniqid("fb", TRUE));
		$name = @$platform_profile['name'];
		$email = @$platform_profile['email'];

		$picture = $this->fb->api(
			"/me/picture",
			"GET",
			array (
				'redirect' => false,
				'height' => '180',
				'type' => 'normal',
				'width' => '180',
			)
		);
		$picture_url = @$picture['data']['url'];

		$platform_profile['picture'] = $picture;

		return $this->buildProfile( $id, $name, $email, $picture_url, self::PLATFORM_FACEBOOK, $platform_profile );
	}

	private function buildProfile( $id, $name, $email, $picture_url, $platform, $platform_profile ) {
		return array(
			'id' => $id,
			'name' => $name,
			'email' => $email,
			'picture_url' => $picture_url,
			'platforms' => array(
				$platform => $platform_profile,
			)
		);
	}

}
