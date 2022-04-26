<?php

use Phalcon\Mvc\Controller;

class AdminController extends Controller
{
    public function indexAction()
    {
    }

    public function orderAction()
    {
        $this->view->content = $this->orderList();
    }

    public function listProductAction()
    {
        $this->view->content = $this->productListing();
    }

    /**
     * Add Action
     * Displays the Form and inserts to Database
     *
     * @return void
     */
    public function addProductAction()
    {
        if ($this->request->isPost()) {
            if (null !== ($this->request->getPost('additionalKey'))) {
                $meta = array_combine($this->request->getPost('additionalKey'), $this->request->getPost('additionalvalue'));
            }

            if (null !== ($this->request->getPost('variationKey'))) {
                $varient = array();
                for ($i = 0; $i < (count($this->request->getPost('variationKey'))); $i++) {
                    array_push($varient, array_combine($this->request->getPost('variationKey')[$i], $this->request->getPost('variationValue')[$i]));
                }
                for ($i = 0; $i < (count($this->request->getPost('variationKey'))); $i++) {
                    $varient[$i]["VariantPrice"] = $this->request->getPost("variationPrice" . $i);
                }
            }
            $product = array(
                'product_name' => $this->objects->escaper->sanitize($this->request->getPost('product_name')),
                'product_category' => $this->objects->escaper->sanitize($this->request->getPost('product_category')),
                'product_stock' => $this->objects->escaper->sanitize($this->request->getPost('product_stock')),
                'product_price' => $this->objects->escaper->sanitize($this->request->getPost('product_price')),
            );
            if (isset($meta)) {
                $product['meta'] = $meta;
            }
            if (isset($varient)) {
                $product['variant'] = $varient;
            }

            $result = $this->mongo->api->insertOne($product);
            $product['_id'] = strval($result->getInsertedId());
            /**
             * Trigger Event
             */
            $this->events->fire('listener:addProduct', $this, $product);
            $this->response->redirect(URL_PATH . '/admin/listProduct');
        }
    }

    /**
     * Delete Action
     * Deletes the product by _id passed in get 'id'
     *
     * @return void
     */
    public function deleteProductAction()
    {
        if (null !== $this->request->getQuery('id')) {
            $this->mongo->api->deleteOne(["_id" => new \MongoDB\BSON\ObjectId($this->request->getQuery('id'))]);
            
            /**
             * Trigger Event
             */
            $product = array(
                '_id' => $this->objects->escaper->sanitize($this->request->getQuery('id')),
            );
            $this->events->fire('listener:deleteProduct', $this, $product);
            $this->response->redirect(URL_PATH . "/admin/listProduct");
        }
    }

    /**
     * productDetail
     * returns the Product detail
     *
     * @return void
     */
    public function productDetailAction()
    {
        $result = $this->mongo->api->findOne(["_id" => new \MongoDB\BSON\ObjectId($this->request->getPost('id'))]);
        return json_encode($result);
    }

    /**
     * searchProducts
     * returns the Products
     *
     * @return void
     */
    // public function searchProductsAction()
    // {
    //     // $result = $this->mongo->api->find(["product_name" => $this->request->getPost('name')]);
    //     // $words = explode(" ", urldecode($this->request->getPost('name')));
    //     // $words = $this->request->getPost('name');
    //     $words = $this->request->getQuery('name');
    //     $data = array();
    //     // foreach ($words as $value) {

    //         $result = $this->mongo->api->find(
    //             [
    //                 "name" => [
    //                     '$regex' => $words,
    //                     '$options' => '$i'
    //                 ]
    //             ]
    //         );
    //         echo "<pre>";
    //         print_r($result);

    //         foreach ($result as $value) {
    //             echo "114";
    //             // $count = 0;
    //             // foreach ($value->variant as $v) {
    //             //     $count++;
    //             // }
    //             // echo "<pre>";
    //             print_r($value);
    //             die;
    //             array_push(
    //                 $data,
    //                 array(
    //                     'product_id' => strval($value->_id),
    //                     'product_name' => $value->product_name,
    //                     'product_category' => $value->product_category,
    //                     'product_price' => $value->product_price,
    //                     'product_stock' => $value->product_stock,
    //                 )
    //             );
    //         }
    //     // }
    //     // $Values=array();
    //     // foreach ($result as $value) {
    //     //     array_push($Values, $value);
    //     // }
    //     // return json_encode($value);
    //     return json_encode($data);
    // }

