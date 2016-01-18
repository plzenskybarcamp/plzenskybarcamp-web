<?php

namespace App\Model;

class Config {

	private $configCollection;
	private $configs;
	private $defaultOptions;


	public function __construct( $mongoConfig ) {
		$manager = new \MongoDB\Driver\Manager( $mongoConfig['uri'] );
		$dbName = $mongoConfig['database'];
		$this->configCollection = new \MongoDB\Collection($manager, "$dbName.config");
		$this->defaultOptions = [ 'typeMap' => [ 'root' => 'array', 'document' => 'array' ] ];
	}

	private function loadConfigs( $force = FALSE ) {
		if( $this->configs !== NULL || $force ) {
			return;
		}

		$result = $this->configCollection->find( [], $this->defaultOptions );

		$configs = [];

		foreach( $result as $document ) {
			$configs[ $document[ '_id' ] ] = $document[ 'value' ];
		}

		$this->configs = $configs;
	}

	public function getConfig( $id, $default = NULL ) {
		$this->loadConfigs();

		if( isset( $this->configs[ $id ] ) ) {
			return $this->configs[ $id ];
		}
		else {
			return $default;
		}
	}

	public function setConfig( $id, $value ) {
		$this->loadConfigs();

		$this->configs[ $id ] = $value;

		$this->configCollection->replaceOne(
			[ '_id' => $id ],
			[
				'_id' => $id,
				'value' => $value,
			]
		);
	}

}