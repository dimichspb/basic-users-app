<?php

namespace App;

class Request {

    private $get  = [];
    private $post = [];
    
    public function __construct() {
        $this->parseGet();
        $this->parsePost();
    }

    private function parseGet() {
        if (sizeof($_GET) > 0) {
            foreach ($_GET as $index => $value) {
                $this->get[$index] = Helper::filterInput($value);
            }
        }
        return;
    }

    private function parsePost() {
        if (sizeof($_POST) > 0) {
            foreach ($_POST as $index => $value) {
                $this->post[$index] = Helper::filterInput($value);
            }
        }
        return;

    }

    public function get($index = NULL) {
        if (!empty($index) && array_key_exists($index, $this->get)) {
            return $this->get[$index];
        }
        return $this->get;
    }

    public function post($index = NULL) {
        if (!empty($index) && array_key_exists($index, $this->post)) {
            return $this->post[$index];
        }
        return $this->post;
    }
}