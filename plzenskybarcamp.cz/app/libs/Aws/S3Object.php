<?php

namespace App\Aws;

class S3Object {

	private $data;
	/*
	'ACL' => 'private|public-read|public-read-write|authenticated-read|bucket-owner-read|bucket-owner-full-control',
    'Body' => <Psr\Http\Message\StreamableInterface>,
    'Bucket' => '<string>', // REQUIRED
    'CacheControl' => '<string>',
    'ContentDisposition' => '<string>',
    'ContentEncoding' => '<string>',
    'ContentLanguage' => '<string>',
    'ContentLength' => <integer>,
    'ContentSHA256' => '<string>',
    'ContentType' => '<string>',
    'Expires' => <integer || string || DateTime>,
    'GrantFullControl' => '<string>',
    'GrantRead' => '<string>',
    'GrantReadACP' => '<string>',
    'GrantWriteACP' => '<string>',
    'Key' => '<string>', // REQUIRED
    'Metadata' => ['<string>', ...],
    'RequestPayer' => 'requester',
    'SSECustomerAlgorithm' => '<string>',
    'SSECustomerKey' => '<string>',
    'SSECustomerKeyMD5' => '<string>',
    'SSEKMSKeyId' => '<string>',
    'ServerSideEncryption' => 'AES256|aws:kms',
    'SourceFile' => '<string>',
    'StorageClass' => 'STANDARD|REDUCED_REDUNDANCY|LT',
    'WebsiteRedirectLocation' => '<string>',
    */

	public function __construct( $data = array() ) {
		$this->data = $data;
	}

	public static function createFromFile( $fileName, $contentType = NULL ) {
		if( $contentType === NULL ) {
			$contentType = GuzzleHttp\Psr7\mimetype_from_filename( $fileName );
		}
		return new self(array(
			'SourceFile' => $fileName,
			'ContentType' => $contentType,
		));
	}

	public static function createFromString( $body, $contentType ) {
		return new self(array(
			'Body' => $body,
			'ContentType' => $contentType,
		));
	}

	public function __set( $name, $value ) {
		$this[ $name ] = $value;
		return $this;
	}

	public function __get( $name ) {
		if( isset( $this->data[ $name ] ) ) {
			return $this->data[ $name ];
		}
		return NULL;
	}

	public function toArray( ) {
		return $this->data;
	}

	public function addMetadata( $header, $value ) {
		$this->data['Metadata'][ $header ] = $value;
		return $this;
	}

	public function getMetadata( $header ) {
		return $this->data['Metadata'][ $header ];
	}

	public function setCacheControl( $cache ) {
		if( !$cache ) {
			$this->data[ 'CacheControl' ] = "private, max-age=0, no-cache";
		}
		elseif( is_numeric( $cache ) ) {
			$this->data[ 'CacheControl' ] = "public, max-age=$cache";
		}
		elseif( is_string( $cache ) && preg_match('/^\s*\+/', $cache ) ) {
			$this->data[ 'CacheControl' ] = "public, max-age=" . strtotime( $cache, 0 );
		}
		else {
			$this->data[ 'CacheControl' ] = $cache;
		}
		return $this;
	}
}
