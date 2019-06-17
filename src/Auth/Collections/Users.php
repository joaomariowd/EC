<?php
namespace EC\Auth\Collections;
use EC\Auth\Models\Role;
use EC\Auth\Models\User;
use EC\Model\Connection;
use EC\Model\Collection;

/**
 * Users Collection class
 * 
 * @package		EC/EC
 * @author		João Mário Nedeff Menegaz
 */
class Users extends Collection {
    /**
     * DB table name
     * @var string
     */
    protected static $table = 'users';

    /**
     * This class returns an array of User objects.
     */
    protected static $objectClass = User::class;

    /**
     *  Returns an array of users by Role, and by page.
     * @param Role $role 
     * @param int $page 
     */
    public function whereRole(Role $role, $page, $limit){
        $sql = "SELECT u.* FROM users u INNER JOIN role_user ru ON u.id = ru.user_id INNER JOIN roles r ON ru.role_id = r.id WHERE r.id = ? ORDER BY u.nickname";
        
        $this->paginate($sql, $page, $limit, [$role->id]);
    }
}