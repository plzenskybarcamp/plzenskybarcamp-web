<?php

namespace App\Aws;

/**
 * Class S3Object
 * @package App\Aws
 * @property string $ACL Possible values: 'private', 'public-read', 'public-read-write', 'authenticated-read', 'bucket-owner-read', 'bucket-owner-full-control'
 * @property \Psr\Http\Message\StreamableInterface $Body
 * @property string $Bucket REQUIRED
 * @property string $CacheControl
 * @property string $ContentDisposition
 * @property string $ContentEncoding
 * @property string $ContentLanguage
 * @property int $ContentLength
 * @property string $ContentSHA256
 * @property string $ContentType
 * @property integer|string|\DateTime $Expires
 * @property string $GrantFullControl
 * @property string $GrantRead
 * @property string $GrantReadACP
 * @property string $GrantWriteACP
 * @property string $Key REQUIRED
 * @property array<string> $Metadata
 * @property $RequestPayer
 * @property string $SSECustomerAlgorithm
 * @property string $SSECustomerKey
 * @property string $SSECustomerKeyMD5
 * @property string $SSEKMSKeyId
 * @property string $ServerSideEncryption Possible values: 'AES256, 'aws:kms'
 * @property string $SourceFile
 * @property string $StorageClass 'STANDARD', 'REDUCED_REDUNDANCY', 'LT'
 * @property string $WebsiteRedirectLocation
 */
class S3Object
{
    /**
     * @var array
     */
    private $data;


    /**
     * S3Object constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }


    /**
     * @param $fileName
     * @param null $contentType
     * @return S3Object
     */
    public static function createFromFile($fileName, $contentType = null): S3Object
    {
        if ($contentType === null) {
            $contentType = self::detectMimeType($fileName);
        }
        return new self(array(
            'SourceFile' => $fileName,
            'ContentType' => $contentType,
        ));
    }


    /**
     * @param $body
     * @param $contentType
     * @return S3Object
     */
    public static function createFromString($body, $contentType): S3Object
    {
        return new self(array(
            'Body' => $body,
            'ContentType' => $contentType,
        ));
    }


    /**
     * @param $fileName
     * @return string|bool
     */
    private static function detectMimeType($fileName)
    {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $fileName);
    }


    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value): void
    {
        $this->data[$name] = $value;
    }


    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        return null;
    }


    /**
     * @param $name
     * @return bool
     */
    public function __isset($name): bool
    {
        return isset($this->data[$name]);
    }


    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }


    /**
     * @param $header
     * @param $value
     * @return S3Object
     */
    public function addMetadata($header, $value): S3Object
    {
        $this->data['Metadata'][$header] = $value;
        return $this;
    }


    /**
     * @param $header
     * @return string
     */
    public function getMetadata($header): string
    {
        return $this->data['Metadata'][$header];
    }


    /**
     * @param $cache
     * @return S3Object
     */
    public function setCacheControl($cache): S3Object
    {
        if (!$cache) {
            $this->data['CacheControl'] = 'private, max-age=0, no-cache';
        } elseif (is_numeric($cache)) {
            $this->data['CacheControl'] = "public, max-age=$cache";
        } elseif (\is_string($cache) && preg_match('/^\s*\+/', $cache)) {
            $this->data['CacheControl'] = 'public, max-age=' . strtotime($cache, 0);
        } else {
            $this->data['CacheControl'] = $cache;
        }
        return $this;
    }
}
