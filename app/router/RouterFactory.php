<?php

namespace App;

use Nette\Application\Routers\CliRouter;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


/**
 * Router factory.
 */
class RouterFactory
{

    /**
     * @param $consoleMode
     * @return \Nette\Application\IRouter
     */
	public function createRouter( $consoleMode ): \Nette\Application\IRouter
    {
		if( $consoleMode ) {
			return $this->createCliRouter();
		}
		else {
			return $this->createHttpRouter();
		}

	}

	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createCliRouter(): \Nette\Application\IRouter
    {
		$router = new RouteList('Cli');
		$router[] = new CliRouter();
		return $router;
	}
	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createHttpRouter(): \Nette\Application\IRouter
    {
		$router = new RouteList();
		$router[] = new Route('2018', 'Homepage:default', Route::ONE_WAY);
		$router[] = new Route('2018/partneri', 'Homepage:partners', Route::ONE_WAY);
		$router[] = new Route('partneri', 'Homepage:partners');
		$router[] = new Route('2018/informace', 'Homepage:info', Route::ONE_WAY);
		$router[] = new Route('informace', 'Homepage:info');
		$router[] = new Route('2018/napsali-o-nas', 'Homepage:written', Route::ONE_WAY);
		$router[] = new Route('napsali-o-nas', 'Homepage:written');
		$router[] = new Route('2018/kontakt', 'Homepage:contact', Route::ONE_WAY);
		$router[] = new Route('kontakt', 'Homepage:contact');
		$router[] = new Route('profil', 'Conference:profil');
		$router[] = new Route('2018/ucastnici', 'Conference:visitors');
		$router[] = new Route('ucastnici', 'Conference:visitors', Route::ONE_WAY);
		$router[] = new Route('prednasky', 'Conference:talks', Route::ONE_WAY);
		$router[] = new Route('2018/prednasky/zebricek', 'Conference:talksRanking');
		$router[] = new Route('2018/prednasky', 'Conference:talks');
		$router[] = new Route('2018/prednasky/<talkId>', 'Conference:talksDetail');
		$router[] = new Route('prednasky/<talkId>', 'Conference:talksDetail', Route::ONE_WAY);
		$router[] = new Route('plzenakovo-slovnicek-pojmu', 'Homepage:vocabulary');
		$router[] = new Route('privacy-policy', 'Homepage:privacyPolicy');
		$router[] = new Route('terms', 'Homepage:terms');
		$router[] = new Route('2018/program', 'Program:list');
		$router[] = new Route('program', 'Program:list', Route::ONE_WAY);
		$router[] = new Route('login', 'Sign:in');
		$router[] = new Route('logout', 'Sign:out');
		$router[] = new Route('login/facebook', 'Sign:inFb');
		$router[] = new Route('login/twitter', 'Sign:inTw');
		$router[] = new Route('login/process/facebook', 'Sign:processFb');
		$router[] = new Route('login/process/twitter', 'Sign:processTw');
		$router[] = new Route('/vip/<token>', 'Vip:useToken');
		$router[] = new Route('<year 201[4-7]>[/<path .+>]', 'Archive:view');

		$router[] = new Route('no-track', 'Homepage:noTrack');
		$router[] = new Route('s/<key [a-z0-9]+>[-<utm [a-z0-9]+>]', 'Shortlink:go');

		$apiRouter = new RouteList('Api');
		$apiRouter[] = new Route('api/log/<action>', 'Log:');
		$router[] = $apiRouter;

		$adminRouter = new RouteList('Admin');
		$adminRouter[] = new Route('admin/vip/token/new', 'Vip:new');
		$adminRouter[] = new Route('admin/vip/token/invalidate/<token>', 'Vip:invalidate');
		$adminRouter[] = new Route('admin/vip[/token/<token>]', 'Vip:list');
		$adminRouter[] = new Route('admin/users', 'Users:list');
		$adminRouter[] = new Route('admin/talks', 'Talks:list');
		$adminRouter[] = new Route('admin/<presenter>/<action>', 'Dashboard:default');
		$router[] = $adminRouter;

		$router[] = new Route('<presenter>/<action>', 'Homepage:default');
		return $router;
	}

}
