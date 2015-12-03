<?php

namespace App;

use App\Application;
use App\Templater;

abstract class Controller {

    public function render($view, array $options = array()) {

        try {
            $viewContent = $this->getView($view);
        } catch (\Exception $e) {
            echo 'Error getting view file: ', $e->getMessage();

        }

        $defaultOptions = $this->defaultOptions();
        $options = array_merge($defaultOptions, $options);
//        $options = array_unique($options);

        try {
            $templater = new Templater();
            $templater->run($viewContent, $options);
        } catch (\Exception $e) {
            echo 'Error running template: ', $e->getMessage();
        }
    }

    private function defaultOptions() {
        return [
            'user_id' => Application::Identity()->check(),
        ];
    }

    protected function getView($view) {

        if (!is_string($view) || empty($view)) {
            throw new \Exception('view file name parameter could not be blank');
        }
        
        $controller = get_class($this);

        $pattern = '#([\w]+)' . Application::getConfigItem('controllerPostfix') . '$#';

        preg_match($pattern, $controller, $matches);

        $controller = $matches[1];

        $viewFilePath = Application::getBasePath() . DIRECTORY_SEPARATOR . Application::getConfigItem('viewsFolder') . DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR . $view;
 
        if (!file_exists($viewFilePath)) {
        
        }
        return file_get_contents($viewFilePath);
    }

    public function actionNotfound() {
        header("HTTP/1.0 404 Not Found");
        $this->render('404.html');
    }

    public function redirect($route) {
        $protocol = !empty($_SERVER['HTTPS'])? 'https://': 'http://';
        $domain = $_SERVER['SERVER_NAME'];
        header("Location: $protocol$domain$route");
    }
}