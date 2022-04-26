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
            if (!$this->request->getPost('customer_name')) {
                return false;
            }
            if (!$this->request->getPost('product_id')) {
                return false;
            }
            if (!$this->request->getPost('product_quantity')) {
                return false;
            }
            return true;
        }

        /**
         * Check Update Order Status Input
         */
        if ($check == 'updateStatusOrder') {
            if (!$this->request->getPut('order_id')) {
                return false;
            }
            if (!$this->request->getPut('order_status')) {
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
            /**
             * Setting Default Order Status
             */
            $orderStatus = 'Processing';

            try {
                /**
                 * Checking Whether the Product Exists in the Database or Not
                 */
                try {
                    $product = $this->mongo->api->find(
                        [
                            '_id' => new \MongoDB\BSON\ObjectId(
                                $this->objects->escaper->sanitize(
                                    $this->request->getPost('product_id')
                                )
                            )
                        ]
                    )->toArray();
                } catch (\Exception $e) {
                    return json_encode(
                        array(
                            'Message' => 'Product ID Not Found. Kindly check whether all the fields are Correct'
                        )
                    );
                }

                /**
                 * Product Found in DB hence Creating Order
                 */
                $order = array(
                    'customer_name' => $this->objects->escaper->sanitize($this->request->getPost('customer_name')),
                    'product_id' => $this->objects->escaper->sanitize($this->request->getPost('product_id')),
                    'product_quantity' => $this->objects->escaper->sanitize($this->request->getPost('product_quantity')),
                    'order_status' => $orderStatus,
                    'order_date' => date('Y-m-d'),
                    'modified_by' => $this->user_id,
                );
                if (null !== $this->request->getPost('variant')) {
                    $order['variant'] = $this->objects->escaper->sanitize($this->request->getPost('variant'));
                }

                $result = $this->mongo->order->insertOne($order);
                $stock = ($product[0] -> product_stock) - $order['product_quantity'];
                
                $this->mongo->api->updateOne(
                    [
                        '_id' => new \MongoDB\BSON\ObjectId($order['product_id'])
                    ],
                    [
                        '$set' => [
                            'product_stock' => $stock
                        ]
                    ]
                );

                /**
                 * Trigger Event
                 */
                $product = array(
                    '_id' => $this->objects->escaper->sanitize($this->request->getPost('product_id')),
                    'product_stock' => $stock
                );

                $this->events->fire('listener:reduceStock', $this, $product);
                
                /**
                 * Order Successful Returning Success Message
                 */
                return json_encode(
                    array(
                        'Order_ID' => strval($result->getInsertedId()),
                        'Order_Status' => $orderStatus,
                        'Message' => 'Order Successfully Added'
                    )
                );
            } catch (\Exception $e) {
                /**
                 * Order Failed Returning Error Message
                 */
                return json_encode(
                    array(
                        'Message' => 'Some Error Occured. Kindly check whether all the fields are Correct'
                    )
                );
            }
        } else {
            /**
             * Empty Fields Provided in the POST Request
             */
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
                /**
                 * Checking Whether the Product Exists in the Database or Not
                 */
                try {
                    $this->mongo->order->find(
                        [
                            '_id' => new \MongoDB\BSON\ObjectId(
                                $this->objects->escaper->sanitize(
                                    $this->request->getPut('order_id')
                                )
                            )
                        ]
                    );
                } catch (\Exception $e) {
                    return json_encode(
                        array(
                            'Message' => 'Order ID Not Found. Kindly check whether all the fields are Correct'
                        )
                    );
                }

                /**
                 * Order ID Found Updating Order Status
                 */
                $this->mongo->order->updateOne(
                    [
                        '_id' => new \MongoDB\BSON\ObjectId(
                            $this->objects->escaper->sanitize(
                                $this->request->getPut('order_id')
                            )
                        )
                    ],
                    [
                        '$set' => [
                            'order_status' => $this->objects->escaper->sanitize($this->request->getPut('order_status')),
                            'modified_by' => $this->user_id,
                        ]
                    ]
                );

                /**
                 * Order Update Successful
                 */
                return json_encode(
                    array(
                        'Order_ID' => $this->objects->escaper->sanitize(
                            $this->request->getPut('order_id')
                        ),
                        'Order_Status' => $this->objects->escaper->sanitize(
                            $this->request->getPut('order_status')
                        ),
                        'Message' => 'Order Successfully Updated'
                    )
                );
            } catch (\Exception $e) {
                /**
                 * Order Update Unsuccessful
                 */
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
