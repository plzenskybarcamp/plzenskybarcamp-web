<?php

namespace App\OAuth;

use Nette\Object,
	Nette\InvalidArgumentException;

class Identity extends Object {

	private $baseFields = array(
		//TRUE if is required
		'id' => TRUE,
		'name' => TRUE,
		'email' => FALSE,
		'picture_url' => TRUE,
	);

	private $baseData;
	private $platform;
	private $accessToken = array();
	private $platformData = array();

	public function __construct( array $baseData, $platform = NULL, $accessToken = NULL, array $platformData = array() ) {
		if( $platform ) {
			$this->initByArguments( $baseData, $platform, $accessToken, $platformData );
		}
		else {
			$this->initByArray( $baseData );
		}
	}

	private function initByArguments( array $baseData, $platform = NULL, $accessToken = NULL, array $platformData = array() ) {
		$this->setBaseData( $baseData );
		$this->setPlatform( $platform );
		$this->setAccessToken( $platform, $accessToken );
		$this->setPlatformData( $platform, $platformData );
	}

	private function initByArray( array $data ) {
		$platform = $this->getRequired( $data, "current_platform" );

		$this->setBaseData( $data );
		$this->setPlatform( $platform );
		$this->setAccessToken( $platform, $this->getRequired( $data, "access_tokens.$platform" ) );
		$this->setPlatformData( $platform, $this->getRequired( $data, "platforms.$platform" ) );
	}

	public function toArray() {
		$data = $this->getBaseData();
		$data['current_platform'] = $this->getPlatform();
		$data['access_tokens'] = $this->getAllAccessTokens();
		$data['platforms'] = $this->getAllPlatformData();
		return $data;
	}

	private function setBaseData( array $data ) {
		$tmp = array();
		foreach ($this->baseFields as $key => $required) {
			if( isset( $data[ $key ] ) && !empty( $data[ $key ] ) ) {
				$tmp[ $key ] = $data[ $key ];
			}
			elseif( $required ) {
				throw new InvalidArgumentException( "Required field \"$key\" is empty or not defined" );
			}
			else {
				$tmp[ $key ] = NULL;
			}
		}
		$this->baseData = $tmp;
	}

	private function setPlatform( $platform ) {
		$this->validateNoEmpty( $platform, 'platform' );
		$this->platform = $platform;
	}

	private function setAccessToken( $platform, $accessToken ) {
		$this->validateNoEmpty( $accessToken, 'accessToken' );
		$this->accessToken[ $platform ] = $accessToken;
	}

	private function setPlatformData( $platform, $platformData ) {
		$this->validateNoEmpty( $platformData, 'platformData' );
		$this->platformData[ $platform ] = $platformData;
	}

	private function validateNoEmpty( $data, $parameterName ) {
		if( empty( $data ) ) {
			throw new InvalidArgumentException( "Required parameter \"$parameterName\" is empty or not defined" );
		}
	}

	public function getBaseData() {
		return $this->baseData;
	}

	public function getId() {
		return $this->baseData[ 'id' ];
	}

	public function getPlartformId( $platform = NULL, $required = FALSE ) {
		return $this->getItem( $this->getPlatformData( $platform ), 'id', $required );
	}

	public function getName() {
		return $this->baseData[ 'name' ];
	}

	public function getEmail() {
		return $this->baseData[ 'email' ];
	}

	public function getPictureUrl() {
		return $this->baseData[ 'picture_url' ];
	}

	public function getPlatform() {
		return $this->platform;
	}

	public function getAccessToken( $platform = NULL ) {
		return $this->getItem( $this->accessToken, $this->_getPlatform( $platform ), !$platform );
	}

	public function getAllAccessTokens() {
		return $this->accessToken;
	}

	public function getPlatformData( $platform = NULL ) {
		return $this->getItem( $this->platformData, $this->_getPlatform( $platform ), !$platform );
	}

	public function getAllPlatformData() {
		return $this->platformData;
	}

	private function _getPlatform( $platform = NULL ) {
		if( $platform == NULL ) {
			return $this->getPlatform();
		}
		return $platform;
	}

	public function mergeIdentity( Identity $identity ) {
		$this->setAccessToken( $identity->getPlatform(), $identity->getAccessToken() );
		$this->setPlatformData( $identity->getPlatform(), $identity->getPlatformData() );
		return $this;
	}

	private function getRequired( $array, $path ) {
		return $this->getItem( $array, $path, TRUE );
	}

	private function getItem( $array, $path, $required = FALSE ) {
		$parts = explode( '.', $path, 2 );
		if( !isset( $array[ $parts[0] ] ) ){
			if( $required ) {
				throw new InvalidArgumentException( "Required field \"$parts[0]\" is empty or not defined" );
			}
			return NULL;
		}

		if( isset( $parts[1] ) ) {
			return $this->getItem( $array[ $parts[0] ], $parts[1], $required );
		}
		return $array[ $parts[0] ];
	}
}