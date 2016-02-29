<?php

namespace App\Model;

use MongoDB\Model\UTCDateTimeConverter;

class Registration {

	private $confereeCollection;
	private $talkCollection;
	private $tokenCollection;
	private $defaultOptions;

	const TOKEN_EXCEPTION_NOTFOUND = 1000;
	const TOKEN_EXCEPTION_INVALID = 1001;
	const TOKEN_EXCEPTION_EXPIRED = 1002;

	public function __construct( $mongoConfig ) {
		$manager = new \MongoDB\Driver\Manager( $mongoConfig['uri'] );
		$dbName = $mongoConfig['database'];
		$this->confereeCollection = new \MongoDB\Collection($manager, $dbName, 'conferee');
		$this->talkCollection = new \MongoDB\Collection($manager, $dbName, 'talk');
		$this->tokenCollection = new \MongoDB\Collection($manager, $dbName, 'token');
		$this->defaultOptions = [ 'typeMap' => [ 'root' => 'array', 'document' => 'array' ] ];

	}

	public function updateConferree( $userId, array $data ) {
		$this->updateConferreeByCondition( array( '_id' => $userId ), $data );
		$conferee = $this->findCoferree( $userId );
		if ( isset( $conferee['talk'] ) ) {
			$this->syncSpeakerWithTalk( $conferee['talk']['_id'], $conferee );
		}
	}

	public function findCoferree( $userId ) {
		return $this->confereeCollection->findOne( [ '_id' => $userId ], $this->defaultOptions );
	}

	public function findCoferreeByPlatform( $platform, $userId ) {
		if(!preg_match('/^[a-z]+$/Di', $platform)) {
			throw new \Nette\InvalidArgumentException("Secure issue: Invalid platform parameter.");
		}

		$path = "identity.platforms.$platform.id";
		return $this->confereeCollection->findOne( [ $path => $userId ], $this->defaultOptions );
	}

	public function createTalk( $userId, array $data ) {
		$data['_id'] = hash("crc32b", uniqid("talk", TRUE));
		$data['created_date'] = (new UTCDateTimeConverter())->toMongo();
		$speaker = $this->findCoferree( $userId );
		$this->talkCollection->insertOne( $data );
		$this->syncTalkWithSpeaker( $userId, $data );
		$this->syncSpeakerWithTalk( $data['_id'], $speaker );
	}

	public function updateTalk( $talkId, array $data, $updateModifier = '$set' ) {
		$this->updateTalkByCondition( array( '_id' => $talkId ), $data, $updateModifier );
		$talk = $this->findTalk( $talkId );
		$this->syncTalkWithSpeaker( $talk['speaker']['_id'], $talk );
	}

	public function findTalk( $talkId ) {
		return $this->talkCollection->findOne( [ '_id' => $talkId ], $this->defaultOptions );
	}

	public function getTalks( $sort = [] ) {
		if(!$sort) {
			$sort = [ 'created_date' => 1 ];
		}
		return $this->talkCollection->find( [], [ 'sort'=> $sort ] + $this->defaultOptions );
	}

	public function countTalks( ) {
		return $this->talkCollection->count(
			[],
			$this->defaultOptions
		);
	}

	public function addLinkToTalk( $talkId, $groupField, array $data ) {
		$linkId = hash("crc32b", uniqid("link", TRUE));
		$data['created_date'] = (new UTCDateTimeConverter())->toMongo();

		if( ! preg_match( '/^[_a-z0-9]+$/iD', $groupField )) {
			throw new \Nette\InvalidArgumentException("Secure issuie: Invalid mongo groupField parameter.");
		}


		$this->updateTalk( $talkId, array( "$groupField.$linkId" => $data ) );

		return $linkId;
	}

	public function editLinkToTalk( $talkId, $groupField, $linkId, array $data ) {
		if( ! preg_match( '/^[_a-z0-9]+$/iD', $groupField )) {
			throw new \Nette\InvalidArgumentException("Secure issuie: Invalid mongo groupField parameter.");
		}
		if( ! preg_match( '/^[a-z0-9]+$/iD', $linkId )) {
			throw new \Nette\InvalidArgumentException("Secure issuie: Invalid mongo linkId parameter.");
		}


		$this->updateTalk( $talkId, array( "$groupField.$linkId" => $data ) );

		return $linkId;
	}

