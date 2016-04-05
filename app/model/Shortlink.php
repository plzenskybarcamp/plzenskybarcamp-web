<?php

namespace App\Model;

class Shortlink {

	private $configCollection;
	private $configs;
	private $defaultOptions;


	public function __construct( $mongoConfig ) {
		$manager = new \MongoDB\Driver\Manager( $mongoConfig['uri'] );
		$dbName = $mongoConfig['database'];
		$this->configCollection = new \MongoDB\Collection($manager, $dbName, 'shortlink');
		$this->defaultOptions = [ 'typeMap' => [ 'root' => 'array', 'document' => 'array' ] ];
	}


	public function getUrl( $key ) {
		$shorlink = $this->configCollection->findOne( ['_id' => $key], $this->defaultOptions );

		if( !$shorlink ) {
			throw new ShortlinkNotFoundException();
		}

		return $shorlink['url'];
	}

}

class ShortlinkNotFoundException extends \Exception {}