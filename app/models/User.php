<?php

namespace App\Models;

use App\Application;
use App\Model;

class User extends Model {

    public function tableName() {
        return 'users';
    }

    public function fields() {
        return [
            'user_id' => [],
            'name' => [
                'required' => TRUE,
                'minsize'  => 3,
            ],
            'email' => [
                'required' => TRUE,
                'unique'   => TRUE,
                'filter'   => FILTER_VALIDATE_EMAIL,
            ],
            'password' => [
                'required' => TRUE,
                'minsize'  => 6,
                'mask'     => '#^(?=(?:.*\d){2,})#',
                'hash'     => TRUE,
            ],
            'country' => [
                'required' => TRUE,
            ],
            'timezone' => [
                'required' => TRUE,
            ],
            'salt' => [
                'required' => TRUE,
            ],
        ];
    }
}