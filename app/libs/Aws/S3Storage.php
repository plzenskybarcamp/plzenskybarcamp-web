<?php

namespace App\Aws;

class S3Storage
{

    private $appConfig;
    private $aws;

    private $_s3;


    public function __construct($appConfig, Aws $aws)
    {
        $this->appConfig = $appConfig;
        $this->aws = $aws;
    }


    public function putObject($sourceObject, $path)
    {
        if ($sourceObject instanceof S3Object) {
            $object = $sourceObject->toArray();
        } else {
            $object = $sourceObject;
        }

        $key = $this->path2Key($path);
        $object = $object + array(
                'Bucket' => $this->appConfig['bucket'],
                'Key' => $key,
            );

        $result = $this->getS3()->putObject($object);

        return $this->path2Url($path);
    }


    public function copyObject($sourceObject, $sourcePath, $path)
    {
        if ($sourceObject instanceof S3Object) {
            $object = $sourceObject->toArray();
        } else {
            $object = $sourceObject;
        }

        $bucket = $this->appConfig['bucket'];
        $sourceKey = $this->path2Key($sourcePath);
        $key = $this->path2Key($path);
        $object = $object + array(
                'Bucket' => $bucket,
                'CopySource' => $bucket . '/' . $sourceKey,
                'Key' => $key,
            );

        $result = $this->getS3()->copyObject($object);

        return $this->path2Url($path);
    }


    public function getObject($path)
    {
        $key = $this->path2Key($path);
        $object = array(
            'Bucket' => $this->appConfig['bucket'],
            'Key' => $key,
        );
        $result = $this->getS3()->getObject($object);

        return new S3Object($result);
    }


    public function headObject($path)
    {
        $key = $this->path2Key($path);
        $object = array(
            'Bucket' => $this->appConfig['bucket'],
            'Key' => $key,
        );
        $result = $this->getS3()->headObject($object);

        return new S3Object($result);
    }


    public function getS3()
    {
        if (!$this->_s3) {
            $this->_s3 = $this->aws->getS3();
        }
        return $this->_s3;
    }


    public function getMimeType($fileName)
    {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $fileName);
    }


    public function listObjects($path)
    {
        $key = $this->path2key($path);

        $result = $this->getS3()->ListObjects(array(
            'Bucket' => $this->appConfig['bucket'],
            'Prefix' => rtrim($key, '/') . '/',
            'Delimiter' => '/',
        ));

        return new S3StorageListResult($result);
    }


    public function isObjectExist($path)
    {
        $key = $this->path2Key($path);
        return $this->getS3()->doesObjectExist($this->appConfig['bucket'], $key);
    }


    public function path2Url($path, $externalLink = false)
    {
        $protocol = $externalLink ? 'https:' : '';
        return $protocol . $this->appConfig['baseUrl'] . '/' . ltrim($path, '/');
    }


    public function url2Path($url)
    {
        if (!$this->isValidUrl($url)) {
            throw new \Nette\InvalidArgumentException("Object URL is not based on known S3 storage.");
        }

        $pattern = preg_quote($this->appConfig['baseUrl'], '/');
        return preg_replace("/^(?:https?:)?$pattern/", '', $url);
    }


    public function isValidUrl($url)
    {
        $pattern = preg_quote($this->appConfig['baseUrl'], '/');
        return preg_match("/^(?:https?:)?$pattern/", $url);
    }


    public function key2Path($key)
    {
        $pattern = preg_quote($this->appConfig['basePath'], '/');
        return preg_replace("/^$pattern/", '', $key);
    }


    public function path2Key($path)
    {
        return ($this->appConfig['basePath'] ? $this->appConfig['basePath'] . '/' : '') . ltrim($path, '/');
    }
}
