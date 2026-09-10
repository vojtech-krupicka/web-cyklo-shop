<?php
/**
 * @filesource  bootstrap.php
 *
 * Main application boot file, which load Nette framework, dibi and register
 * all important services and variables (routes, ...).
 * 
 * @author      Ing. Vojtech Krupicka
 * @copyright   HassaSimple Framework
 * @version     1.0.0 
 */


/**
 * My Application bootstrap file.
 */
use Nette\Application\Routers;


// Load Nette Framework
require LIBS_DIR . '/Nette/loader.php';


// Configure application
$configurator = new Nette\Config\Configurator;

// Enable Nette Debugger for error visualisation & logging
//$configurator->setDebugMode($configurator::AUTO);
$configurator->enableDebugger(__DIR__ . '/../log');

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(APP_DIR)
	->addDirectory(LIBS_DIR)
	->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config/config.neon');
$container = $configurator->createContainer();


// Register own panels
\Nette\Diagnostics\Debugger::$bar->addPanel(new \Hassa\Diagnostics\Panels\SessionPanel($container->session));
\Hassa\Diagnostics\Panels\ConfiguratorPanel::register($container);


// Setup router
$router = $container->router;

// Detekce mod_rewrite
if (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) {
	$router[] = $adminRouter = new Routers\RouteList('Admin');
	$adminRouter[] = new Routers\Route('admin/index.php', 'Default:default', Routers\Route::ONE_WAY);
	$adminRouter[] = new Routers\Route('admin/<presenter>/<action>/[<id <?(\d+)(\-[a-z\-]+)?>/]', array(
								'presenter' => array(
									Routers\Route::VALUE => 'Default',
									Routers\Route::FILTER_TABLE => array(
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
									Routers\Route::VALUE => 'default',
									Routers\Route::FILTER_TABLE => array(
										'vychozi' => 'default',
										'vytvorit' => 'add',
										'upravit' => 'edit',
										'upravit-polozku-menu' => 'editItem',
										'upravit-vlastni-stranku' => 'editPage',
										'komentare' => 'comments'
									),
								),
								'id' => NULL,
						));
	
	$router[] = $frontRouter = new Routers\RouteList('Front');
	$frontRouter[] = new Routers\Route('index.php', 'Homepage:default', Routers\Route::ONE_WAY);	
	$frontRouter[] = new Routers\Route('<uri [a-z0-9-_/]+>.html', 'Cms:default');
	$frontRouter[] = $route = new Routers\Route('<presenter>/<action>/[<id>]', array(
								'presenter' => array(
									Routers\Route::VALUE => 'Homepage',
									Routers\Route::FILTER_TABLE => array(
										'uvod' => 'Homepage',
										'novinky' => 'News',
										'galerie' => 'Gallery',
										'diskuze' => 'Guestbook',
										'kontakt' => 'Contact',
										'mapa-stranek' => 'Sitemap'
									),
								),
								'action' => array(
									Routers\Route::VALUE => 'default',
									Routers\Route::FILTER_TABLE => array(
										'vychozi' => 'default'
									),
								),
								'id' => NULL,
							));
} 
// Vychozi routa pokud neni povolen mod_rewrite
else {
	$router[] = new Routers\SimpleRouter('Front:Homepage:default');
}


// Configure and run the application!
$app = $container->application; 
$app->onRequest[] = function($app, $request) {
			$presenter = $request->presenterName;
			$errorPresenter = 'Error';

			if(($pos = strrpos($presenter, ':')) !== false) {
				try {
					$errorPresenter = substr($presenter, 0, ($pos + 1)) . 'Error';
					$errorPresenterClass = $app->presenterFactory->createPresenter($errorPresenter);
				}
				catch (\Nette\Application\InvalidPresenterException $e) {
					$errorPresenter = 'Error';
				}
			}
			
			$app->errorPresenter = $errorPresenter;
		};

$app->run();
