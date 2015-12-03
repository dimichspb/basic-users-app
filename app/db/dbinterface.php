<?php

namespace App\Db;


interface DbInterface {

    public function update(array $options);
    public function select(array $options);
    public function selectOne(array $options);
    public function selectLike(array $options);
    public function insert(array $options);
    public function query($query);

}