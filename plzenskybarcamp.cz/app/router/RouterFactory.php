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
		$router[] = new Route('partneri', 'Homepage:partners');
		$router[] = new Route('kontakt', 'Homepage:contact');
		$router[] = new Route('profil', 'Conference:profil');
		$router[] = new Route('ucastnici', 'Conference:visitors');
		$router[] = new Route('prednasky', 'Conference:talks');
		$router[] = new Route('prednasky/<talkId>', 'Conference:talksDetail');
		$router[] = new Route('plzenakovo-slovnicek-pojmu', 'Homepage:vocabulary');
		$router[] = new Route('login', 'Sign:in');
		$router[] = new Route('logout', 'Sign:out');
		$router[] = new Route('login/facebook', 'Sign:inFb');
		$router[] = new Route('login/twitter', 'Sign:inTw');
		$router[] = new Route('login/process/facebook', 'Sign:processFb');
		$router[] = new Route('login/process/twitter', 'Sign:processTw');
		$router[] = new Route('admin', 'Admin:default');
		$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}

}
