<?php
namespace EC\Auth\Models;
use EC\Model\Model;

class Permission extends Model{
    protected static $table = 'permissions';

    protected $data = [
        'id' => NULL,
        'title' => NULL,
        'slang' => NULL,
        'description' => NULL,
        'created_at' => NULL,
        'updated_at' => NULL
    ];

}