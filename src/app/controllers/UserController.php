<?php

use Phalcon\Mvc\Controller;
use Firebase\JWT\JWT;

class UserController extends Controller
{
    public function indexAction()
    {
        
    }

    /**
     * Generates Token for 5 Minutes
     *
     * @return void
     */
    public function generateTokenAction()
    {
        $key = "anugrah_vishwas_paul";
        
        $now        = new \DateTime();
        $issued     = $now->getTimestamp();
        $notBefore  = $now->modify('+30 minute')->getTimestamp();
        
        $payload = array(
            "iat" => $issued,
            "exp" => $notBefore,
            "sub" => $this->session->userDetail->user_id,
        );

        $jwt = JWT::encode($payload, $key, 'HS256');
        $jwt = array(
            "access_token" => $jwt,
            "expires in" => "30 minutes"
        );
        return json_encode($jwt) ;
    }

    public function webhookAction()
    {
        $this->view->webhook = array(
            'Update Product' => 'Update Product',
            'Add Product' => 'Add Product',
            'Delete Product' => 'Delete Product',
        );
    }

    public function registerWebhookAction()
    {
        if ($this->request->isPost()) {
            $webhook = array(
                'type' => $this->request->getPost('webhook'),
                'url' => $this->request->getPost('url')
            );
            $this->webhookDB->product->insertOne($webhook);
            $this->response->redirect(URL_PATH.'/user/webhook');
        }
    }
}
