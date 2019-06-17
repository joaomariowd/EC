<?php
namespace EC\Auth\Models;
use EC\Model\Connection;
use EC\Model\Model;


/**
 * Role class
 * 
 * @package     EC/EC
 * @author      João Mário Nedeff Menegaz
 */
class Role extends Model {
    
    /**
     * DB table name
     * @var string
     */
    protected static $table = 'roles';

    /**
     * Object properties
     * DB fields on table roles
     */
    protected $data = [
        'id' => NULL,
        'title' => NULL,
        'slang' => NULL,
        'description' => NULL,
        'created_at' => NULL,
        'updated_at' => NULL
    ];

    /**
     * Array of Permission objects (permissions) for each Role
     */
    protected $permissions;

    /**
     * Array of User objects (users) for each Role
     */
    protected $users;

    /**
     * Users per role
     */
    protected $userCount;
    
    /**
     * Fetch this Role permissions from DB and sets it to $permissions var
     */
    public function set_permissions(){
        $sql = "SELECT p.* FROM permissions p INNER JOIN permission_role pr ON p.id = pr.permission_id INNER JOIN roles r ON pr.role_id = r.id WHERE r.id = ? ORDER BY p.title;";
        $result = Connection::fetchObjects($sql, Permission::class, [$this->id]);

        if ($result[1] > 0)
            $this->permissions = $result[0];
    }

    /**
     * @return Array Permission set for this Role
     */
    public function get_permissions(){
        if(is_null($this->permissions))
            $this->set_permissions();

        return $this->permissions;
    }

    /**
     * @return Boolean Wheter Role has the permission with this slang
     */
    public function hasPermission($slang){
        $table = self::$table;
        $sql = "SELECT count(*) AS `Permission` FROM roles r INNER JOIN permission_role pr ON r.id = pr.role_id INNER JOIN permissions p ON pr.permission_id = p.id WHERE p.slang = ? AND r.id = ?";

        $values = [$slang, $this->id];
        $result = Connection::fetchArray($sql, $values);
        $result = (int) $result[0]['Permission'];

        if($result != 1)
            return false;

        return true;
    }

    /**
     * Fetch users from DB and set it to the $users var
     */
    public function set_users(){
        $sql = "SELECT u.* FROM users u INNER JOIN role_user ru ON u.id = ru.user_id INNER JOIN roles r ON ru.role_id = r.id WHERE r.id = ? ORDER BY u.nickname;";
        $result = Connection::fetchObjects($sql, User::class, [$this->id]);

        if ($result[1] > 0)
            $this->users = $result[0];
    }

    /**
     * @return Array of User objects
     */
    public function get_users(){
        if(is_null($this->users))
            $this->set_users();

        return $this->users;
    }
    
    /**
     * Queries DB on users count and set it to $userCount var
     */
    protected function set_userCount(){
        $sql = "SELECT count(u.id) AS `count` FROM users u INNER JOIN role_user ru ON u.id = ru.user_id INNER JOIN roles r ON ru.role_id = r.id WHERE r.id = ?;";
        $result = Connection::fetchArray($sql, [$this->id]);
        
        $this->userCount = $result[0]['count'];
    }

    /**
     * @return int User count
     */
    public function get_userCount(){
        if (is_null($this->userCount))
            $this->set_userCount();

        return $this->userCount;
    }

    /* FINDERS */

    /**
     * Users Roles
     * @param User $user 
     * @return Array
     */
    public static function findByUser(User $user){
        $sql = "SELECT r.* FROM roles r INNER JOIN role_user ru ON r.id = ru.role_id INNER JOIN users u ON ru.user_id = u.id WHERE  u.id = ? ORDER BY r.id;";
        $result = Connection::fetchObjects($sql, Role::class, [$user->id]);
        
        return $result[0];
    }

    /**
     * @param string $slang 
     * @return Role
     */
    public static function findBySlang(string $slang){
        $sql = "SELECT * FROM roles WHERE `slang` = ?;";
        $result = Connection::fetchObject($sql, Role::class, [$slang]);
        return $result[0];
    }
}