    /**
     * Update Function Displays the Product and updates it
     *
     * @return void
     */
    public function updateProductAction()
    {
        $this->view->result = json_decode(json_encode($this->mongo->api->findOne(["_id" => new \MongoDB\BSON\ObjectId($this->request->getQuery('id'))])));

        if ($this->request->isPost()) {
            if (null !== $this->request->getPost('additionalKey')) {
                $meta = array_combine($this->request->getPost('additionalKey'), $this->request->getPost('additionalvalue'));
            }

            if (null !== $this->request->getPost('variationKey')) {
                $variant = array();
                if ($this->request->getPost('variationKey')[500]) {
                    for ($i = 500; $i < (count($this->request->getPost('variationKey')) + 500); $i++) {
                        array_push($variant, array_combine($this->request->getPost('variationKey')[$i], $this->request->getPost('variationValue')[$i]));
                    }
                    for ($i = 500; $i < (count($this->request->getPost('variationKey'))); $i++) {
                        $variant[$i]["VariantPrice"] = $this->request->getPost("variationPrice" . $i);
                    }
                } else {
                    for ($i = 0; $i < (count($this->request->getPost('variationKey'))); $i++) {
                        array_push($variant, array_combine($this->request->getPost('variationKey')[$i], $this->request->getPost('variationValue')[$i]));
                    }
                    for ($i = 0; $i < (count($this->request->getPost('variationKey'))); $i++) {
                        $variant[$i]["VariantPrice"] = $this->request->getPost("variationPrice" . $i);
                    }
                }
            }

            $product = array(
                'product_name' => $this->request->getPost('product_name'),
                'product_category' => $this->request->getPost('product_category'),
                'product_stock' => intval($this->request->getPost('product_stock')),
                'product_price' => intval($this->request->getPost('product_price')),
            );
            if (isset($meta)) {
                $product['meta'] = $meta;
            }
            if (isset($variant)) {
                $product['variant'] = $variant;
            }
            $this->mongo->api->updateOne(
                [
                    "_id" => new MongoDB\BSON\ObjectId($this->request->getQuery('id'))
                ],
                [
                    '$set' => $product
                ]
            );

            /**
             * Trigger Event
             */
            $this->events->fire('listener:updateProduct', $this, $product);
            $this->response->redirect(URL_PATH.'/admin/listProduct');
        }
    }

    /**
     * creates and returns html for Product listing
     *
     * @return html
     */
    private function productListing()
    {
        $result = $this->mongo->api->find();
        $html = '';
        foreach ($result as $key => $value) {
            $html .= '
                <tr>
                    <td>
                        ' . $value->product_name . '
                    </td>
                    <td>
                        ' . $value->product_category . '
                    </td>
                    <td>
                        ' . $value->product_price . '
                    </td>
                    <td>
                        ' . $value->product_stock . '
                    </td>
                    <td>
                        <a href="' . URL_PATH . '/admin/deleteProduct?id=' . $value->_id . '" class="btn btn-danger">Delete</a>
                    </td>
                    <td>
                        <a href="' . URL_PATH . '/admin/updateProduct?id=' . $value->_id . '" class="btn btn-warning">Update</a>
                    </td>
                    <td>
                        <a data-id="' . $value->_id . '" class="quickPeek btn btn-primary text-light" data-toggle="modal" data-target="#exampleModal">
                            Quick Peek
                        </a>
                    </td>
                    ';
            $html .= '</tr>';
        }
        return $html;
    }

    /**
     * creates orders list and returns html
     *
     * @return html
     */
    private function orderList()
    {
        $html = '';
        $result = $this->mongo->order->find();
        foreach ($result as $key => $value) {
            $html .= '
                <tr>
                    <td>
                        ' . $this->mongo->user->findOne(['_id' => new \MongoDB\BSON\ObjectId($value->modified_by)])->user_email . '
                    </td>
                    <td>
                        ' .  $this->mongo->api->findOne(['_id' => new \MongoDB\BSON\ObjectId($value->product_id)])->product_name . '
                    </td>
                    <td>
                        ' . $this->mongo->api->findOne(['_id' => new \MongoDB\BSON\ObjectId($value->product_id)])->product_price . '
                    </td>
                    <td>
                        ' . $value->order_date . '
                    </td>
                    <td>
                        ' . $value->order_status . '
                    </td>
                    
                    ';
            $html .= '</tr>';
        }
        return $html;
    }
}
