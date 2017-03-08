<?php

namespace App\Model;

use MongoDB\Model\UTCDateTimeConverter;

class BearerToken {

	private $tokenCollection;
	private $defaultOptions;

	const TOKEN_EXCEPTION_NOTFOUND = 1000;
	const TOKEN_EXCEPTION_INVALID = 1001;
	const TOKEN_EXCEPTION_EXPIRED = 1002;

	public function __construct( $mongoConfig ) {
		$manager = new \MongoDB\Driver\Manager( $mongoConfig['uri'] );
		$dbName = $mongoConfig['database'];
		$this->tokenCollection = new \MongoDB\Collection($manager, $dbName, 'auth_token');
		$this->defaultOptions = [ 'typeMap' => [ 'root' => 'array', 'document' => 'array' ] ];

	}

	public function createToken( array $data ) {
		$token = hash("sha1", uniqid("bearer", TRUE));
		$data['_id'] = $token;
		$data['created_date'] = (new UTCDateTimeConverter())->toMongo();
		$data['expired_date'] = (isset($data['expired_date']) ? (new UTCDateTimeConverter( $data['expired_date'] ))->toMongo() : NULL );
		$data['valid'] = true;
		$this->tokenCollection->insertOne( $data );
		return $token;
	}

	public function invalideToken( $token ) {
		$this->tokenCollection->updateOne( array('_id'=>$token), array('$set'=>array('valid'=>false))  );
	}

	public function validateToken( $tokenId ) {
		$token = $this->getToken( $tokenId );

		$invalidateReasonTitle = NULL;
		$invalidity = $this->getTokenInvalidity( $token, $invalidateReasonTitle );
		if( $invalidity ) {
			throw new InvalidTokenException( $invalidateReasonTitle, $invalidity );
		}
	}

	public function getTokenInvalidity( $token, &$invalidateReasonTitle = NULL ) {
		if( ! $token ) {
			$invalidateReasonTitle = "Token not found";
			return self::TOKEN_EXCEPTION_NOTFOUND;
		}
		if( ! isset( $token['valid'] ) || !$token['valid'] ) {
			$invalidateReasonTitle = "Token invalid";
			return self::TOKEN_EXCEPTION_INVALID;
		}
		if( isset( $token['expired_date'] ) && (new UTCDateTimeConverter($token['expired_date']))->toTimestamp() < time() ) {
			$invalidateReasonTitle = "Token expired";
			return self::TOKEN_EXCEPTION_EXPIRED;
		}
		return FALSE; //No invalidity
	}

	public function getToken( $token ) {
		return $this->tokenCollection->findOne( [ "_id"=>$token ] );
	}

	public function findTokens( $condition = array(), $options = array() ) {
		return $this->tokenCollection->find( $condition, $options );
	}
}