<?php

namespace Handler;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Phalcon\Di\Injectable;

class Product extends Injectable
{
    private $mongo = ' ';

    public function __construct()
    {
        $this->mongo = (new \MongoDB\Client('mongodb://mongo', array('username' => 'root', "password" => 'password123')))->mongodb;
    }

    /**
     * Resolves token and checks whether token is Good / Bad
     *
     * @return void
     */
    public function resolveToken()
    {
        $tokenReceived = $this->request->getQuery('access_token');
        $key = "anugrah_vishwas_paul";
        $now           = new \DateTime();
        $expires       = $now->modify('+30 minute')->getTimestamp();

        try {
            $decoded = JWT::decode($tokenReceived, new Key($key, 'HS256'));
        } catch (\Exception $e)
        {
            header('location: /register/expired');
        }
        return $decoded->sub;
    }

    /**
     * Returns all Products with matching Keyword
     *
     * @return void
     */
    public function findByKeyword($keyword)
    {
        $this->resolveToken();

        $words = explode(" ", urldecode($keyword));
        $data = array();
        foreach ($words as $value) {
            $result = $this->mongo->api->find(
                [
                    "name" => [
                        '$regex' => $value,
                        '$options' => '$i'
                    ]
                ]
            )->toArray();

            foreach ($result as $value) {
                $count = 0;
                foreach ($value->variant as $v) {
                    $count++;
                }
                array_push(
                    $data,
                    array(
                        'product_id' => strval($value->_id),
                        'product_name' => $value->product_name,
                        'product_category' => $value->product_category,
                        'product_price' => $value->product_price,
                        'product_stock' => $value->product_stock,
                        'product_variation' => $count,
                    )
                );
            }
        }
        return json_encode($data);
    }

    /**
     * Get Product by ID
     *
     * @return void
     */
    public function search()
    {
        $this-> resolveToken();
        if ($this->request->isPost('product_id') && $this->inputCheck('search')) {
            try {
                $result = $this->mongo->api->findOne(['_id' => new \MongoDB\BSON\ObjectId($this->request->getPost('product_id'))]);

                $data=array();
                $variation=array();
                foreach ($result->variant as $v) {
                    $temp=[];
                    foreach ($v as $key => $value) {
                        $temp[$key] = $value;
                    }
                    array_push($variation, $temp);
                }

                
                $data = array(
                    'product_id' => strval($result->_id),
                    'product_name' => $result->product_name,
                    'product_category' => $result->product_category,
                    'product_price' => $result->product_price,
                    'product_stock' => $result->product_stock,
                    'product_variation' => $variation,
                );

                return json_encode($data);
            } catch (\Exception $e) {

            }
        }
    }

    /**
     * Returns all Products
     *
     * @return void
     */
    public function all($limit = 20, $page = 0, $token = "")
    {
        $this->resolveToken();

        $result = $this->mongo->api->find(
            [],
            [
                "limit" => intval($limit),
                "skip" => intval($page),
            ]
        );
        $data = [];
        foreach ($result as $value) {
            $count = 0;
            foreach ($value->variant as $v) {
                $count++;
            }
            array_push(
                $data,
                array(
                    'product_id' => strval($value->_id),
                    'product_name' => $value->product_name,
                    'product_category' => $value->product_category,
                    'product_price' => $value->product_price,
                    'product_stock' => $value->product_stock,
                    'product_variation' => $count,
                )
            );
        }
        return json_encode($data);
    }

    private function inputCheck($check)
    {
        /**
         * Check search order Inputs
         */
        if ($check == 'search') {
            if (! $this->request->getPost('product_id')) {
                return false;
            }
            return true;
        }
    }

    /**
     * Basic Documentation Help for USER
     *
     * @return void
     */
    public function help()
    {
        $html = '
        <pre>
        <a href="https://github.com/AVP-cedcoss/REST_API">GITHUB</a>
        <strong>API Contains 1000 Products</strong>

            -> api/products/get?access_token={generated Token}
                %{GET}%
                # Provides only 20 Initial Products 

            -> api/products/get/<strong>{limit}</strong>/<strong>{page}</strong>?access_token={generated Token}
                %{GET}%
                # Limit no of products
                # Page No

            -> api/products/search/<strong>{keyword}</strong>?access_token={generated Token}
                %{GET}%
                # returns similar name Products

            -> api/get/product?access_token={generated Token}
                %{POST}%
                # "product_id"

            -> api/order/create?access_token={generated Token}
                %{POST}%
                # "customer_name" = {name}
                # "product_id" = {id}
                # "product_quantity" = {quantity}

            -> api/order/update?access_token={generated Token}
                %{POST}%
                # "order_id" = {order id}
                # "order_status" = {order status}

        </pre>
        ';

        return $html;
    }
}
