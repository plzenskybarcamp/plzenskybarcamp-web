<?php

namespace App;

class SessionStarter {
	private $session;

	public function __construct( \Nette\Http\Session $session ) {
		$this->session = $session;
	}

	public function start() {
		$this->session->start();
	}
}