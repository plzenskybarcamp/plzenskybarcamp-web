<?php

namespace App;

use App\Components\Registration\Identity;
use Nette;


/**
 * Users management.
 */
class Authenticator implements Nette\Security\IAuthenticator
{
    public function authenticate(array $credentials): Identity
    {
        $id = $credentials[0]['id'];
        $data = $credentials[0]['data'];

        return new Identity($id, null, $data);
    }
}