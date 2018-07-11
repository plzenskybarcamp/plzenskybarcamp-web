<?php

declare(strict_types=1);

namespace App\Aws;

use Aws\Result as AwsResult;

/**
 * Class S3StorageListResult
 * @package App\Aws
 */
class S3StorageListResult
{
    /**
     * @var AwsResult
     */
    private $result;


    /**
     * S3StorageListResult constructor.
     * @param AwsResult $s3ListResult
     */
    public function __construct(AwsResult $s3ListResult)
    {
        $this->result = $s3ListResult;
    }


    /**
     * @return array
     */
    public function getObjects(): array
    {
        if (!isset($this->result['Contents'])) {
            return [];
        }
        return $this->result['Contents'];
    }


    /**
     * @return array
     */
    public function getPrefixes(): array
    {
        $prefixes = [];
        if (isset($this->result['CommonPrefixes'])) {
            foreach ($this->result['CommonPrefixes'] as $prefixItem) {
                $prefixes[] = [
                    'Key' => $prefixItem['Prefix'],
                ];
            }
        }
        return $prefixes;
    }
}