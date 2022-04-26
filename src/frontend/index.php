<?php
// print_r(apache_get_modules());
// echo "<pre>"; print_r($_SERVER); die;
$_SERVER["REQUEST_URI"] = str_replace("frontend/", "", $_SERVER["REQUEST_URI"]);
// $_GET["_url"] = "/";

use helper\listener;
use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Url;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Config;
use Phalcon\Session\Manager;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\Stream as logStream;
use Phalcon\Events\Manager as EventsManager;

// $config = new Config([]);

// Define some absolute path constants to aid in locating resources
define('space', 'frontend');
define('Space', 'Frontend');
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/'.space);
define('URL_PATH', "/".space);
define('URL_ROOT', "http://192.168.2.64:8080");
define('API_ROOT', "http://192.168.2.64:8080/api");
// echo APP_PATH;
// die;

require_once(APP_PATH . "/vendor/autoload.php");


/*************************************Loader Start********************************** */
// Register an autoloader
$loader = new Loader();

$loader->registerDirs(
    [
        APP_PATH . "/controllers/",
        // APP_PATH . "/views/",
    ]
);

$loader->registerNamespaces(
    [
        Space."\Helper" => APP_PATH . "/helper/",
        Space."\Models" => APP_PATH . "/models"
    ]
);

$loader->register();
/*************************************Loader End********************************** */


/******************************Events Start******************************** */
// $eventsManager = new EventsManager();

//Default Before Handle Request
// $eventsManager->attach(
//     'application:beforeHandleRequest',
//     new \helper\listener()
// );

//Product Event
// $eventsManager->attach(
//     'listener:addProduct',
//     new \helper\listener()
// );

//Order Event
// $eventsManager->attach(
//     'listener:addOrder',
//     new \helper\listener()
// );

/******************************Events End********************************** */


/**********************************Container Start********************************** */
$container = new FactoryDefault();

$container->set(
    'access_token',
    function () {
        return 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NTA5NjY1NTMsImV4cCI6MTY1MDk2ODM1Mywic3ViIjoiNjI1ZWIwM2UwMzI4OTkyYTUzMDFiOGUyIn0.JjpFL89w-oItqDHfG7mxDQX_zpBnvRJp7iYBeAZbVFU';
    }
);

// $container->set(
//     'EventManager',
//     $eventsManager
// );

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
    'objects',
    function () {
        $detail = array(
            'escaper' => new Frontend\Helper\myescaper(),
            'logger' => new Logger(
                'messages',
                [
                    "main" => new logStream(APP_PATH . "/logs/main.log"),
                    "admin" => new logStream(APP_PATH . "/logs/admin.log"),
                ]
            ),
        );
        return (object)$detail;
    }
);

$container->set(
    'mongo',
    function () {
        $mongo =  new \MongoDB\Client('mongodb://mongo', array('username' => 'root', "password" => 'password123'));
        return $mongo->frontend;
    }
);


/**********************************Container End********************************** */

$application = new Application($container);

// $application->setEventsManager($eventsManager);

// echo $_SERVER['REQUEST_URI'];
// die;
try {
    // Handle the request
    $response = $application->handle(
        $_SERVER["REQUEST_URI"]
    );

    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}
