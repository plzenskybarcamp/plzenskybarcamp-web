<?php

namespace App\Aws;

use Aws\Sdk as Sdk,
	Nette\Object;

class Aws extends Object {

	private $awsInstance;
	private $appConfig;

	public function __construct( $appConfig ) {
		$this->appConfig = $appConfig;
	}

	public function getS3() {
		return $this->getAwsInstance()->createS3();
	}

	public function getAwsInstance() {
		if( ! $this->awsInstance) {
			$this->awsInstance = new Sdk( $this->appConfig );
		}
		return $this->awsInstance;
	}
}
