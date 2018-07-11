<?php

declare(strict_types=1);

namespace App\Aws;

use Aws\Sns\SnsClient;

/**
 * Class SnsPublisher
 * @package App\Aws
 */
class SnsPublisher
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
     * @var
     */
    private $_sns;


    /**
     * SnsPublisher constructor.
     * @param array $appConfig
     * @param Aws $aws
     */
    public function __construct(array $appConfig, Aws $aws)
    {
        $this->appConfig = $appConfig;
        $this->aws = $aws;
    }


    /**
     * @param mixed $message
     */
    public function publish($message): void
    {
        $object = array(
            'TopicArn' => $this->appConfig['topicArn'],
            'Message' => json_encode($message),
        );

        $this->getSNS()->publish($object);
    }


    /**
     * @return SnsClient
     */
    public function getSNS(): SnsClient
    {
        if (!$this->_sns) {
            $this->_sns = $this->aws->getSns();
        }
        return $this->_sns;
    }
}
