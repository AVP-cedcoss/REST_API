<?php

use Phalcon\Mvc\Controller;

class ProductController extends Controller
{
    /**
     * Index Function
     * lists the Products Available
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view->content = $this->productListing();
    }

    /**
     * creates and returns html for Product listing
     *
     * @return html
     */
    private function productListing()
    {
        $result = $this->mongo->product->find();
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
                        <a data-id="' . strval($value->_id) . '" class="quickPeek btn btn-primary text-light" data-toggle="modal" data-target="#exampleModal">
                            Quick Peek
                        </a>
                    </td>
                    ';
            $html .= '</tr>';
        }
        return $html;
    }

    /**
     * productDetail
     * returns the Product detail
     *
     * @return void
     */
    public function productDetailAction()
    {
        $result = $this->mongo->product->findOne(["_id" => new \MongoDB\BSON\ObjectId($this->request->getPost('id'))]);
        return json_encode($result);
    }
}
