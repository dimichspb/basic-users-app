<?php

namespace App;

use App\Application;
use App\ModelInterface;
use App\Validator;
use App\Helper;

abstract class Model implements ModelInterface {

    private $fields = [];

    public function __construct() {
        if (!$this->checkTableExists()) {
            echo (' there is no table ' . $this->tableName() . ' in DB');
        }
    }

    private function checkTableExists() {
        $tableName = $this->tableName();

        $query = "SHOW TABLES LIKE '" . $tableName . "'";
        $rows = Application::DB()->query($query);
        return (is_array($rows) && sizeof($rows) > 0);        
    }

    public function load(array $fields = array()) {
        if (sizeof($fields) > 0) {
            $modelFields = $this->fields();
            foreach ($fields as $index => $value) {
                if (array_key_exists($index, $modelFields)) {
                    $this->$index = $value;
                }
            }
        }
    }

    public function validate() {
        $validationResult = TRUE;

        $modelFields = $this->fields();
        foreach ($modelFields as $fieldName => $fieldParams) {
            $fieldValue = $this->$fieldName;
            if (!empty($fieldValue)) {
                foreach ($fieldParams as $paramName => $paramValue) {
                    $validationRequireResult = TRUE;
                    $validationUniqueResult  = TRUE;
                    $validationMinsizeResult = TRUE;
                    $validationFilterResult  = TRUE;
                    $validationMaskResult    = TRUE;
                    switch (strtolower($paramName)) {
                        case 'required':
                            $validationRequireResult = Validator::required($this->$fieldName, $paramValue);
                            break;
                        case 'unique':
                            $validationUniqueResult = Validator::unique($this, $fieldName, $this->$fieldName, $paramValue);
                            break;
                        case 'minsize':
                            $validationMinsizeResult = Validator::minsize($this->$fieldName, $paramValue);
                            break;
                        case 'filter':
                            $validationFilterResult = Validator::filter($this->$fieldName, $paramValue);
                            break;
                        case 'mask':
                            $validationMaskResult = Validator::mask($this->$fieldName, $paramValue);
                            break;
                        case 'hash':
                            break;
                        default:
                            $validationResult = FALSE;
                    }
                    $validationResult = ($validationResult        &&
                                         $validationRequireResult && 
                                         $validationUniqueResult  &&
                                         $validationMinsizeResult &&
                                         $validationFilterResult  &&
                                         $validationMaskResult);
                 }
            }
        }
        return $validationResult;
    }

    public function __set($field, $value) {
        $this->fields[$field] = $value;
    }        

    public function __get($field) {
        return isset($this->fields[$field])? $this->fields[$field]: NULL;
    }

    public function save() {
        $tableName = $this->tableName();
        $modelFields = $this->fields();
        $loadedFields = $this->fields;

        foreach ($loadedFields as $field => $value) {
            if (array_key_exists($field, $modelFields)) {
                if (isset($modelFields[$field]['hash']) && $modelFields[$field]['hash'] == TRUE) {
                    $value = Helper::hashIt($value);
                }
                $fields[$field] = $value;
            }
        }

        if (is_array($fields) && sizeof($fields) > 0) {
            $insert_id = Application::DB()->insert([
                'table'  => $tableName,
                'fields' => $fields,
            ]);
            return $insert_id;
        }
        return false;
    }

    public function findByField($field, $value) {
        $tableName = $this->tableName();
        $fields = array_keys($this->fields());
        $where = [$field => $value];

        $rows = Application::DB()->select([
            'table'  => $tableName,
            'fields' => $fields,
            'where'  => $where,
        ]);
        if (is_array($rows) && sizeof($rows) > 0) {
            return $rows;
        } 
        return FALSE;
    }

    public function findByFieldsLike(array $fields = []) {
        if (is_array($fields) && sizeof($fields) > 0) {

            $tableName = $this->tableName();
            $fieldsSel = array_keys($this->fields());
            $whereLike = $fields;

            $rows = Application::DB()->selectLike([
                'table'  => $tableName,
                'fields' => $fieldsSel,
                'where'  => $whereLike,
            ]);
            if (is_array($rows) && sizeof($rows) > 0) {
                return $rows;
            } 
        }
        return FALSE;
    }


}
