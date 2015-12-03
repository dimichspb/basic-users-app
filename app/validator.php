<?php

namespace App;

class Validator {

    public static function required($value, $param) {
        if ($param) {
            if (!empty($param)) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return TRUE;
        }
    }

    public static function minsize($value, $param) {
        return ($param > 0 && mb_strlen($value) >= $param);
    }  

    public static function unique($model, $field, $value, $param) {
        if ($param) {
            $rows = $model->findByField($field, $value);
            if (is_array($rows) && sizeof($rows) > 0) {
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            return TRUE;
        }
    }

    public static function filter($value, $param) {
        return filter_var($value, $param);
    }

    public static function mask($value, $param) {
        return preg_match($param, $value);
    }

}