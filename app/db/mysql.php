<?php

namespace App\Db;

use App\Db\DBInterface;

class Mysql implements DBInterface {

    const QUERY_TYPE_UPDATE = 'UPDATE';
    const QUERY_TYPE_SELECT = 'SELECT';
    const QUERY_TYPE_INSERT = 'INSERT';

    private $connection;

    public function __construct($config) {
        try {
            $this->setConnection($config);
        } catch (\Exception $e) {
            echo 'Error setting connection to DB: ', $e->getMessage();
        }
    }

    private function setConnection(array $config = []) {
        if (empty($config['host'])     || 
            empty($config['dbname'])   ||
            empty($config['username']) ||
            empty($config['password'])) {
            throw new \Exception('missing config params');
        }
        $this->connection = new \mysqli($config['host'], $config['username'], $config['password'], $config['dbname'], isset($config['port'])? $config['port']: null);
        if (!empty($config['charset'])) {
            @$this->connection->set_charset($config['charset']);
        }

        if ($this->connection->connect_error) {
            throw new \Exception('error connecting mysql db (' . $this->connection->connect_errno . ') '
                                  . $this->connection->connect_error);
        }
    }

    public function update(array $options) {

        if (!empty($options['table']) && !empty($options['fields']) && !empty($options['where'])) {

            try {
                $query = $this->buildQuery(self::QUERY_TYPE_UPDATE, $options['table'], $options['fields'], $options['where']);
                $queryResult = $this->connection->query($query);
                if ($this->connection->connect_error) {
                    throw new \Exception('(' . $this->connection->connect_errno . ') '
                      . $this->connection->connect_error);
                }
            } catch (\Exception $e) {
                echo 'Error updating data from DB: ', $e->getMessage();
            }
            return $this->connection->affected_rows;   
        }
        return false;
    }

    public function select(array $options, $limit = NULL, $like = FALSE) {

        if (!empty($options['table']) && !empty($options['fields']) && !empty($options['where'])) {
            
            try {
                $query = $this->buildQuery(self::QUERY_TYPE_SELECT, $options['table'], $options['fields'], $options['where'], $limit, $like);
                $queryResult = $this->connection->query($query);
                if ($this->connection->connect_error) {
                    throw new \Exception('(' . $this->connection->connect_errno . ') '
                      . $this->connection->connect_error);
                }
            } catch (\Exception $e) {
                echo 'Error selecting data from DB: ', $e->getMessage();
            }
            if ($queryResult) {
                if ($queryResult->num_rows == 1) {
                    return $queryResult->fetch_array(MYSQLI_ASSOC);
                } else {
                    return $queryResult->fetch_all(MYSQLI_ASSOC);
                }
            }
        }
        return false;
        
    }

    public function selectOne(array $options) {
        return $this->select($options, 1);
    }

    public function selectLike(array $options) {
        return $this->select($options,'', TRUE);
    }

    public function insert(array $options) {
        if (!empty($options['table']) && !empty($options['fields'])) {
            try {
                $query = $this->buildQuery(self::QUERY_TYPE_INSERT, $options['table'], $options['fields']);
                $queryResult = $this->connection->query($query);
                if ($this->connection->connect_error) {
                    throw new \Exception('(' . $this->connection->connect_errno . ') '
                      . $this->connection->connect_error);
                }
            } catch (\Exception $e) {
                echo 'Error inserting data from DB: ', $e->getMessage();
            }
            return $this->connection->insert_id;   
        }
        return false;
    }

    public function query($query) {
        if (!empty($query)) {
            try {
                $queryResult = $this->connection->query($query);
                if ($this->connection->connect_error) {
                    throw new \Exception('(' . $this->connection->connect_errno . ') '
                      . $this->connection->connect_error);
                }
            } catch (\Exception $e) {
                echo 'Error querying data from DB: ', $e->getMessage();
            }
            if ($queryResult) {
                return $queryResult->fetch_array(MYSQLI_ASSOC);   
            }
        }
    }

