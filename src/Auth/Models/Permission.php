<?php
namespace EC\Auth\Models;
use EC\Model\Model;

class Permission extends Model{
	protected static $table = 'permissions';

	protected $data = [
		'id' => '',
		'title' => '',
		'slang' => '',
		'description' => '',
		'created_at' => '',
		'updated_at' => ''
	];

}