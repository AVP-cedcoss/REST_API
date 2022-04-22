<?php

error_reporting(E_ALL);

use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Router;

// Define some absolute path constants to aid in locating resources
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('URL_ROOT', 'http://localhost:8080');

require_once(BASE_PATH."/vendor/autoload.php");

$container = new FactoryDefault();

$loader = new Loader();

$loader->registerNamespaces(
    [
        'Handler' => BASE_PATH . '/handler',
    ]
);

$loader->register();

$container->set(
    'mongo',
    function () {
        $mongo =  new \MongoDB\Client('mongodb://mongo', array('username'=>'root',"password"=>'password123'));
        return $mongo->mongodb;
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

$product = new Handler\Product();
$register = new Handler\Register();
$order = new Handler\Order();


$app = new Micro($container);

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
    '/api',
    [
        $product,
        'help'
    ]
);

$app->before(
    function () use ($app) {
        if (str_contains($_SERVER['REQUEST_URI'], 'products')) {
            if (null === ($app->request->getQuery("access_token"))) {
                die (
                    json_encode(
                        array(
                            'message' => "Kindly Provide Access Token"
                        )
                    )
                );
            }
        }
    }
);

$app->get(
    '/api/products/get',
    [
        $product,
        'all'
    ]
);

$app->get(
    '/api/products/get/{limit}/{page}',
    [
        $product,
        'all'
    ]
);

$app->post(
    '/api/get/product',
    [
        $product,
        'search'
    ]
);

$app->get(
    '/api/products/search/{keyword}',
    [
        $product,
        'findByKeyword'
    ]
);
/* -----------------------Product End------------------*/

/* -----------------------Order Start------------------*/
$app->before(
    function () use ($app) {
        if (str_contains($_SERVER['REQUEST_URI'], 'order')) {
            if (null === ($app->request->getQuery("access_token"))) {
                die (
                    json_encode(
                        array(
                            'message' => "Kindly Provide Access Token"
                        )
                    )
                );
            }
        }
    }
);

$app->post(
    '/api/order/create',
    [
        $order,
        'create'
    ]
);

$app->put(
    '/api/order/update',
    [
        $order,
        'update'
    ]
);
/* -----------------------Order End------------------*/



/**----------------------------------------------------GET REQUEST END---------------------------------------------- */

$app->handle(
    $_SERVER['REQUEST_URI']
);
