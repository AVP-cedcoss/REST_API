<?php

use Phalcon\Mvc\Controller;
use Firebase\JWT\JWT;

class TokenController extends Controller
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
        $notBefore  = $now->modify('+5 minute')->getTimestamp();
        
        $payload = array(
            "iat" => $issued,
            "exp" => $notBefore,
            "sub" => $this->session->userDetail->user_id,
        );

        $jwt = JWT::encode($payload, $key, 'HS256');
        $jwt = array(
            "access_token" => $jwt,
            "expires in" => "5 minutes"
        );
        return json_encode($jwt) ;
    }
}
