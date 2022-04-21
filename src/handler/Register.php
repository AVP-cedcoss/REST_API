<?php

namespace Handler;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use Phalcon\Di\Injectable;

class Register extends Injectable
{
    private $mongo = ' ';

    public function __construct()
    {
        $this->mongo = (new \MongoDB\Client('mongodb://mongo', array('username' => 'root', "password" => 'password123')))->mongodb;
    }

    /**
     * Register Form Display
     *
     * @return void
     */
    public function register()
    {
        $html='
        <form method="POST" action="/register/signup">
            <input type="text" name="user_name" placeholder="username" required>
            <input type="email" name="user_email" placeholder="email" required>
            <input type="password" name="user_password" placeholder="password" required>
            <input type="submit">
        </form>
        ';
        return $html;
    }

    /**
     * Signup Handle
     *
     * @return void
     */
    public function signup()
    {
        $user = array(
            'user_email' => $this->request->getPost('user_email'),
            'user_name' => $this->request->getPost('user_name'),
            'user_password' => $this->request->getPost('user_password'),
            'user_role' => 'User',
        );

        $this->mongo->user->insertOne($user);
        header('location: /register/login');
    }
    
    /**
     * Login Form Display
     *
     * @return void
     */
    public function login()
    {
        $html='
        <form method="POST" action="/register/signin">
            <input type="email" name="user_email" placeholder="email" required>
            <input type="password" name="user_password" placeholder="password" required>
            <input type="submit">
        </form>
        ';
        return $html;
    }

    /**
     * Login Handle
     *
     * @return void
     */
    public function signin()
    {
        $user = array(
            'user_email' => $this->request->getPost('user_email'),
            'user_password' => $this->request->getPost('user_password')
        );
        $result = $this->mongo->user->findOne(
            [
                "user_email" => $this->request->getPost('user_email'),
                'user_password' => $this->request->getPost('user_password')
            ]
        );

        if (isset($result->_id)) {
            header('location: /register/getToken');
        } else {
            header('location: /register/login');
        }
    }

    /**
     * Display Get Token Button
     *
     * @return void
     */
    public function getToken()
    {
        $html='
        <form method="GET" action="/register/generateToken">
            <input type="submit" value="Generate Token">
        </form>
        ';
        return $html;
    }

    /**
     * Generates Token for 5 Minutes
     *
     * @return void
     */
    public function generateToken()
    {
        $key = "anugrah_vishwas_paul";
        
        $now        = new \DateTime();
        $issued     = $now->getTimestamp();
        $notBefore  = $now->modify('+5 minute')->getTimestamp();
        
        $payload = array(
            "iat" => $issued,
            "exp" => $notBefore,
            "sub" => 'granted',
        );

        $jwt = JWT::encode($payload, $key, 'HS256');
        $jwt = array(
            "access_token" => $jwt,
            "expires in" => "5 minutes"
        );
        return json_encode($jwt) ;
    }

    /**
     * if Token Expired
     *
     * @return void
     */
    public function expired()
    {
        $html = '
        <form method="GET" action="/register/generateToken">
            Token Expired. Kindly Generate a New One.
            <input type="submit" value="Click Me!">
        </form>
        ';
        return $html;
    }
}
