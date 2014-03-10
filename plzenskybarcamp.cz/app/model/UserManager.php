<?php

namespace App;

use Nette,
	Nette\Utils\Strings;


/**
 * Users management.
 */
class UserManager extends Nette\Object implements Nette\Security\IAuthenticator
{

	private $fb;

	public function __construct(\Facebook $fb)
	{
		$this->fb = $fb;
	}


	public function authenticate(array $credentials)
	{
		list($platform) = $credentials;
		if($platform == 'fb') {
			$user_id = $this->fb->getUser();
			if($user_id) {
				$user_profile = $this->fb->api('/me','GET');
				if( ! $user_profile) {
					throw new Nette\Security\AuthenticationException('User info request failed');
				}
				$id = $user_profile['id'];
				$name = $user_profile['name'];
				$email = $user_profile['email'];
			}
			else {
				throw new Nette\Security\AuthenticationException('No user identity');
			}
		}
		else {
			throw new Nette\Security\AuthenticationException('Unknown platform ' . $platform);
		}


		$profile = array(
			'id' => $id,
			'name' => $name,
			'email' => $email,
			'currentPlatform' => $platform,
			'platforms' => array(
				$platform => $user_profile,
			)
		);
		return new Nette\Security\Identity($id, NULL, $profile);
	}

}
