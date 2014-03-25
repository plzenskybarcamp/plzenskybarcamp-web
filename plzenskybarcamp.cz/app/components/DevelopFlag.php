<?php

namespace App\Components;

class DevelopFlag {

private $is_develop;

	public function __construct( $is_develop ) {
		$this->is_develop = $is_develop;
	}

	public function isDevelop() {
		return $this->is_develop;
	}
}