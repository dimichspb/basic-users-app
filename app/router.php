<?php

namespace App;

use App\Application;

class Router {

    const ROUTES_FILENAME = 'routes.php';

    const NOTFOUND_CONTROLLER = 'Site';
    const NOTFOUND_ACTION = 'notfound';

    private static $routes = [];
    private static $_instance = null;

    private static $controller;
    private static $action;
    private static $params = array();

    public static $renderer;

    public static function getInstance(array $options = array()) {
        if (null === self::$_instance) {
            self::$_instance = new self($options);
        }
        return self::$_instance;
    }

    private function __construct(array $options = array()){
        try {
            self::setRoutes();
        } catch (\Exception $e) {
            echo 'Error loading routes ', $e->getMessage();
        }

        if (empty($options)) {
            self::parseUri();
        } else {
            try {
                if (isset($options['controller'])) {
                    self::setController($options['controller']);
                }
                if (isset($options['action'])) {
                    self::setAction($options['action']);
                }
                if (isset($options['parameters'])) {
                    self::setParameters($options['parameters']);
                }
            } catch (\Exception $e) {
                echo 'Error routing: ', $e->getMessage();
            }
        }
    }

    private function __clone(){
    }

    private static function setRoutes() {

        $routesFilePath = Application::getConfigPath() . self::ROUTES_FILENAME;

        if (!file_exists($routesFilePath)) {
            throw new \Exception(self::ROUTES_FILENAME . ' config file can not be found');
        } 

        $routesArray = require($routesFilePath);

        if (!is_array($routesArray)) {
            throw new \Exception(self::ROUTES_FILENAME . ' config file should return an array');
        }

        if (sizeof($routesArray) == 0) {
            throw new \Exception(self::ROUTES_FILENAME . ' config file should contain at least one route');
        }

        foreach ($routesArray as $key => $value) {
            if ($key[0] != '/') {
                throw new \Exception('Each route in routes config file should starts from slash');
            }
        }

        self::$routes = $routesArray;
        return;
    }

    private static function getRoutes() {

        if (!is_array(self::$routes) || sizeof(self::$routes) == 0) {
            try {
                self::setRoutes();
            } catch (\Exception $e) {
                echo 'Error reading routes config file: ', $e->getMessage();
            }
        }
        return self::$routes;
    }

    public static function getUri(){
        if(!empty($_SERVER['REQUEST_URI'])) {
            return trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), DIRECTORY_SEPARATOR);
        }
    }

    public static function parseUri() {
        $uri = self::getUri();

        $routes = self::getRoutes();

        foreach($routes as $pattern => $route) {

            if($pattern === $uri) {

                $segments = explode('/', $route);

                if (sizeof($segments) >= 2) {
                    try {
                        self::setController(array_shift($segments));
                        self::setAction(array_shift($segments));
                        self::setParameters($segments);
                    } catch (\Exception $e) {
                        echo 'Error setting route: ', $e->getMessage();
                    }
                }
            return;
            }
        }        
        return;
    }

    public static function setController($controller = self::NOTFOUND_CONTROLLER) {

        if (empty($controller)) $controller = self::NOTFOUND_CONTROLLER;

        $controller = __NAMESPACE__ . DIRECTORY_SEPARATOR . Application::getConfigItem('controllersFolder'). DIRECTORY_SEPARATOR . ucfirst(strtolower($controller)) . Application::getConfigItem('controllerPostfix');

        if (!class_exists($controller)) {
            throw new \Exception('could not find ' . $controller . ' controller');
        }

        self::$controller = $controller;
        return;
    }

    public static function getController() {
        if (empty(self::$controller)) {
            try {
                self::setController();
            } catch (\Exception $e) {
                echo 'Error setting controller: ', $e->getMessage();
            }
        }
        return (string)self::$controller;
    }
    
    public static function setAction($action = self::NOTFOUND_ACTION) {
        if (empty($action)) $action = self::NOTFOUND_ACTION;
        $action = Application::getConfigItem('actionPrefix') . ucfirst($action);

        $controller = self::getController();

        if (empty($controller)) {
            throw new \Exception('controller should be set before action');
        }

        $reflector = new \ReflectionClass($controller);

        if (!$reflector->hasMethod($action)) {
            throw new \Exception('controller action ' . $action . ' has been not defined');
        }

        self::$action = $action;
        return;
    }

    public static function getAction() {
        if (empty(self::$action)) {
            try {
                self::setAction();
            } catch (\Exception $e) {
                echo 'Error setting action: ', $e->getMessage();
            }
        }
        return (string)self::$action;
    }

    public static function setParameters(array $params) {
        self::$params = $params;
        return;
    }

    public static function getParameters() {
        return (array)self::$controller;
    }
    
    public static function run() {
        self::getInstance();
        $controller = self::getController();
        $action = self::getAction();
        $parameters = self::getParameters();

        call_user_func_array(array(new $controller, $action), $parameters);
    }
}