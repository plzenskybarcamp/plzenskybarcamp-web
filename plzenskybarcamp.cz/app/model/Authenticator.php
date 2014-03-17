<?php

namespace App;

use Nette,
	Nette\Utils\Strings,
	App\Model;


/**
 * Users management.
 */
class Authenticator extends Nette\Object implements Nette\Security\IAuthenticator
{

	public function authenticate(array $credentials) {
		$id = $credentials[0]['id'];
		$data = $credentials[0]['data'];

		$identity = new \App\Components\Registration\Identity($id, NULL, $data);

		return $identity;
	}
}