<?php
namespace EC\Auth\Collections;
use EC\Auth\Models\Role;
use EC\Auth\Models\User;
use EC\Model\Connection;
use EC\Model\Collection;


/**
 * Collection para tabela users do DB
 * 
 * @package		EC/EC
 * @author		João Mário Nedeff Menegaz
 */
class Users extends Collection{
	/**
	 * Tabela do BD
	 * @var string
	 */
	protected static $table = 'users';

	/**
	 * Classe User dos objetos da coleção
	 */
	protected static $objectClass = User::class;

	/**
	 *  Array de Users do Role, paginados
	 * @param Role $role 
	 * @param int $page 
	 */
	public function whereRole(Role $role, $page, $limit){
		$sql = "SELECT u.* FROM users u INNER JOIN role_user ru ON u.id = ru.user_id INNER JOIN roles r ON ru.role_id = r.id WHERE r.id = ? ORDER BY u.nickname";
		$this->paginate($sql, $page, $limit, [$role->id]);
	}
}