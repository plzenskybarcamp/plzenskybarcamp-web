<?php

declare(strict_types=1);

namespace App;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


/**
 * Router factory.
 */
class RouterFactory
{

    /**
     * @return \Nette\Application\IRouter
     */
    public static function createRouter(): \Nette\Application\IRouter
    {
        return self::createHttpRouter();
    }


    /**
     * @return \Nette\Application\IRouter
     */
    public static function createHttpRouter(): \Nette\Application\IRouter
    {
        $router = new RouteList();

        // Special route to keep homepage at / (rewrites /2018/)
        $router[] = new Route('', [
            'presenter' => 'Archive',
            'action' => 'default',
            'path' => '',
            'year' => '2018',
        ]);

        $router[] = new Route('<year \d{4}>[/<path .+>]', 'Archive:default');

        $router[] = new Route('<path>', [
            'presenter' => 'Archive',
            'action' => 'default',
            'year' => '2018',
        ], Route::ONE_WAY);

        return $router;
    }

}
