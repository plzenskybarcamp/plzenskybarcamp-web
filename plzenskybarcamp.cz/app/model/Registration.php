<?php

namespace App\Model;

class Registration {

	private $confereeCollection;
	private $talkCollection;

	public function __construct( $host ) {
		$client = new \MongoClient( $host );
		$database = $client->barcamp;
		$this->confereeCollection = $database->conferee;
		$this->talkCollection = $database->talk;
	}

	public function updateConferree( $userId, array $data ) {
		return $this->updateConferreeByCondition( array( 'user_id' => $userId ), $data );
	}

	public function createTalk( $userId, array $data ) {
		$data['speaker_id'] = $userId;
		$data['talk_id'] = uniqid(); // may use talk ID as url key
		$this->talkCollection->insert( $data );
		$this->syncTalkWithSpeaker( $data );
	}

	public function updateTalk( $talkId, array $data ) {
		$this->talkCollection->update( array( 'talk_id' => $talkId ),
			array( '$set' => $data ), array( 'upsert' => true ) );
		$talk = iterator_to_array( $this->findTalk( $talk ) );
		$this->syncTalkWithSpeaker( $talk );
	}

	public function findTalk( $talkId ) {
		return $this->talkCollection->find( array( 'talk_id' => $talkId ) );
	}

	public function getSpeakers() {
		return $this->findCoferrees( array( 'talk' => array( '$ne' => null ) ) );
	}

	public function getConferrees() {
		return $this->findCoferrees();
	}

	private function findCoferrees( $condition = array() ) {
		return $this->confereeCollection->find( $condition );
	}

	private function updateConferreeByCondition( $condition, array $data ) {
		return $this->confereeCollection->update( $condition,
			array( '$set' => $data ), array( 'upsert' => true ) );
	}

	private function syncTalkWithSpeaker( array $data ) {
		$speakerId = $data['speaker_id'];
		unset( $data['speaker_id'] );
		unset( $data['_id'] );
		$this->updateConferree( $speakerId, array( 'talk' => $data ) );
	}
}