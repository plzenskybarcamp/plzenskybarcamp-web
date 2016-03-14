<?php

namespace App;

use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;


/**
 * Router factory.
 */
class RouterFactory
{

	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();
		$router[] = new Route('2014/partneri', 'Homepage:partners');
		$router[] = new Route('2014/kudy-kam', 'Homepage:location');
		$router[] = new Route('2014/informace', 'Homepage:info');
		$router[] = new Route('2014/napsali-o-nas', 'Homepage:written');
		$router[] = new Route('2014/kontakt', 'Homepage:contact');
		$router[] = new Route('profil', 'Conference:profil');
		$router[] = new Route('2014/ucastnici', 'Conference:visitors');
		$router[] = new Route('2014/prednasky', 'Conference:talks');
		$router[] = new Route('2014/prednasky/<talkId>', 'Conference:talksDetail');
		$router[] = new Route('2014/plzenakovo-slovnicek-pojmu', 'Homepage:vocabulary');
		$router[] = new Route('2014/program[/<action>]', 'Program:list');
		$router[] = new Route('login', 'Sign:in');
		$router[] = new Route('logout', 'Sign:out');
		$router[] = new Route('login/facebook', 'Sign:inFb');
		$router[] = new Route('login/twitter', 'Sign:inTw');
		$router[] = new Route('login/process/facebook', 'Sign:processFb');
		$router[] = new Route('login/process/twitter', 'Sign:processTw');
		$router[] = new Route('/vip/<token>', 'Vip:useToken');

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
