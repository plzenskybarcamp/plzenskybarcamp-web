<?php

namespace MongoDB\Model;

use \MongoDB\BSON\UTCDateTime,
	\DateTime;

class MongoDbSanitizer
{
	public static function sanitizeDocument( array $data ) {
		foreach( $data as $key => $value ) {
			if( is_array( $value ) ) {
				$data[$key] = self::sanitizeDocument( $value );
			}
			elseif( $value instanceof UTCDateTime ) {
				$data[$key] = self::sanitizeFields( $value );
			}
		}
		return $data;
	}

	public static function sanitizeFields( $item ) {
		return (new UTCDateTimeConverter( $item ) )->toDateTime();
	}
}
