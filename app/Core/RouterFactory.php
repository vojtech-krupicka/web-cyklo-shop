<?php declare(strict_types=1);

namespace App\Core;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;

        $frontRouter = new RouteList('FrontModule');
        $frontRouter->addRoute('index.php', 'Homepage:default', Route::ONE_WAY);
        $frontRouter->addRoute('<uri [a-z0-9_/-]+>.html', 'Cms:default');
        $frontRouter->addRoute('<presenter>/<action>[/<id>]', array(
								'presenter' => array(
									Route::Value => 'Homepage',
									Route::FilterTable => array(
										'uvod' => 'Homepage',
										'novinky' => 'News',
										'galerie' => 'Gallery',
										'diskuze' => 'Guestbook',
										'kontakt' => 'Contact',
										'mapa-stranek' => 'Sitemap'
									),
								),
								'action' => array(
									Route::Value => 'default',
									Route::FilterTable => array(
										'vychozi' => 'default'
									),
								),
								'id' => NULL,
							));

        $router->add($frontRouter);

		// $router->addRoute('<presenter>/<action>[/<id>]', 'Home:default');
		return $router;
	}
}
