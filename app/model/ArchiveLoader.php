<?php

namespace App\Model;

use App\Aws\S3Storage,
	Nette\Caching\Cache,
	Nette\Caching\IStorage;

class ArchiveLoader {

	private $s3;
	private $cache;


	public function __construct( S3Storage $s3, IStorage $cacheStorage ) {
		$this->s3 = $s3;
		$this->cache = new Cache($cacheStorage, 'archive-loader');
	}

	public function load( $path ) {
		$awsPath = '/archive' . $path;

		$content = $this->cache->load($awsPath, function(& $cacheParams) use ($awsPath) {
			$cacheParams = [Cache::EXPIRE => '1 month'];
			return $this->loadArchiveStorage( $awsPath );
		});
		return $content;
	}

	private function loadArchiveStorage( $path ) {
		if($this->s3->isObjectExist( $path )) {
			$object = $this->s3->getObject( $path );
			return [
				'status' => 200,
				'content' => $object->Body->getContents(),
			];
		}

		return [
			'status' => 404,
			'content' => NULL,
		];
	}
}