<?php

use Phalcon\Mvc\Controller;

class WebhookController extends Controller
{
    public function indexAction()
    {

    }

    /**
     * add Product Webhook
     *
     * @return void
     */
    public function addProductAction()
    {
        if ($this->request->isPost()) {
            $product = json_decode($this->request->getPost("data"), true);
            $product["_id"] = new MongoDB\BSON\ObjectId($product['_id']);
            try {
                $this->mongo->product->insertOne($product);
                return json_encode(
                    array(
                        'Message' => 'Data Inserted Successfully'
                    )
                );
            } catch (\Exception $e) {
                return json_encode(
                    array(
                        'Message' => 'Data Not Inserted'
                    )
                );
            }
        }
    }

    /**
     * Update Product Webhook
     *
     * @return void
     */
    public function updateProductAction()
    {
        if ($this->request->isPost()) {
            $product = json_decode($this->request->getPost("data"), true);
            $product["_id"] = new MongoDB\BSON\ObjectId($product['_id']);
            try {
                $this->mongo->product->updateOne(
                    [
                        '_id' => new MongoDB\BSON\ObjectId(strval($product['_id']))
                    ],
                    [
                        '$set' => $product
                    ]
                );
            } catch (\Exception $e) {
                return json_encode(
                    array(
                        'Message' => 'Data Not Updated'
                    )
                );
            }
        }
    }

    /**
     * DeleteProduct Webhook
     *
     * @return void
     */
    public function deleteProductAction()
    {
        if ($this->request->isPost()) {
            try {
                $product = json_decode($this->request->getPost('data'), true);
                $this->mongo->product->deleteOne(
                    [
                        '_id' => new \MongoDB\BSON\ObjectId($product['_id'])
                    ]
                );
                return json_encode(
                    array(
                        'Message' => 'Product Deleted Successfully'
                    )
                );
            } catch (\Exception $e) {
                return json_encode(
                    array(
                        'Message' => 'Data Not Updated',
                        'details' => $e
                    )
                );
            }
        }
    }
}
