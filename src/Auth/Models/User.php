<?php
namespace EC\Auth\Models;
use EC\Auth\Interfaces\User as iUser;
use EC\Exceptions\ModelNotFoundException;
use EC\Helpers\Formatter;
use EC\Model\Connection;
use EC\Model\Model;
use EC\Site\Models\Log;

/**
 * User class
 * Holds user info and functionality for ACL.
 * 
 * @package     EC/EC
 * @author      João Mário Nedeff Menegaz
 */
class User extends Model implements iUser {
    
    /**
     * DB table name
     * @var string
     */
    protected static $table = 'users';

    /**
     * Object properties
     * DB fields on table users
     */
    protected $data = [
        'id' => NULL,
        'nickname' => NULL,
        'email' => NULL,
        'hash' => NULL,
        'remember_token' => NULL,
        'active' => NULL,
        'created_at' => NULL,
        'updated_at' => NULL
    ];

    /**
     * Validations array
     * 'field' => 'Validation|AnotherValidation:parameter|OtherValidation'
     */
    protected $validations = [
        'nickname' => 'Required|Min:3|Max:15',
        'email' => 'Required|Email|Unique'
    ];

    /**
     * User's text password
     */
    protected $password;

    /**
     * Users roles
     */
    protected $roles;
    
    /**
     * Encrypts text passwords and sets hash to Property / DB Field hash
     */
    public function set_password(string $password){
        $this->data['hash'] = password_hash($password, PASSWORD_DEFAULT);
        $this->password = $password;
    }
    
    /**
     * @return String user password
     */
    public function get_password () {
        return $this->password;
    }

    /**
     * Verify if both passwords are equal and greater than 4 chars and set it
     */
    public function new_password($pass1, $pass2){
        
        if($pass1 == $pass2 AND strlen($pass1) >= 4){
            $this->set_password($pass1);
            return true;
        }

        return false;
    }
    
    /**
     * To reset password, this function erases hash and sets a UUID Unique code on remember_token.
     */
    public function password_reset(){
        $sql = "SELECT UUID() AS UUID;";
        $result = Connection::fetchArray($sql, []);
        $result = $result[0];

        $this->remember_token = $result['UUID'];
        $this->hash = -1;
        $active = $this->active;
        $this->active = -1;
        $r = $this->save();

        if($r == true) {
            /* Password reset log */
            Log::new ('user-password-reset', 'success', ['active' => $active], $this->id);
            return true;
        }

        return false;
    }

    /**
     * Verifies if user is active (active > 0)
     * Verifies if password match password_verify
     * Verifies is user is already logged in, must not be in order to authenticate
     * @return Boolean Authentication successful?
     */
    public function authenticate(string $password){
        
        if ($this->isActive() AND $this->isLoggedIn() == false AND password_verify($password, $this->hash)) {
            $sql = "SELECT * FROM `users_login_active` WHERE user_id = ?;";
            $ula = Connection::fetchArray($sql, [$this->id]);
            
            if ($ula == false)
                $sql = "INSERT INTO `users_login_active` (`user_id`, `active`, `created_at`, `updated_at`) VALUES (:user_id, 1, NOW(), NOW());";
            else
                $sql = "UPDATE `users_login_active` SET `active` = 1, `created_at` = NOW(), `updated_at` = NOW() WHERE `user_id` = :user_id;";

            $data = ['user_id' => $this->id];
            $r = Connection::Execute ($sql, $data);
            return true;
        }

        return false;
    }

    /**
     * Verifies if User is logged in, on users_login_active table
     * As we set PHP session to 2 hours, this function also checks if session time is greater
     * than 2hs. If so, allows login.
     * session.gc_maxlifetime 7200 sec
     */
    public function isLoggedIn() {
        $sql = "SELECT `active`, IF (TIMEDIFF(NOW(), created_at) <= '02:00:00', 'OK', 'EXPIRED') AS `session` FROM users_login_active WHERE user_id = ?;";
        $active = Connection::fetchArray($sql, [$this->id]);
        
        if ($active == false) 
            return false;
        elseif ($active[0]['active'] == 0 OR $active[0]['session'] == 'EXPIRED')
            return false;
        else
            return true;

    }

