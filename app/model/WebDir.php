<?php

declare(strict_types=1);

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
    public function __construct(string $wwwDir)
    {
        $this->wwwDir = $wwwDir;
    }


    /**
     * @param string $suffix
     * @return string
     */
    public function getPath(string $suffix = ''): string
    {
        return $this->wwwDir . ($suffix !== '' ? DIRECTORY_SEPARATOR . $suffix : '');
    }
}
