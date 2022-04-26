<?php

use Phalcon\Mvc\Controller;

class LoginController extends Controller
{
    public function indexAction()
    {

    }

    public function registerAction()
    {

    }

    public function signupAction()
    {
        $user = array(
            'user_email' => $this->objects->escaper->sanitize($this->request->getPost('user_email')),
            'user_name' => $this->objects->escaper->sanitize($this->request->getPost('user_name')),
            'user_password' => $this->objects->escaper->sanitize($this->request->getPost('user_password')),
            'user_role' => 'User',
        );

        $this->mongo->user->insertOne($user);
        $this->objects->logger->info("User Registered: '" . $user['user_email'] . "'");
        $this->response->redirect(URL_PATH.'/login');
    }

    public function loginAction()
    {
        $user = array(
            'user_email' => $this->objects->escaper->sanitize($this->request->getPost('user_email')),
            'user_password' => $this->objects->escaper->sanitize($this->request->getPost('user_password'))
        );

        $result = $this->mongo->user->findOne(
            [
                "user_email" => $this->objects->escaper->sanitize($this->request->getPost('user_email')),
                'user_password' => $this->objects->escaper->sanitize($this->request->getPost('user_password'))
            ]
        );
        
        if (isset($result->_id)) {
            $user['user_id'] = strval($result->_id);
            $this->session->set('userDetail', (object)$user);
            $this->objects->logger->info("User Logged in: '" . $user['user_email'] . "'");

            if ($result->user_role === 'Admin') {
                echo $result->user_role;
                $this->response->redirect(URL_PATH.'/admin');
            } else {
                $this->response->redirect(URL_PATH.'/user');
            }
        } else {
            $this->objects->logger->critical("Failed User Log in: '" . $user['user_email'] . "'");
            $this->response->redirect(URL_PATH.'/login');
        }
    }
}
