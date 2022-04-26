<?php

namespace Frontend\Helper;

use ProductController;
use OrderController;
use Phalcon\Acl\Adapter\Memory;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Phalcon\Events\Event;
use Phalcon\Di\Injectable;

class listener extends Injectable
{
    /**
     * addProduct Event Handler
     * Returns the input Data
     *
     * @param Event $event
     * @param ProductController $obj
     * @return array
     */
    public function updateStock(Event $event, ProductController $obj, $product_id)
    {
        
    }
}
