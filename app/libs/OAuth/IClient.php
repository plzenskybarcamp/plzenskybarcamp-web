<?php

namespace App\OAuth;

interface IClient
{
	function getAuthUrl( $redirectUrl, array $scope = array() );

	function getIdentity();

}
