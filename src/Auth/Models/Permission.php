<?php
namespace EC\Auth\Models;
use EC\Model\Model;

/**
 * Permission class
 * Holds permission info.
 * Each Role has an array of Permission objects.
 * 
 * @package		EC/EC
 * @author		João Mário Nedeff Menegaz
 */
class Permission extends Model {
    
    /**
     * DB table name
     * @var string
     */
    protected static $table = 'permissions';

    /**
     * Object properties
     * DB fields on table permissions
     */
    protected $data = [
        'id' => NULL,
        'title' => NULL,
        'slang' => NULL,
        'description' => NULL,
        'created_at' => NULL,
        'updated_at' => NULL
    ];

}