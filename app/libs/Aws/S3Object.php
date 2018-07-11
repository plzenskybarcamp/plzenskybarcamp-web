<?php

declare(strict_types=1);


namespace App\Aws;

use Aws\ResultInterface;

/**
 * Class S3Object
 * @package App\Aws
 * @property string $ACL Possible values: 'private', 'public-read', 'public-read-write', 'authenticated-read', 'bucket-owner-read', 'bucket-owner-full-control'
 * @property \Psr\Http\Message\StreamInterface $Body
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
     * @param array|ResultInterface $data
     */
    public function __construct($data = [])
    {
        $this->data = $data;
    }


    /**
     * @param string $fileName
     * @param string|null $contentType
     * @return S3Object
     */
    public static function createFromFile(string $fileName, string $contentType = null): S3Object
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
     * @param string $fileName
     * @param string $default
     * @return string
     */
    private static function detectMimeType(string $fileName, string $default = 'application/octet-stream'): string
    {
        $mimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $fileName);
        return $mimeType !== false ? $mimeType : $default;
    }


    /**
     * @param string $body
     * @param string $contentType
     * @return S3Object
     */
    public static function createFromString(string $body, string $contentType): S3Object
    {
        return new self(array(
            'Body' => $body,
            'ContentType' => $contentType,
        ));
    }


    /**
     * @param string $name
     * @param $value
     */
    public function __set(string $name, $value)
    {
        $this->data[$name] = $value;
    }


    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }


    /**
     * @param string $name
     * @return mixed|null
     */
    public function __get(string $name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        return null;
    }


    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }


    /**
     * @param string $header
     * @param string $value
     * @return $this
     */
    public function addMetadata(string $header, string $value): self
    {
        $this->data['Metadata'][$header] = $value;
        return $this;
    }


    /**
     * @param string $header
     * @return string
     */
    public function getMetadata(string $header): string
    {
        return $this->data['Metadata'][$header];
    }


    /**
     * @param bool|int|string $cache
     * @return $this
     */
    public function setCacheControl($cache): self
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
