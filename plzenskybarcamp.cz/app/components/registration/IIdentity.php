<?php

namespace App\Components\Registration;

interface IIdentity {

	function isLoggedIn();

	function isRegistered();

	function isSpeaker();
}