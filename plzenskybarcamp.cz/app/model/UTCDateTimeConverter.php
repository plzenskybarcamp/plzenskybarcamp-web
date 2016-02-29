<?php

namespace MongoDB\Model;

use \MongoDB\BSON\UTCDateTime,
	\DateTime;

class UTCDateTimeConverter
{
	private $dateTime;

	public function __construct( $date = NULL ) {
		if( is_null( $date ) ) {
			$this->dateTime = new DateTime();
		}
		elseif( $date instanceof DateTime ) {
			$this->dateTime = $date;
		}
		elseif( $date instanceof UTCDateTime ) {
			$this->dateTime = $date->toDateTime()
				->setTimeZone( new \DateTimeZone( date_default_timezone_get() ) );
		}
		elseif( is_numeric( $date ) ) {
			$this->dateTime = ( new DateTime( "@$date" ) )
				->setTimeZone( new \DateTimeZone( date_default_timezone_get() ) );
		}
		else {
			throw new \Exception( 'Unable to convert date' );
		}
	}

	public function toDateTime() {
		return $this->dateTime;
	}

	public function toTimestamp() {
		return $this->dateTime->getTimestamp();
	}

	public function toMongo() {
		$timestamp = $this->dateTime->getTimestamp();
		return new UTCDateTime( $timestamp * 1000 );
	}

	public function format( $format ) {
		return $this->dateTime->format( $format );
	}
}