    private function buildQuery($queryType, $tableName, array $fields = [], array $where = [], $limit = NULL, $like = FALSE) {
        if (empty($tableName) || empty($queryType) || sizeof($fields) == 0 || ($queryType != self::QUERY_TYPE_INSERT && sizeof($where) == 0)) {
            throw new \Exception('all parameters should be not empty');
        }
        switch ($queryType) {
            case self::QUERY_TYPE_UPDATE:
                $fieldsString = $this->buildUpdateFieldsString($fields);
                $whereString = $this->buildUpdateWhereString($where, $like);
                $limitString = $this->buildUpdateLimitString($limit);                

                $queryString = 'UPDATE `'. $tableName . '` SET ' . $fieldsString . ' WHERE ' . $whereString . ';';
                break;
            case self::QUERY_TYPE_SELECT:
                $fieldsString = $this->buildSelectFieldsString($fields);
                $whereString = $this->buildSelectWhereString($where, $like);
                $limitString = $this->buildSelectLimitString($limit);                

                $queryString = 'SELECT ' . $fieldsString . ' FROM `' . $tableName . '` WHERE ' . $whereString . ' ' . $limitString . ';';
                break;
            case self::QUERY_TYPE_INSERT:
                $fieldsString = $this->buildInsertFieldsString($fields);
                $valuesString = $this->buildInsertFieldsString($fields, TRUE);

                $queryString = 'INSERT INTO `' . $tableName . '` (' . $fieldsString . ') VALUES (' . $valuesString . ');';
                break;
            default:
                throw new \Exception('unknown operation type ' . $queryType);
        }
        return $queryString;
    }

    private function buildSelectFieldsString(array $fields) {
        $fieldsString = '';
        $fieldsStringArray = [];

        if (sizeof($fields) > 0) {
            foreach ($fields as $index => $value) {
                $fieldsStringArray[] = '`' . $value . '`';
            }
            $fieldsString = implode(',', $fieldsStringArray);
        }
        return $fieldsString;
    }

    private function buildUpdateFieldsString(array $fields) {
        $fieldsString = '';
        $fieldsStringArray = [];

        if (sizeof($fields) > 0) {
            foreach ($fields as $index => $value) {
                $fieldsStringArray[] = '`' . $index . '` = \'' . $value . '\'';
            }
            $fieldsString = implode(',', $fieldsStringArray);
        }
        return $fieldsString;
    }

    private function buildInsertFieldsString(array $fields, $values = FALSE) {
        $fieldsString = '';
        $fieldsStringArray = [];

        if (sizeof($fields) > 0) {
            foreach ($fields as $index => $value) {
                $fieldsStringArray[] = ($values? '\'' . $value . '\'': '`' . $index . '`');
            }
            $fieldsString = implode(',', $fieldsStringArray);
        }
        return $fieldsString;
    }


    private function buildSelectWhereString(array $where, $like = FALSE) {
        $whereString = '';
        $whereStringArray = [];
        if (sizeof($where) > 0) {
            foreach ($where as $index => $value) {
                if ($like) {
                    $whereStringArray[] = '`' . $index . '` LIKE \'%' . $value . '%\'';
                } else {
                    $whereStringArray[] = '`' . $index . '` = \'' . $value . '\'';
                }
            }
            if ($like) {
                $whereString = ' (' . implode(' OR ', $whereStringArray) . ') ';
            } else {
                $whereString = implode(' AND ', $whereStringArray);
            }
        }        
        return $whereString;
    }

    private function buildUpdateWhereString(array $where, $like = FALSE) {
        return $this->buildSelectWhereString($where, $like);
    }

    private function buildSelectLimitString($limit) {
        $limitString = (is_numeric($limit) && !empty($limit))? ' LIMIT ' . $limit: '';
        return $limitString;
    }

    private function buildUpdateLimitString($limit) {
        return $this->buildSelectLimitString($limit);        
    }


}