    /**
     * Wether or not User is Active and should use the system or not.
     */
    public function isActive() {
        return $this->active >= 1;
    }
    
    /**
     * Updates user_login_active table and logs off
     */
    public function logout() {

        //Change login status
        $sql = "UPDATE `users_login_active` SET `active` = 0, `updated_at` = NOW() WHERE `user_id` = :user_id;";
        $data = ['user_id' => $this->id];
        $r = Connection::Execute ($sql, $data);

        //Time diff
        $sql = "SELECT TIMEDIFF (updated_at, created_at) AS td FROM users_login_active WHERE user_id = ?;";
        $td = Connection::fetchArray($sql, [$this->id]);
        $td = $td[0]['td'];

        /* Log User Logged in Time */
        Log::new ('user-logged-in-time', 'success', ['Time' => $td], $this->id);
        
        return true;
    }

    /**
     * @return Boolean User has permission?
     */
    public function hasPermission(string $slang){
        $sql = "SELECT count(*) AS `Permission` FROM users u INNER JOIN role_user ru ON u.id = ru.user_id INNER JOIN roles r ON ru.role_id = r.id INNER JOIN permission_role pr ON r.id = pr.role_id INNER JOIN permissions p ON pr.permission_id = p.id WHERE u.id = ? AND p.slang = ?";

        $result = Connection::fetchArray($sql, [$this->id, $slang]);
        $result = (int)$result[0]['Permission'];

        if($result == 0)
            return false;

        return true;
    }

    /**
     * @return Boolean User has Role
     */
    public function hasRole(string $slang){
        $sql = "SELECT count(r.id) AS `Role` FROM users u INNER JOIN role_user ru ON u.id = ru.user_id INNER JOIN roles r ON r.id = ru.role_id WHERE u.id = ? AND r.slang = ?;";
        $result = Connection::fetchArray($sql, [$this->id, $slang]);

        $result = (int)$result[0]['Role'];

        if($result == 0)
            return false;

        return true;
    }
    
    /**
     * Adds a Role to the User, table role_user
     */
    public function addRole(Role $role){
        if ($this->hasRole($role->slang))
            return true;
        
        $sql = "INSERT INTO role_user (`role_id`, `user_id`, `created_at`, `updated_at`) VALUES (:role_id, :user_id, NOW(), NOW());";
        $data = ['role_id' => $role->id, 'user_id' => $this->id];
        $r = Connection::Execute ($sql, $data);

        if ($r[1] == 1)
            return true;

        return false;
    }
    
    /**
     * Search Logs for the Last Active Status
     */
    public function recoverLastActiveStatus() {
        $sql = "SELECT `description` FROM logs WHERE user_id = ? AND `slang` = 'user-password-reset' ORDER BY created_at DESC LIMIT 1;";
        $las = Connection::fetchArray ($sql, [$this->id]);

        if ($las[1] == 1) {
            $las = json_decode($las[0]['description']);
            return $las->active;
        }
        
        return 1;
    }

    /* FINDERS */

    /**
     * Find user by Email
     * @param string $email 
     * @return User
     */
    public static function findByEmail(string $email){
        $sql = "SELECT * FROM users WHERE `email` = ?";

        $values = [$email];
        $result = Connection::fetchObject($sql, User::class, $values);

        if(is_object($result[0]))
            return $result[0];

        return false;
    }
    
    /**
     * Find user by remember_token
     * @param string $remember_token 
     * @return User
     */
    public static function findByRememberToken(string $remember_token){
        $sql = "SELECT * FROM `users` WHERE `remember_token` = ?";

        $result = Connection::fetchObject($sql, User::class, [$remember_token]);

        if($result[1] == 1)
            return $result[0];

        if(isset(static::$exception))
            throw new static::$exception();
        else
            throw new ModelNotFoundException(static::class . " não encontrado!");
    }
}