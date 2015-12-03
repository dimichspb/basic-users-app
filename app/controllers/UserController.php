<?php

namespace App\Controllers;

use App\Application;
use App\Controller;
use App\Models\User;
use App\Helper;

class UserController extends Controller {
  
    public function actionIndex() {
        $user_id = Application::Identity()->check();
        if (!$user_id) {
            $this->redirect('/login');
        } 

        $post = Application::request()->post();
        $model = new User();
        $user = $model->findByField('user_id', $user_id);
        $usersList = [];
            
        if (!empty($post['search'])) {
            $usersList = $model->findByFieldsLike([
                'email' => $post['search'],
                'name'  => $post['search'],
            ]);

            if (!empty($usersList['user_id'])) {
                $usersList = [ $usersList ];
            }
        }

        $this->render('index.html', [
            'user'      => $user,
            'usersList' => $usersList,
        ]);
     }

    public function actionLogin() {

        $post = Application::request()->post();
        $warning = '';

        if (!empty($post['email'])) {
            $model = new User();
            $user = $model->findByField('email', $post['email']);
            if ($user) {
                if (Helper::compareHash($user['password'], $post['password'])) {
                    Application::Identity()->checkin($user['user_id']);
                    $this->redirect('/search');
                } else {
                    $warning = 'Password is wrong';
                }
            } else {
                $warning = 'E-mail not found';
            }
        }


        if (!Application::Identity()->check()) {
            $this->render('login.html', [
                'warning' => $warning,
            ]);
        } else {
            $this->redirect('/search');
        }
    }

    public function actionSignup() {

        $post = Application::request()->post();
        $warning = '';
        
        if (sizeof($post) > 0) {
            $model = new User();
            $model->load($post);
            $validation = $model->validate();
            if ($validation) {
                $user_id = $model->save();
                if ($user_id) {
                    Application::Identity()->signin($user_id);
                    $this->redirect('/search');
                } else {
                    $warning = 'Error adding row to DB';
                }
            } else {
                $warning = 'Please enter correct fields values';
            }
        }

        if (!Application::Identity()->check()) {
            $this->render('signup.html', [
                'salt' => Helper::generateCode(15),
                'warning' => $warning,
            ]);
        } else {
            $this->redirect('/search');
        }
    }

    public function actionLogout() {
        $user_id = Application::Identity()->check();
        if ($user_id) {
            Application::Identity()->checkout($user_id);
            $this->render('logout.html');
        } else {
            $this->redirect('/login');
        }
    }
}