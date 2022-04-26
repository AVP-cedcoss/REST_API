<?php

error_reporting(E_ALL);

use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Router;
use Api\Helper;
use Handler\Register;
use Phalcon\Events\Manager as EventsManager;

// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('URL_PATH', '/api');
define('APP_PATH', BASE_PATH . '/api');
// echo BASE_PATH;
// die;

define('URL_ROOT', 'http://localhost:8080');

require_once(APP_PATH . "/vendor/autoload.php");

$container = new FactoryDefault();
$app = new Micro($container);
$eventsManager = new EventsManager();


$loader = new Loader();

$loader->registerNamespaces(
    [
        'Handler' => APP_PATH . '/handler',
        'Api\Helper' => APP_PATH . '/helper',
    ]
);

$loader->register();

/******************************Events Start******************************** */

//Event
$eventsManager->attach(
    'listener',
    new Api\Helper\listener()
);

$container->set(
    'events',
    $eventsManager
);

/******************************Events End********************************** */

$register = new Handler\Register();
$product = new Handler\Product();
$order = new Handler\Order();


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
    'user_id',
    function () use ($app, $register) {
        /**
         * Resolving Token and Checking Whether User Exists
         */
        try {
            return strval($app->mongo->user->findOne(
                [
                    '_id' => new MongoDB\BSON\ObjectId(($register)->resolveToken())
                ]
            )->_id);
        } catch (\Exception $e) {
            return json_encode(
                array(
                    'Message' => 'User Not Registered. Kindly Register or Generate A NEW TOKEN'
                )
            );
        }
    }
);

$container->set(
    'objects',
    function () {
        $obj = array(
            'escaper' => new Helper\myescaper(),
            
        );
        return (object)$obj;
    }
);

/**--------------------------------------------------GET REQUEST START----------------------------------------------- */

/* -----------------------REGISTER Start------------------*/
// $app->get(
//     '/register',
//     [
//         $register,
//         'register'
//     ]
// );

// $app->post(
//     '/register/signup',
//     [
//         $register,
//         'signup'
//     ]
// );

// $app->get(
//     '/register/login',
//     [
//         $register,
//         'login'
//     ]
// );

// $app->post(
//     '/register/signin',
//     [
//         $register,
//         'signin'
//     ]
// );

// $app->get(
//     '/register/getToken',
//     [
//         $register,
//         'getToken'
//     ]
// );

// $app->get(
//     '/register/generateToken',
//     [
//         $register,
//         'generateToken'
//     ]
// );

// $app->get(
//     '/register/expired',
//     [
//         $register,
//         'expired'
//     ]
// );

/* -----------------------REGISTER End------------------*/

/* -----------------------Product Start------------------*/

$app->get(
    URL_PATH,
    [
        $product,
        'help'
    ]
);

$app->get(
    URL_PATH.'/product/getSample',
    [
        $product,
        'getSample'
    ]
);

$app->get(
    URL_PATH.'/product/getSampleProductDetail',
    [
        $product,
        'getSampleProductDetail'
    ]
);

$app->before(
    function () use ($app) {
        // $app->user_id;
        if (str_contains($_SERVER['REQUEST_URI'], 'products')) {
            if (null === ($app->request->getQuery("access_token"))) {
                die(json_encode(
                    array(
                        'message' => "Kindly Provide Access Token"
                    )
                ));
            }
        }
    }
);

$app->get(
    URL_PATH.'/products/get',
    [
        $product,
        'all'
    ]
);

$app->get(
    URL_PATH.'/products/get/{limit}/{page}',
    [
        $product,
        'all'
    ]
);

$app->post(
    URL_PATH.'/get/product',
    [
        $product,
        'search'
    ]
);

$app->get(
    URL_PATH.'/products/search/{keyword}',
    [
        $product,
        'findByKeyword'
    ]
);
/* -----------------------Product End------------------*/

/* -----------------------Order Start------------------*/
$app->before(
    function () use ($app) {
        // $this->user_id;
        if (str_contains($_SERVER['REQUEST_URI'], 'order')) {
            if (null === ($app->request->getQuery("access_token"))) {
                die(json_encode(
                    array(
                        'message' => "Kindly Provide Access Token"
                    )
                ));
            }
        }
    }
);

$app->post(
    URL_PATH.'/order/create',
    [
        $order,
        'create'
    ]
);

$app->put(
    URL_PATH.'/order/update',
    [
        $order,
        'update'
    ]
);
/* -----------------------Order End------------------*/



/**----------------------------------------------------GET REQUEST END---------------------------------------------- */
try {
    $app->handle(
        $_SERVER['REQUEST_URI']
    );
} catch (\Exception $e) {
    return json_encode(
        array(
            "Message" => "Invalid ENDPOINT. Kindly Check!!"
        )
    );
}
