<?php

use Phalcon\Mvc\Controller;

class OrderController extends Controller
{
    public function indexAction()
    {

    }

    /**
     * Displays Order Form and sends Request to API and Updates to self
     *
     * @return void
     */
    public function addAction()
    {
        $this->view->product = (new Frontend\Models\Orders())->listProductsOrderPage($this->mongo);
        if ($this->request->isPost()) {
            $url = API_ROOT.'/order/create?access_token='.$this->access_token;
            $order = array(
                'customer_name' => $this->objects->escaper->sanitize($this->request->getPost('customer_name')),
                'product_id' => $this->objects->escaper->sanitize($this->request->getPost('product_id')),
                'product_quantity' => $this->objects->escaper->sanitize($this->request->getPost('product_quantity')),
            );
            ((new Frontend\Helper\curl())->APIPOST($url, $order));
            $this->response->redirect(URL_PATH."/user");
        }
    }
}
