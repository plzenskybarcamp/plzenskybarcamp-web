<?php

namespace App\Components\Registration;

use Nette\Security as NS;

class Identity extends NS\Identity implements IIdentity, NS\IIdentity {

	private $id;
	private $conferee;
	private $talk;

	/**
	* Overriding Nette method (we need string of id)
	**/
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	* Overriding Nette method
	**/
	public function getId()
	{
		return $this->id;
	}

	public function setConferee( $conferee ) {
		$this->conferee = $conferee;
	}

	public function getConferee() {
		return $this->conferee;
	}

	public function isRegistered() {
		return (bool) $this->conferee;
	}

	public function setTalk( $talk ) {
		$this->talk = $talk;
	}

	public function getTalk() {
		return $this->talk;
	}

	public function isSpeaker() {
		return (bool) $this->talk;
	}
}