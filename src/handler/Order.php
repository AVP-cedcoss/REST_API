<?php

namespace Handler;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use Phalcon\Di\Injectable;

class Order extends Injectable
{
    private $mongo = ' ';

    public function __construct()
    {
        $this->mongo = (new \MongoDB\Client('mongodb://mongo', array('username' => 'root', "password" => 'password123')))->mongodb;
    }

    public function create()
    {
        $result = $this->mongo->product->find();
        $product = [];
        foreach ($result as $value) {
            array_push($product, array(
                'product_id' => strval($value->_id),
                'product_name' => $value->product_name
            )
            );
        }
    }

    private function add()
    {
        if ($this->request->isPost()) {
            $order = array(
                'customer_name' => $this->request->getPost('customer_name'),
                'product_id' => $this->request->getPost('product_id'),
                'product_quantity' => $this->request->getPost('product_quantity'),
                'order_status' => 'Processing',
                'order_date' => date('Y-m-d')
            );
            if (null !== $this->request->getPost('variant')) {
                $order['variant'] = $this->request->getPost('variant');
            }

            $this->mongo->order->insertOne($order);
            $this->response->redirect('admin/order/index');
        }
    }
}