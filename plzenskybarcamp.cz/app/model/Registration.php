<?php

namespace App\Model;

class Registration {

	private $confereeCollection;
	private $talkCollection;

	const TOKEN_EXCEPTION_NOTFOUND = 1000;
	const TOKEN_EXCEPTION_INVALID = 1001;
	const TOKEN_EXCEPTION_EXPIRED = 1002;

	public function __construct( $mongoConfig ) {
		$client = new \MongoClient( $mongoConfig['host'] );
		$database = $client->$mongoConfig['db'];
		$this->confereeCollection = $database->conferee;
		$this->talkCollection = $database->talk;
		$this->tokenCollection = $database->token;
	}

	public function updateConferree( $userId, array $data ) {
		$this->updateConferreeByCondition( array( '_id' => $userId ), $data );
		$conferee = $this->findCoferree( $userId );
		if ( isset( $conferee['talk'] ) ) {
			$this->syncSpeakerWithTalk( $conferee['talk']['_id'], $conferee );
		}
	}

	public function findCoferree( $userId ) {
		$data = $this->findCoferrees( array( '_id' => $userId ) );
		return $data->getNext();
	}

	public function findCoferreeByPlatform( $platform, $userId ) {
		if(!preg_match('/^[a-z]+$/Di', $platform)) {
			throw new \Nette\InvalidArgumentException("Secure issuie: Invalid platform parameter.");
		}

		$path = "identity.platforms.$platform.id";
		$data = $this->findCoferrees( array( $path => $userId ) );
		return $data->getNext();
	}

	public function createTalk( $userId, array $data ) {
		$data['_id'] = hash("crc32b", uniqid("talk", TRUE));
		$data['created_date'] = new \MongoDate( time() );
		$speaker = $this->findCoferree( $userId );
		$this->talkCollection->insert( $data );
		$this->syncTalkWithSpeaker( $userId, $data );
		$this->syncSpeakerWithTalk( $data['_id'], $speaker );
	}

	public function updateTalk( $talkId, array $data, $updateModifier = '$set' ) {
		$this->updateTalkByCondition( array( '_id' => $talkId ), $data, $updateModifier );
		$talk = $this->findTalk( $talkId );
		$this->syncTalkWithSpeaker( $talk['speaker']['_id'], $talk );
	}

	public function findTalk( $talkId ) {
		return $this->talkCollection->find( array( '_id' => $talkId ) )->getNext();
	}

	public function getTalks( $sort = NULL ) {
		if(!is_array($sort)) {
			$sort = array('created_date' => 1 );
		}
		return $this->talkCollection->find()->sort( $sort );
	}

	public function addLinkToTalk( $talkId, $groupField, array $data ) {
		$linkId = hash("crc32b", uniqid("link", TRUE));
		$data['created_date'] = new \MongoDate( time() );

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
		return $this->findCoferrees( array( 'talk' => array( '$ne' => null ) ) )
			->sort( array('talk.created_date' => -1) )
			->limit( $limit );
	}

	public function getConferrees( $limit = 0 ) {
		return $this->findCoferrees()
			->sort( array('created_date' => -1) )
			->limit( $limit );
	}

	public function getVotesCount( $talkId ) {
		$talk = $this->findTalk( $talkId );
		return isset( $talk['votes_count'] ) ? $talk[ 'votes_count' ] : 0;
	}

	public function addVote( $talkId, $userId ) {
		$this->talkCollection->update(
			array( '_id' => $talkId ),
			array(
				'$push' => array( 'votes' => $userId ),
				'$inc' => array( 'votes_count' => 1 )
			)
		);
	}

	public function removeVote( $talkId, $userId ) {
		$this->talkCollection->update(
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
		$data['created_date'] = new \MongoDate( time() );
		$data['expired_date'] = ( isset($data['expired_date']) ? new \MongoDate($data['expired_date']) : NULL );
		$data['valid'] = true;
		$this->tokenCollection->insert( $data );
		return $token;
	}

	public function invalideVipToken( $token ) {
		$this->tokenCollection->update( array('_id'=>$token), array('$set'=>array('valid'=>false))  );
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
			return SELF::TOKEN_EXCEPTION_NOTFOUND;
		}
		if( ! isset( $token['valid'] ) || !$token['valid'] ) {
			$invalidateReasonTitle = "Token invalid";
			return SELF::TOKEN_EXCEPTION_INVALID;
		}
		if( isset( $token['expired_date'] ) && $token['expired_date']->sec < time() ) {
			$invalidateReasonTitle = "Token expired";
			return SELF::TOKEN_EXCEPTION_EXPIRED;
		}
		return FALSE; //No invalidity
	}

	public function getVipToken( $token ) {
		return $this->findVipTokens( array( "_id"=>$token ) )->getNext();
	}

	public function findVipTokens( $condition = array() ) {
		return $this->tokenCollection->find( $condition );
	}

	private function findCoferrees( $condition = array() ) {
		return $this->confereeCollection->find( $condition );
	}

	private function updateConferreeByCondition( $condition, array $data ) {
		return $this->confereeCollection->update( $condition,
			array( '$set' => $data ), array( 'upsert' => true ) );
	}

	private function updateTalkByCondition( $condition, array $data, $updateModifier = '$set' ) {
		return $this->talkCollection->update( $condition,
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