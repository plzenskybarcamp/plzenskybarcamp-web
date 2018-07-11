<?php

namespace App\Model;

/**
 * Class WebDir
 * @package App\Model
 */
class WebDir
{
    /**
     * @var
     */
    private $wwwDir;


    /**
     * WebDir constructor.
     * @param string $wwwDir
     */
    public function __construct($wwwDir)
    {
        $this->wwwDir = $wwwDir;
    }


    /**
     * @param string|null $suffix
     * @return string
     */
    public function getPath($suffix = null)
    {
        return $this->wwwDir . ($suffix !== null ? DIRECTORY_SEPARATOR . $suffix : '');
    }
}
