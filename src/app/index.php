<?php
// print_r(apache_get_modules());
// echo "<pre>"; print_r($_SERVER); die;
$_SERVER["REQUEST_URI"] = str_replace("app/", "", $_SERVER["REQUEST_URI"]);
// $_GET["_url"] = "/";
// $test = str_replace("/app/", "", $_SERVER["REQUEST_URI"]);

use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Http\Response;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Url;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Config\ConfigFactory;
use Phalcon\Session\Manager;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Http\Response\Cookies;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\Stream as logStream;
use Phalcon\Events\Manager as EventsManager;

// $config = new Config([]);

// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('URL_PATH', "/app");
define('URL_ROOT', "http://localhost:8080");
define('APP_PATH', BASE_PATH . '/app');
// echo BASE_PATH;
// die;


require_once(APP_PATH . "/vendor/autoload.php");

$profiler = new Fabfuel\Prophiler\Profiler();
$toolbar = new Fabfuel\Prophiler\Toolbar($profiler);
$toolbar->addDataCollector(new Fabfuel\Prophiler\DataCollector\Request());
echo $toolbar->render();

// echo $_SERVER["REQUEST_URI"];
// die;
// Register an autoloader
$loader = new Loader();
$container = new FactoryDefault();
$eventsManager = new EventsManager();

$loader->registerDirs(
    [
        APP_PATH . "/controllers/",
        APP_PATH . "/models/",
    ]
);

$loader->registerNamespaces(
    [
        "App\Helper" => APP_PATH . "/helper/"
    ]
);

$loader->register();

/******************************Events Start******************************** */

//Event
$eventsManager->attach(
    'listener',
    new \App\Helper\listener()
);


/******************************Events End********************************** */
$container->set(
    'events',
    $eventsManager
);

$container->set(
    'objects',
    function () {
        $detail = array(
            'escaper' => new App\Helper\myescaper(),
            'logger' => new Logger(
                'messages',
                [
                    "main" => new logStream(APP_PATH . "/logs/main.log"),
                    "admin" => new logStream(APP_PATH . "/logs/admin.log"),
                ]
            ),
            'webhookHelper' => new \App\Helper\webhook(),
            //'curl' => new \App\Helper\curl('', []),
        );
        return (object)$detail;
    }
);

$container->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(APP_PATH . '/views/');
        return $view;
    }
);

$container->set(
    'url',
    function () {
        $url = new Url();
        $url->setBaseUri('/');
        return $url;
    }
);

$container->set(
    'session',
    function () {
        $session = new Manager();
        $files = new Stream(
            [
                'savePath' => '/tmp',
            ]
        );
        $session->setAdapter($files);
        $session->start();
        return $session;
    }
);

$container->set(
    'mongo',
    function () {
        $mongo =  new \MongoDB\Client('mongodb://mongo', array('username' => 'root', "password" => 'password123'));
        return $mongo->mongodb;
    }
);

$container->set(
    'webhookDB',
    function () {
        $mongo =  new \MongoDB\Client('mongodb://mongo', array('username' => 'root', "password" => 'password123'));
        return $mongo->webhook;
    }
);

$application = new Application($container);

// $application->setEventsManager($eventsManager);

try {
    // Handle the request
    $response = $application->handle(
        $_SERVER["REQUEST_URI"]
        // $test
    );

    $response->send();
} catch (\Exception $e) {
    $response = new Response();
    if (strpos($e->getMessage(), " handler class cannot be loaded")) {
        // echo "Controller Not Found";
        print_r($e->getMessage());
        // Getting a response instance
        // $response->redirect('error');
        // $response->send();
    } elseif (strpos($e->getMessage(), "was not found on handler")) {
        echo $e->getMessage()." Method Not Found";
    }
}
