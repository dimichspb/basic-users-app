<?php

namespace App;

use App\Application;

class Language {

    const LANGUAGES_FOLDER = 'languages';

    public $langString;
    public $vocabulary = [];

    public function __construct($langString = null){
        $this->langString = isset($langString)? $langString: Application::getConfigItem('defaultLanguage');
        try {
            $this->loadLanguageFile();
        } catch (\Exception $e) {
            echo 'Error loading language file: ', $e->getMessage();
        }
        return;
    }

    private function loadLanguageFile() {
        $languageFilePath = Application::getConfigPath() . DIRECTORY_SEPARATOR . self::LANGUAGES_FOLDER . DIRECTORY_SEPARATOR . $this->langString . '.php';

        if (!file_exists($languageFilePath)) {
            throw new \Exception($languageFilePath . ' file could not be found');
        }

        $filePathVocabulary = require($languageFilePath);

        if (!is_array($filePathVocabulary)) {
            throw new \Exception('language aliases config file should return an array');
        }

        $this->vocabulary = $filePathVocabulary;
        return;
    }

    public function getText($alias) {
        if (is_string($alias) && array_key_exists($this->$vocabulary, $alias)) {
            return $this->$vocabulary[$alias];
        }
        return '';
    }
}