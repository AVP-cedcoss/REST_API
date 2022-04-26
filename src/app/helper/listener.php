<?php

namespace App\Helper;

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
     * @param AdminController $obj
     * @return array
     */
    public function updateProduct(Event $event, AdminController $obj, $product)
    {
        $result = $obj->webhookDB->product->find(
            [
                'type' => 'Update Product'
            ]
        )->toArray();
        $product['_id'] = $obj->request->getQuery('id');
        foreach ($result as $value) {
            ((new \App\Helper\curl())->webhookPost($value->url, json_encode($product)));
        }
        return;
    }

    /**
     * deleteProduct Event Handler
     * Returns the input Data
     *
     * @param Event $event
     * @param AdminController $obj
     * @return array
     */
    public function deleteProduct(Event $event, AdminController $obj, $product)
    {
        $result = $obj->webhookDB->product->find(
            [
                'type' => 'Delete Product'
            ]
        )->toArray();
        foreach ($result as $value) {
            ((new \App\Helper\curl())->webhookPost($value->url, json_encode($product)));
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
    /**
     * add Order
     *
     * @param Event $event
     * @param OrderController $obj
     * @return array
     */
    public function addOrder(Event $event, OrderController $obj)
    {
        $setting = \Settings::find()[0];
        
        $inputdata = array(
            'customer_name' => $this->objects->sanitize->html($this->request->getPost('customer_name')),
            'customer_address' => $this->objects->sanitize->html($this->request->getPost('customer_address')),
            'product' => $this->objects->sanitize->html($this->request->getPost('product')),
            'product_quantity' => $this->objects->sanitize->html($this->request->getPost('product_quantity')),
        );

        /**
         * If No Zipcode Given Using Default Zipcode
         */
        if ($this->request->getPost('zipcode') === '') {
            $inputdata['zipcode'] = $setting->default_zipcode;
        }/**
         * Using Given Zipcode
         */ else {
            $inputdata['zipcode'] = $this->objects->sanitize->html($this->request->getPost('zipcode'));
        }

        return $inputdata;
    }
}
