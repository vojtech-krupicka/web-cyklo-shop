<?php declare(strict_types=1);

namespace App\Core;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette;

final class RouterFactory
{
    use Nette\StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList;

        $adminRouter = new RouteList('AdminModule');
        $adminRouter->addRoute('admin/index.php', 'Default:default', Route::ONE_WAY);
        $adminRouter->addRoute('admin/<presenter>/<action>[/<id \d+(?:-[a-z-]+)?>]', array(
            'presenter' => array(
                Route::Value => 'Default',
                Route::FilterTable => array(
                    'uvod' => 'Default',
                    'novinky' => 'News',
                    'vlastni-stranky' => 'Cms',
                    'galerie' => 'Gallery',
                    'diskuze' => 'Guestbook',
                    'spravce-souboru' => 'File',
                    'prihlaseni' => 'Sign'
                ),
            ),
            'action' => array(
                Route::Value => 'default',
                Route::FilterTable => array(
                    'vychozi' => 'default',
                    'vytvorit' => 'add',
                    'upravit' => 'edit',
                    'vytvorit-polozku-menu' => 'itemAdd',
                    'upravit-polozku-menu' => 'itemEdit',
                    'upravit-vlastni-stranku' => 'pageEdit',
                    'komentare' => 'comments'
                ),
            ),
            'signal' => array(
                Route::FilterTable => array(
                    'odhlasit' => 'logout',
                ),
            ),
            'id' => NULL,
        ));

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

        $router->add($adminRouter);
        $router->add($frontRouter);

        return $router;
    }
}
