<?php

namespace App\Model;

class Config {

	private $configCollection;
	private $configs;

	public function __construct( $host ) {
		$client = new \MongoClient( $host );
		$database = $client->barcamp;
		$this->configCollection = $database->config;
	}

	private function loadConfigs( $force = FALSE ) {
		if( $this->configs !== NULL || $force ) {
			return;
		}

		$result = $this->configCollection->find( array(  ) );

		$configs = array();

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

		$this->configCollection->update(
			array( '_id' => $id ),
			array(
				'_id' => $id,
				'value' => $value,
			),
			array( 'upsert' => TRUE )
		);
	}

}