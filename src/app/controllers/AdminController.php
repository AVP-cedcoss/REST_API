<?php

use Phalcon\Mvc\Controller;

class AdminController extends Controller
{
    public function indexAction()
    {
        $this->view->content = $this->orderList();
    }

    private function orderList()
    {
        $html='';
        $result = $this->mongo->order->find();
        foreach ($result as $key => $value) {
            $html .= '
                <tr>
                    <td>
                        ' . $this->mongo->user->findOne(['_id' => new \MongoDB\BSON\ObjectId($value->modified_by)])-> user_email . '
                    </td>
                    <td>
                        ' .  $this->mongo->api->findOne(['_id' => new \MongoDB\BSON\ObjectId($value->product_id)])-> product_name . '
                    </td>
                    <td>
                        ' . $this->mongo->api->findOne(['_id' => new \MongoDB\BSON\ObjectId($value->product_id)])-> product_price . '
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
