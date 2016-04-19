<?php

namespace App;

use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\CliRouter;


/**
 * Router factory.
 */
class RouterFactory
{

	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter( $consoleMode )
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
	public function createCliRouter()
	{
		$router = new RouteList('Cli');
		$router[] = new CliRouter();
		return $router;
	}
	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createHttpRouter()
	{
		$router = new RouteList();
		$router[] = new Route('2016/partneri', 'Homepage:partners');
		$router[] = new Route('partneri', 'Homepage:partners', Route::ONE_WAY);
		$router[] = new Route('2016/informace', 'Homepage:info');
		$router[] = new Route('informace', 'Homepage:info', Route::ONE_WAY);
		$router[] = new Route('2016/napsali-o-nas', 'Homepage:written');
		$router[] = new Route('napsali-o-nas', 'Homepage:written', Route::ONE_WAY);
		$router[] = new Route('2016/promo-team', 'Homepage:promoTeam');
		$router[] = new Route('promo-team', 'Homepage:promoTeam', Route::ONE_WAY);
		$router[] = new Route('2016/kontakt', 'Homepage:contact');
		$router[] = new Route('kontakt', 'Homepage:contact', Route::ONE_WAY);
		$router[] = new Route('profil', 'Conference:profil');
		$router[] = new Route('2016/ucastnici', 'Conference:visitors');
		$router[] = new Route('ucastnici', 'Conference:visitors', Route::ONE_WAY);
		$router[] = new Route('prednasky', 'Conference:talks', Route::ONE_WAY);
		$router[] = new Route('prednasky/zebricek', 'Conference:talksRanking');
		$router[] = new Route('2016/prednasky', 'Conference:talks');
		$router[] = new Route('2016/prednasky/<talkId>', 'Conference:talksDetail');
		$router[] = new Route('prednasky/<talkId>', 'Conference:talksDetail', Route::ONE_WAY);
		$router[] = new Route('plzenakovo-slovnicek-pojmu', 'Homepage:vocabulary');
		$router[] = new Route('privacy-policy', 'Homepage:privacyPolicy');
		$router[] = new Route('terms', 'Homepage:terms');
		$router[] = new Route('2016/program', 'Program:list');
		$router[] = new Route('program', 'Program:list', Route::ONE_WAY);
		$router[] = new Route('login', 'Sign:in');
		$router[] = new Route('logout', 'Sign:out');
		$router[] = new Route('login/facebook', 'Sign:inFb');
		$router[] = new Route('login/twitter', 'Sign:inTw');
		$router[] = new Route('login/process/facebook', 'Sign:processFb');
		$router[] = new Route('login/process/twitter', 'Sign:processTw');
		$router[] = new Route('/vip/<token>', 'Vip:useToken');
		$router[] = new Route('2014[/<path .+>]', 'Archive:2014');
		$router[] = new Route('2015[/<path .+>]', 'Archive:2015');

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
