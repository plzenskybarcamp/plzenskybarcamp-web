<?php

namespace App\Aws;

use Aws\Sdk;
use Nette\SmartObject;

class Aws {
    use SmartObject;

	private $awsInstance;
	private $appConfig;

	public function __construct( $appConfig ) {
		$this->appConfig = $appConfig;
	}

	public function getS3(): \Aws\S3\S3Client
    {
		return $this->getAwsInstance()->createS3();
	}

	public function getSns(): \Aws\Sns\SnsClient
    {
		return $this->getAwsInstance()->createSns();
	}

	public function getAwsInstance(): Sdk
    {
		if( ! $this->awsInstance) {
			$this->awsInstance = new Sdk( $this->appConfig );
		}
		return $this->awsInstance;
	}
}
