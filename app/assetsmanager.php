<?php

namespace App;

use App\Application;
use App\Helper;

class AssetsManager {

    private static $assetsFolder;
    private static $cssFolder;
    private static $jsFolder;

    const BOOTSTRAP_FOLDER = 'vendor/twbs/bootstrap/dist';

    private function __construct() {
        require_once(__DIR__ . '/autoload.php');
        Application::getInstance();

        try {
            self::setAssetsFolder();
            self::setCssFolder();
            self::setJsFolder(Application::getConfigItem('jsFolder'));
        } catch (\Exception $e) {
            echo 'Error setting assets: ', $e->getMessage();
        }
    }

    private static function setAssetsFolder($assetsFolder = '') {
        if (empty($assetsFolder)) $assetsFolder = Application::getBasePath() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . Application::getConfigItem('webFolder') . DIRECTORY_SEPARATOR . Application::getConfigItem('assetsFolder');

        if (!is_dir($assetsFolder)) {
            throw new \Exception('assetsFolder is not a folder');
        }
        self::$assetsFolder = $assetsFolder;
    }

    private static function getAssetsFolder() {
        if (empty(self::$assetsFolder)) {
            self::setAssetsFolder();
        }
        return self::$assetsFolder;
    }

    private static function setCssFolder($cssFolder = '') {
        if (empty($cssFolder)) {
            $cssFolder = self::getAssetsFolder() . DIRECTORY_SEPARATOR . Application::getConfigItem('cssFolder');
        } else {
            $cssFolder = self::getAssetsFolder() . DIRECTORY_SEPARATOR . $cssFolder;
        }

        if (!is_dir($cssFolder)) {
            throw new \Exception('cssFolder is not a folder');
        }
        self::$cssFolder = $cssFolder;
    }

    private static function getCssFolder() {
        if (empty(self::$cssFolder)) {
            self::setCssFolder();
        }
        return self::$cssFolder;
    }


    private static function setJsFolder($jsFolder = '') {
        if (empty($jsFolder)) {
            $jsFolder = self::getAssetsFolder() . DIRECTORY_SEPARATOR . Application::getConfigItem('jsFolder');
        } else {
            $jsFolder = self::getAssetsFolder() . DIRECTORY_SEPARATOR . $jsFolder;
        }

        if (!is_dir($jsFolder)) {
            throw new \Exception('jsFolder is not a folder');
        }
        self::$jsFolder = $jsFolder;
    }

    private static function getJsFolder() {
        if (empty(self::$jsFolder)) {
            self::setJsFolder();
        }
        return self::$jsFolder;
    }

    public static function postUpdate() {
        try {
            self::copyBootstrap();
        } catch (\Exception $e) {
            echo 'Error updating assets: ', $e->getMessage();
        }

    }

    private static function copyBootstrap() {
        $bootStrapFolder = self::BOOTSTRAP_FOLDER;
        $assetsFolder = self::getAssetsFolder();

        if (!is_dir($bootStrapFolder)) {
            throw new \Exception('can not find bootstrap folder');
        }

        if (!is_dir($assetsFolder)) {
            throw new \Exception('can not find assets folder');
        }

        Helper::copy($bootStrapFolder, $assetsFolder);

    }
}