	public function removeLinkFromTalk( $talkId, $groupField, $linkId ) {
		if( ! preg_match( '/^[_a-z0-9]+$/iD', $groupField )) {
			throw new \Nette\InvalidArgumentException("Secure issuie: Invalid mongo groupField parameter.");
		}
		if( ! preg_match( '/^[a-z0-9]+$/iD', $linkId )) {
			throw new \Nette\InvalidArgumentException("Secure issuie: Invalid mongo linkId parameter.");
		}

		$this->updateTalk( $talkId, array( "$groupField.$linkId" => "" ) , '$unset' );
	}

	public function getSpeakers( $limit = 0 ) {
		return $this->findCoferrees(
			[ 'talk' => [ '$ne' => NULL ] ],
			[
				'sort' => [ 'talk.created_date' => -1 ],
				'limit' => $limit,
			]
		);
	}

	public function getConferrees( $limit = 0 ) {
		return $this->getFilteredConferrees( [], $limit );
	}

	public function countConferrees( ) {
		return $this->confereeCollection->count(
			[],
			$this->defaultOptions
		);
	}

	public function getFilteredConferrees( $condition, $limit = 0 ) {
		return $this->findCoferrees(
			$condition,
			[
				'sort' => [ 'created_date' => -1 ],
				'limit' => $limit,
			]
		);
	}

	public function getVotesCount( $talkId ) {
		$talk = $this->findTalk( $talkId );
		return isset( $talk['votes_count'] ) ? $talk[ 'votes_count' ] : 0;
	}

	public function addVote( $talkId, $userId ) {
		$this->talkCollection->updateOne(
			array( '_id' => $talkId ),
			array(
				'$push' => array( 'votes' => $userId ),
				'$inc' => array( 'votes_count' => 1 )
			)
		);
	}

	public function removeVote( $talkId, $userId ) {
		$this->talkCollection->updateOne(
			array( '_id' => $talkId ),
			array(
				'$pull' => array( 'votes' => $userId ),
				'$inc' => array( 'votes_count' => -1 )
			)
		);
	}

	public function hasTalk( $talkId ) {
		return $this->talkCollection->find( array( '_id' => $talkId ) )->hasNext();
	}

	public function createVipToken( array $data ) {
		$token = hash("sha1", uniqid("vip", TRUE));
		$data['_id'] = $token;
		$data['created_date'] = (new UTCDateTimeConverter())->toMongo();
		$data['expired_date'] = (isset($data['expired_date']) ? (new UTCDateTimeConverter( $data['expired_date'] ))->toMongo() : NULL );
		$data['valid'] = true;
		$this->tokenCollection->insertOne( $data );
		return $token;
	}

	public function invalideVipToken( $token ) {
		$this->tokenCollection->updateOne( array('_id'=>$token), array('$set'=>array('valid'=>false))  );
	}

	public function validateVipToken( $tokenId ) {
		$token = $this->getVipToken( $tokenId );

		$invalidateReasonTitle = NULL;
		$invalidity = $this->getVipTokenInvalidity( $token, $invalidateReasonTitle );
		if( $invalidity ) {
			throw new InvalidTokenException( $invalidateReasonTitle, $invalidity );
		}
	}

	public function getVipTokenInvalidity( $token, &$invalidateReasonTitle = NULL ) {
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

	public function getVipToken( $token ) {
		return $this->tokenCollection->findOne( [ "_id"=>$token ] );
	}

	public function findVipTokens( $condition = array(), $options = array() ) {
		return $this->tokenCollection->find( $condition, $options );
	}

	private function findCoferrees( $filter = [], $options = [] ) {
		return $this->confereeCollection->find(
			$filter,
			$options + $this->defaultOptions
		);
	}

	private function updateConferreeByCondition( $condition, array $data ) {
		return $this->confereeCollection->updateOne( $condition,
			array( '$set' => $data ), array( 'upsert' => true ) );
	}

	private function updateTalkByCondition( $condition, array $data, $updateModifier = '$set' ) {
		return $this->talkCollection->updateOne( $condition,
			array( $updateModifier => $data ), array( 'upsert' => true ) );
	}

	private function syncTalkWithSpeaker( $speakerId, array $data ) {
		unset( $data['speaker'] );
		unset( $data['votes'] );
		unset( $data['votes_count'] );
		$this->updateConferreeByCondition( array( '_id' => $speakerId ), array( 'talk' => $data ) );
	}

	private function syncSpeakerWithTalk( $talkId, array $data ) {
		unset( $data['talk'] );
		unset( $data['identity']);
		$this->updateTalkByCondition( array( '_id' => $talkId ), array( 'speaker' => $data ) );
	}
}