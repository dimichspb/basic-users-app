<?php

namespace App;

use App\Application;

class Templater {

    const NOTFOUND_VIEW = '404.html';
    const DEFAULT_LAYOUT = 'layout.html';

    public function __construct() {

        $twigTemplatesPath = Application::getBasePath() .  DIRECTORY_SEPARATOR . Application::getConfigItem('viewsFolder');

        try {
            $this->twigLoaderFileSystem = new \Twig_Loader_Filesystem($twigTemplatesPath);
        } catch (\Exception $e) {
            echo 'Error loading Twig: ', $e->getMessage();
        }

    }

    public function run($viewContent, array $options = array()) {

        $extendsBlock = '{% extends "' . self::DEFAULT_LAYOUT . '" %}';
        $contentStartBlock = '{% block content %}';
        $contentEndBlock = '{% endblock %}';

        $viewContent = $extendsBlock . $contentStartBlock . $viewContent . $contentEndBlock;

        if (array_key_exists('layout', $options)) $layoutFileName = $options['layout'];
        else $layoutFileName = self::DEFAULT_LAYOUT;

        $layoutFilePath = Application::getBasePath() . DIRECTORY_SEPARATOR . Application::getConfigItem('viewsFolder') . DIRECTORY_SEPARATOR . $layoutFileName;

        if (!file_exists($layoutFilePath)) {
            throw new \Exception('layout file could not be found');
        }

        $layoutContent = file_get_contents($layoutFilePath);
        
        $this->twigLoaderArray = new \Twig_Loader_Array([
            'layout.html'  => $layoutContent,
            'view.html'    => $viewContent,
        ]);

        $loader = new \Twig_Loader_Chain([
            $this->twigLoaderFileSystem,
            $this->twigLoaderArray,
        ]);
        
        $twig = new \Twig_Environment($loader);

        $twig->registerUndefinedFunctionCallback(function($name) {
            if (function_exists($name)) {
                return new \Twig_SimpleFunction($name, function() use($name) {
                    return call_user_func_array($name, func_get_args());
                });
            return false;
            }
        });

        echo $twig->render('view.html', $options);

        return;

    }

}