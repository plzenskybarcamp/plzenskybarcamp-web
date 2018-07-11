<?php

namespace App\Aws;

class SnsPublisher
{

    private $appConfig;
    private $aws;

    private $_sns;


    public function __construct($appConfig, Aws $aws)
    {
        $this->appConfig = $appConfig;
        $this->aws = $aws;
    }


    public function publish($message)
    {
        $object = array(
            'TopicArn' => $this->appConfig['topicArn'],
            'Message' => json_encode($message),
        );

        $result = $this->getSNS()->publish($object);
    }


    public function getSNS()
    {
        if (!$this->_sns) {
            $this->_sns = $this->aws->sns;
        }
        return $this->_sns;
    }
}
