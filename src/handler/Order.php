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
        $result = $this->mongo->api->find();
        $html = '';
        $product = [];
        foreach ($result as $value) {
            array_push($product, array(
                'product_id' => strval($value->_id),
                'product_name' => $value->product_name
                )
            );
        }
        
        $html.="
            <form method='POST' action='/order/add'>
                <input type='text' name='customer_name' placeholder='Customer Name' required>
                <input type='text' name='product_quantity' placeholder='Product Quantity' required>
        ";
        
        $html .="
                <label for='product_id'>Category</label>
                <select name='product_id' id='product_id_order' class='form-control' required>";
        foreach ($product as $value) {
            $html.= "<option value='".$value['product_id']."'>".$value['product_name']."</option>";
        }
        $html .="
            </select>
            <input type='submit'>
        </form>
        ";
        return $html;

    }

    public function add()
    {
        if ($this->request->isPost()) {
            $orderStatus='Processing';
            try {
                $order = array(
                    'customer_name' => $this->request->getPost('customer_name'),
                    'product_id' => $this->request->getPost('product_id'),
                    'product_quantity' => $this->request->getPost('product_quantity'),
                    'order_status' => $orderStatus,
                    'order_date' => date('Y-m-d')
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
        }
    }
}