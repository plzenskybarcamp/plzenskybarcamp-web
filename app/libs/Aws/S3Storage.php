<?php

declare(strict_types=1);

namespace App\Aws;

/**
 * Class S3Storage
 * @package App\Aws
 */
class S3Storage
{

    /**
     * @var array
     */
    private $appConfig;
    /**
     * @var Aws
     */
    private $aws;
    /**
     * @var \Aws\S3\S3Client|null
     */
    private $_s3;


    /**
     * S3Storage constructor.
     * @param array $appConfig
     * @param Aws $aws
     */
    public function __construct(array $appConfig, Aws $aws)
    {
        $this->appConfig = $appConfig;
        $this->aws = $aws;
    }


    /**
     * @param S3Object|array $sourceObject
     * @param string $path
     * @return string
     */
    public function putObject($sourceObject, string $path): string
    {
        if ($sourceObject instanceof S3Object) {
            $object = $sourceObject->toArray();
        } else {
            $object = $sourceObject;
        }

        $key = $this->path2Key($path);
        $object += [
            'Bucket' => $this->appConfig['bucket'],
            'Key' => $key,
        ];

        $this->getS3()->putObject($object);

        return $this->path2Url($path);
    }


    /**
     * @param S3Object|array $sourceObject
     * @param string $sourcePath
     * @param string $path
     * @return string
     */
    public function copyObject($sourceObject, string $sourcePath, string $path): string
    {
        if ($sourceObject instanceof S3Object) {
            $object = $sourceObject->toArray();
        } else {
            $object = $sourceObject;
        }

        $bucket = $this->appConfig['bucket'];
        $sourceKey = $this->path2Key($sourcePath);
        $key = $this->path2Key($path);
        $object += [
            'Bucket' => $bucket,
            'CopySource' => $bucket . '/' . $sourceKey,
            'Key' => $key,
        ];

        $this->getS3()->copyObject($object);

        return $this->path2Url($path);
    }


    /**
     * @param string $path
     * @return S3Object
     */
    public function getObject(string $path): S3Object
    {
        $key = $this->path2Key($path);
        $object = [
            'Bucket' => $this->appConfig['bucket'],
            'Key' => $key,
        ];
        $result = $this->getS3()->getObject($object);

        return new S3Object($result);
    }


    /**
     * @param string $path
     * @return S3Object
     */
    public function headObject(string $path): S3Object
    {
        $key = $this->path2Key($path);
        $object = [
            'Bucket' => $this->appConfig['bucket'],
            'Key' => $key,
        ];
        $result = $this->getS3()->headObject($object);

        return new S3Object($result);
    }


    /**
     * @return \Aws\S3\S3Client
     */
    public function getS3(): \Aws\S3\S3Client
    {
        if (!$this->_s3) {
            $this->_s3 = $this->aws->getS3();
        }
        return $this->_s3;
    }


    /**
     * @param string $fileName
     * @param string $default
     * @return string
     */
    public function getMimeType(string $fileName, string $default = 'application/octet-stream'): string
    {
        $mimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $fileName);
        return $mimeType !== false ? $mimeType : $default;
    }


    /**
     * @param string $path
     * @return S3StorageListResult
     */
    public function listObjects(string $path): S3StorageListResult
    {
        $key = $this->path2Key($path);

        $result = $this->getS3()->listObjects([
            'Bucket' => $this->appConfig['bucket'],
            'Prefix' => rtrim($key, '/') . '/',
            'Delimiter' => '/',
        ]);

        return new S3StorageListResult($result);
    }


    /**
     * @param string $path
     * @return bool
     */
    public function isObjectExist(string $path): bool
    {
        $key = $this->path2Key($path);
        return $this->getS3()->doesObjectExist($this->appConfig['bucket'], $key);
    }


    /**
     * @param string $path
     * @param bool $externalLink
     * @return string
     */
    public function path2Url(string $path, bool $externalLink = false): string
    {
        $protocol = $externalLink ? 'https:' : '';
        return $protocol . $this->appConfig['baseUrl'] . '/' . ltrim($path, '/');
    }


    /**
     * @param string $url
     * @return string
     */
    public function url2Path(string $url): string
    {
        if (!$this->isValidUrl($url)) {
            throw new \Nette\InvalidArgumentException('Object URL is not based on known S3 storage.');
        }

        $pattern = preg_quote($this->appConfig['baseUrl'], '/');
        return preg_replace("/^(?:https?:)?$pattern/", '', $url);
    }


    /**
     * @param string $url
     * @return bool
     */
    public function isValidUrl(string $url): bool
    {
        $pattern = preg_quote($this->appConfig['baseUrl'], '/');
        return (bool)preg_match("/^(?:https?:)?$pattern/", $url);
    }


    /**
     * @param string $key
     * @return string
     */
    public function key2Path(string $key): string
    {
        $pattern = preg_quote($this->appConfig['basePath'], '/');
        return preg_replace("/^$pattern/", '', $key);
    }


    /**
     * @param string $path
     * @return string
     */
    public function path2Key(string $path): string
    {
        return ($this->appConfig['basePath'] ? $this->appConfig['basePath'] . '/' : '') . ltrim($path, '/');
    }
}
