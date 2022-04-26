<?php

namespace App\Helper;

use Phalcon\Events\Event;
use Phalcon\Di\Injectable;

class webhook extends Injectable
{
    public function getController()
    {
        $controllers = [];

        foreach (glob(APP_PATH . '/controllers/*Controller.php') as $controller) {
            $className = basename($controller, '.php');
            $temp = substr($className, 0, -10);
            $controllers[$temp] = $temp;
        }
        return $controllers;
    }

    public function getMethod($controller)
    {
        $ActionMethod = [];
        $controller = APP_PATH . '/controllers/' . $controller . 'Controller.php';

        $className = basename($controller, '.php');
        $methods = (new \ReflectionClass($className))->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            if (\Phalcon\Text::endsWith($method->name, 'Action')) {
                $temp = substr($method->name, 0, -6);
                $ActionMethod[$temp] = $temp;
            }
        }
        return $ActionMethod;
    }
}
