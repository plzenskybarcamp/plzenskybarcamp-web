<?php

declare(strict_types=1);

namespace App\Aws;

use Aws\Sdk;
use Nette\SmartObject;

/**
 * Class Aws
 * @package App\Aws
 */
class Aws
{
    use SmartObject;

    /**
     * @var
     */
    private $awsInstance;
    /**
     * @var array
     */
    private $appConfig;


    /**
     * Aws constructor.
     * @param array $appConfig
     */
    public function __construct(array $appConfig)
    {
        $this->appConfig = $appConfig;
    }


    /**
     * @return \Aws\S3\S3Client
     */
    public function getS3(): \Aws\S3\S3Client
    {
        return $this->getAwsInstance()->createS3();
    }


    /**
     * @return \Aws\Sns\SnsClient
     */
    public function getSns(): \Aws\Sns\SnsClient
    {
        return $this->getAwsInstance()->createSns();
    }


    /**
     * @return Sdk
     */
    public function getAwsInstance(): Sdk
    {
        if (!$this->awsInstance) {
            $this->awsInstance = new Sdk($this->appConfig);
        }
        return $this->awsInstance;
    }
}
