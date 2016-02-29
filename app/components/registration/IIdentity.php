<?php

namespace App\Components\Registration;

interface IIdentity {

	function isRegistered();

	function isSpeaker();
}