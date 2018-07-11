<?php

declare(strict_types=1);

namespace App\Model;

use App\Aws\S3Storage;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;

/**
 * Class ArchiveLoader
 * @package App\Model
 */
class ArchiveLoader
{
    /**
     * @var S3Storage
     */
    private $s3;
    /**
     * @var Cache
     */
    private $cache;


    /**
     * ArchiveLoader constructor.
     * @param S3Storage $s3
     * @param IStorage $cacheStorage
     */
    public function __construct(S3Storage $s3, IStorage $cacheStorage)
    {
        $this->s3 = $s3;
        $this->cache = new Cache($cacheStorage, 'archive-loader');
    }


    /**
     * @param string $path
     * @return mixed
     */
    public function load(string $path)
    {
        $awsPath = '/archive' . $path;

        $content = $this->cache->load($awsPath, function (& $cacheParams) use ($awsPath) {
            $cacheParams = [Cache::EXPIRE => '1 month'];
            return $this->loadArchiveStorage($awsPath);
        });
        return $content;
    }


    /**
     * @param string $path
     * @return array
     */
    private function loadArchiveStorage(string $path): array
    {
        if ($this->s3->isObjectExist($path)) {
            $object = $this->s3->getObject($path);

            return [
                'status' => 200,
                'content' => $object->Body->getContents(),
            ];
        }

        return [
            'status' => 404,
            'content' => null,
        ];
    }
}