<?php

namespace App;

use App\Component;
use App\Language;
use App\Router;
use App\Identity;
use App\Db\Db;

class Application {

    const CONFIG_FOLDER = 'config';
    const CONFIG_FILENAME = 'web.php';

    private static $config = [];
    private static $_instance = null;

    private static $language;
    private static $basePath;
    private static $configPath;
    private static $identity;
    private static $db;
    private static $request;

    public static function getInstance() {
        return self::$_instance ?: self::$_instance = new self;
    }

    private function __construct(){
        try {
            self::setConfig();
            self::setBasePath();
            self::$db = new Db();
            self::$request = new Request();
            self::$language = new Language();
            self::$identity = new Identity();
        } catch (\Exception $e) {
            echo 'Error constructing application: ', $e->getMessage();
        }
    }

    private function __clone(){
        throw new \Exception(__CLASS__ . ' is singleton, can not clone it');
    }

    protected static function setBasePath($dirName = __DIR__) {
        if (!is_dir($dirName)) {
            throw new \InvalidArgumentException('invalid directory name: ' . $dirName);
        }
        self::$basePath = rtrim($dirName, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public static function getBasePath() {
        if (empty(self::$basePath)) self::setBasePath();
        return self::$basePath;
    }

    protected static function setConfigPath() {
        if (empty(self::$basePath)) self::setBasePath();
        $configPath = self::$basePath . '..' . DIRECTORY_SEPARATOR . self::CONFIG_FOLDER . DIRECTORY_SEPARATOR;
        if (!is_dir($configPath)) {
            throw new \Exception('could not find config folder');
        }
        self::$configPath = $configPath;
    }

    public static function getConfigPath() {
        if (empty(self::$configPath)) self::setConfigPath();
        return self::$configPath;
    }


    protected static function setConfig() {

        if (empty(self::$configPath)) {
            try {
                self::setConfigPath();
            } catch (\Exception $e) {
                echo 'Error setting config path ', $e->getMessage();
            }
        }
        $configFilePath = self::getConfigPath() . self::CONFIG_FILENAME;

        if (!file_exists($configFilePath)) {
            throw new \Exception(self::CONFIG_FILENAME . ' config file can not be found');
        } 

        $configArray = require($configFilePath);

        if (!is_array($configArray)) {
            throw new \Exception(self::CONFIG_FILENAME . ' config file should return an array');
        }

        self::$config = $configArray;
    }

    public static function getConfigItem($varName) {
        if (!self::$config) self::setConfig();
        if (array_key_exists($varName, self::$config)) {
            return self::$config[$varName];
        }
        return null;
    }

    public static function DB() {
        return self::$db;
    }

    public static function Request() {
        return self::$request;
    }

    public static function Identity() {
        return self::$identity;
    }

    public static function run() {
        self::getInstance();
        Router::run();
    }
}