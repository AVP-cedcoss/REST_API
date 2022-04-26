<?php

namespace Api\Helper;

use AdminController;
use ProductController;
use OrderController;
use Phalcon\Acl\Adapter\Memory;
use Phalcon\Events\Event;
use Phalcon\Di\Injectable;

class listener extends Injectable
{
    /**
     * updateProduct Event Handler
     * Returns the input Data
     *
     * @param Event $event
     * @param Order $obj
     * @return array
     */
    public function reduceStock(Event $event, $obj, $product)
    {
        $result = $obj->webhookDB->product->find(
            [
                'type' => 'Update Product'
            ]
        )->toArray();

        foreach ($result as $value) {
            ((new \Api\Helper\curl())->webhookPost($value->url, json_encode($product)));
        }
        return;
    }

    /**
     * addProduct Event Handler
     * Returns the input Data
     *
     * @param Event $event
     * @param AdminController $obj
     * @return array
     */
    public function addProduct(Event $event, AdminController $obj, $product)
    {
        $result = $obj->webhookDB->product->find(
            [
                'type' => 'Add Product'
            ]
        )->toArray();
        foreach ($result as $value) {
            ((new \App\Helper\curl())->webhookPost($value->url, json_encode($product)));
        }
        return;
    }
}
