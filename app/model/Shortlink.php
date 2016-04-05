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


	public function getUrl( $key, $utm = NULL ) {
		$shorlink = $this->configCollection->findOne( ['_id' => $key], $this->defaultOptions );

		if( !$shorlink ) {
			throw new ShortlinkNotFoundException();
		}

		$url = $shorlink['url'];

		if( $utm && isset( $shorlink['utm'][ $utm ] ) ) {
			$url .= ( strpos( $url, '?' ) !== FALSE ? '&' : '?' ) . $shorlink['utm'][ $utm ];
		}

		return $url;
	}

}

class ShortlinkNotFoundException extends \Exception {}