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

    public function resolveToken()
    {
        $tokenReceived = $this->request->getQuery('access_token');
        $key = "anugrah_vishwas_paul";
        $now           = new \DateTime();
        $expires       = $now->modify('+10 day')->getTimestamp();

        try
        {
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
                foreach ($value->labelValue as $v) {
                    $count++;
                }
                array_push(
                    $data,
                    array(
                        'product_id' => strval($value->_id),
                        'product_name' => $value->name,
                        'product_category' => $value->categoryName,
                        'product_price' => $value->price,
                        'product_stock' => $value->stock,
                        'product_variation' => $count,
                    )
                );
            }
        }
        return json_encode($data);
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
            foreach ($value->labelValue as $v) {
                $count++;
            }
            array_push(
                $data,
                array(
                    'product_id' => strval($value->_id),
                    'product_name' => $value->name,
                    'product_category' => $value->categoryName,
                    'product_price' => $value->price,
                    'product_stock' => $value->stock,
                    'product_variation' => $count,
                )
            );
        }
        return json_encode($data);
    }

    public function help()
    {
        $html = '
        <pre>
        <strong>API Contains 1000 Products</strong>

            -> register/register
                # Register Form

            -> register/login
                #login Form

            -> products/get?access_token={generated Token}
                # Provides only 20 Initial Products 

            -> products/get/<strong>{limit}</strong>/<strong>{page}</strong>?access_token={generated Token}
                # Limit no of products
                # Page No

            -> products/search/<strong>{keyword}</strong>?access_token={generated Token}
                # returns similar name Products
        </pre>
        ';

        return $html;
    }
}
