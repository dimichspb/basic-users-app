<?php

namespace App\Controllers;

use App\Application;
use App\Router;
use App\Controller;


class SiteController extends Controller {

    public function actionIndex() {
        $this->render('index.html');
    }
}