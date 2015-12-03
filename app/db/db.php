<?php

namespace App\Db;

use App\Application;

class Db {

    const DB_CONFIG_FILENAME = 'db.php';

    private $config = [];
    private $_instance = null;

    public function __construct() {

        $this->setConfig();

        $provider = ucfirst($this->getConfigItem('provider'));

        $config = $this->getConfigItem('config');

        $this->_instance = new $provider($config);

    }

    public function __call($name, $arguments) {
        return $this->_instance->$name($arguments[0]);
    }

    private function setConfig() {

        $dbConfigFilePath = Application::getConfigPath() . self::DB_CONFIG_FILENAME;

        if (!file_exists($dbConfigFilePath)) {
            throw new \Exception(self::DB_CONFIG_FILENAME . ' config file can not be found');
        } 

        $dbConfigArray = require($dbConfigFilePath);

        if (!is_array($dbConfigArray)) {
            throw new \Exception(self::DB_CONFIG_FILENAME . ' config file should return an array');
        }

        $this->config= $dbConfigArray;
        return;
    }

    private function getConfigItem($index) {

        if (!is_array($this->config) || sizeof($this->config) == 0) {
            try {
                $this->setConfig();
            } catch (\Exception $e) {
                echo 'Error reading db config file: ', $e->getMessage();
            }
        }
        return $this->config[$index];
    }
}