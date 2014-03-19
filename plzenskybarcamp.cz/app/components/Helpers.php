<?php

namespace App\Components;

class Helpers {

	public static function twitterize( $url, $prefix ) {
		return preg_replace('~^(((https?:)?//)?(.*\.?twitter.com/|@?))~i', $prefix, $url );
	}

	public static function biggerTwitterPicture( $url, $typeName = '') {
		return preg_replace('~_normal\\.([a-z]+)$~i', "$typeName.$1", $url );
	}

}