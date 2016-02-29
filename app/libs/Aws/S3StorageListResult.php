<?php

namespace App\Aws;

use Nette\Object;

class S3StorageListResult {

	private $result;

	public function __construct( $s3ListResult ) {
		$this->validateResult( $s3ListResult );

		$this->result = $s3ListResult;
	}

	public function getObjects() {
		if( !isset( $this->result[ 'Contents' ] )) {
			return array();
		}
		return $this->result[ 'Contents' ];
	}

	public function getPrefixes() {
		$prefixes = array();
		if( isset( $this->result[ 'CommonPrefixes' ] ) ) {
			foreach ($this->result[ 'CommonPrefixes' ] as $prefixItem) {
				$prefixes[] = array(
					'Key' => $prefixItem[ 'Prefix' ],
				);
			}
		}
		return $prefixes;
	}

	private function validateResult( $result ) {
		if( ! $result instanceof \Guzzle\Service\Resource\Model ) {
			throw new \InvalidArgumentException( 'Argument is not valid Response from ListObject command' );
		}
	}
}