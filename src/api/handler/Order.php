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

    private function inputCheck($check)
    {
        /**
         * Check Create order Inputs
         */
        if ($check == 'createOrder') {
            if (! $this->request->getPost('customer_name')) {
                return false;
            }
            if (! $this->request->getPost('product_id')) {
                return false;
            }
            if (! $this->request->getPost('product_quantity')) {
                return false;
            }
            return true;
        }

        /**
         * Check Update Order Status Input
         */
        if ($check == 'updateStatusOrder') {
            if (! $this->request->getPut('order_id')) {
                return false;
            }
            if (! $this->request->getPut('order_status')) {
                return false;
            }
            return true;
        }
    }

    /**
     * Add Order
     *
     * @return void
     */
    public function create()
    {
        if ($this->request->isPost() && $this->inputCheck('createOrder')) {
            $orderStatus='Processing';
            try {
                $order = array(
                    'customer_name' => $this->request->getPost('customer_name'),
                    'product_id' => $this->request->getPost('product_id'),
                    'product_quantity' => $this->request->getPost('product_quantity'),
                    'order_status' => $orderStatus,
                    'order_date' => date('Y-m-d'),
                    'modified_by' => (new Product())->resolveToken(),
                );
                if (null !== $this->request->getPost('variant')) {
                    $order['variant'] = $this->request->getPost('variant');
                }

                $result = $this->mongo->order->insertOne($order);
                return json_encode(
                    array(
                        'Order_ID' => $result->getInsertedId(),
                        'Order_Status' => $orderStatus,
                        'Message' => 'Order Successfully Added'
                    )
                );
            } catch (\Exception $e) {
                return json_encode(
                    array(
                        'Message' => 'Some Error Occured. Kindly check whether all the fields are Correct'
                    )
                );
            }
        } else {
            return json_encode(
                array(
                    'Message' => 'Fields Cannot Be Empty'
                )
            );
        }
    }

    /**
     * Update Order Status
     *
     * @return void
     */
    public function update()
    {
        if ($this->request->isPut() && $this->inputCheck('updateStatusOrder')) {
            try {
                $this->mongo->order->updateOne(
                    [
                        '_id' => new \MongoDB\BSON\ObjectId($this->request->getPut('order_id'))
                    ],
                    [
                        '$set' => [
                            'order_status' => $this->request->getPut('order_status'),
                            'modified_by' => (new Product())->resolveToken(),
                        ]
                    ]
                );
    
                return json_encode(
                    array(
                        'Order_ID' => $this->request->getPut('order_id'),
                        'Order_Status' => $this->request->getPut('order_status'),
                        'Message' => 'Order Successfully Updated'
                    )
                );
            } catch (\Exception $e) {
                return json_encode(
                    array(
                        'Message' => 'Some Error Occured. Kindly check whether all the fields are Correct'
                    )
                );
            }
        } else {
            return json_encode(
                array(
                    'Message' => 'Fields Cannot Be Empty'
                )
            );
        }
    }
}

