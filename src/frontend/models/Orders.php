<?php

namespace Frontend\Models;

use Phalcon\Mvc\Model;

class Orders extends Model
{
    public function listProductsOrderPage($mongo)
    {
        $result = $mongo->product->find();
        $product = [];
        foreach ($result as $value) {
            array_push($product, array(
                    'product_id' => strval($value->_id),
                    'product_name' => $value->product_name
                )
            );
        }
        return $product;
    }
}
