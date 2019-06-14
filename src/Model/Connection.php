<?php
/**
 * Conn class
 */
namespace EC\Model;
use PDO;
use Exception;
use PDOException;
use EC\Helpers\Config;
use EC\Helpers\Log;

/**
 * Connects to DB and offers Transaction and Query functions
 *
 * @package		EC/EC
 * @author		João Mário Nedeff Menegaz
 */
class Connection{
	/**
	 * @var PDO object
	 */
	protected static $conn;

	/**
	 * Parameters for PHP/PDO data exchange
	 *
	 * @var Array
	 */
	protected static $PDOParams = [
		'integer' => PDO::PARAM_INT,
		'float' => PDO::PARAM_STR,
		'double' => PDO::PARAM_STR,
		'string' => PDO::PARAM_STR,
		'boolean' => PDO::PARAM_BOOL,
		'bool' => PDO::PARAM_BOOL,
		'NULL' => PDO::PARAM_NULL
	];

	/**
	 * If $conn is empty, sets PDO instance
	 * @param Config $config
	 * @return true or dies with error
	 */
	public static function setConn(Config $config){
		if(!isset(self::$conn)){
			$host = $config->host;
			$charset = $config->charset;
			$dbname = $config->dbname;
			$user = $config->user;
			$password = $config->password;

			$dsn = 'mysql:dbname=' . $dbname . ';host=' . $host . ';charset=' . $charset . '';

			try{
				self::$conn = new PDO($dsn, $user, $password, [
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]);
				self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
				self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}catch (PDOException $e){
				if($config->app_mode == 'production' AND $config->logging==true)
					Log::Debug($e->getMessage());
				else if($config->app_mode == 'development' OR $config->app_mode == 'staging')
					die('Erro: ' . $e->getMessage());

				echo 'Houve um erro. Por favor retorne mais tarde.';
			}
		}

		return true;
	}

	/**
	 * Begin PDO Transaction
	 * @return bool
	 */
	public static function beginTransaction(){
		return self::$conn->beginTransaction();
	}

	/**
	 * inTransaction?
	 * @return bool
	 */
	public static function inTransaction(){
		return self::$conn->inTransaction();
	}

	/**
	 * Commits
	 * @return bool
	 */
	public static function commit(){
		return self::$conn->commit();
	}

	/**
	 * Rolls back
	 * @return bool
	 */
	public static function rollback(){
		return self::$conn->rollback();
	}

	/**
	 * Encera conn
	 */
	public function clear(){
		self::$conn = NULL;
	}

	/**
	 * Retorna um objeto do BD
	 * @param string $sql
	 * @param string $class
	 * @param array|null $values
	 * @return array|false
	 * [object|rowCount]|false
	 */
	public static function fetchObject($sql, $class, array $values = NULL){
		if(!class_exists($class))
			throw new Exception("Classe $class não encontrada!");

		$sth = self::$conn->prepare($sql);

		//Bind Parameters
		if(!is_null($values)){
			$i = 1;
			foreach($values as $value){
				$type = gettype($value);
				$param = self::$PDOParams[$type];
				$value = ($type == 'float' OR $type == 'double') ? (string) $value : $value;
				$sth->bindValue($i, $value, $param);
				$i++;
			}
		}

		$sth->execute();
		$rowCount = $sth->rowCount();

		if($rowCount == 1){
			$sth->setFetchMode(PDO::FETCH_CLASS, $class);
			$result = $sth->fetch();

			return [$result, $rowCount];

		}

		return false;
	}

	/**
	 * Retorna um array de objetos do BD
	 * @param string $sql
	 * @param string $class
	 * @param array|null $values
	 * @return array|false
	 * [array of Objs|rowCount]
	 */
	public static function fetchObjects($sql, $class, array $values = NULL){
		if(!class_exists($class))
			throw new Exception("Classe $class não encontrada!");

		$sth = self::$conn->prepare($sql);

		//Bind Parameters
		if(!is_null($values)){
			$i = 1;
			foreach($values as $value){
				$type = gettype($value);
				$param = self::$PDOParams[$type];
				$value = ($type == 'float' OR $type == 'double') ? (string) $value : $value;
				$sth->bindValue($i, $value, $param);
				$i++;
			}
		}

		$sth->execute();
		$rowCount = $sth->rowCount();

		if($rowCount >= 1){
			$sth->setFetchMode(PDO::FETCH_CLASS, $class);
			$result = $sth->fetchAll();

			return [$result, $rowCount];
		}

		return false;
	}

	/**
	 * Fetches an array from DB
	 * @param string $sql
	 * @param array|null $values
	 * @return array|bool
	 * [array,1] ou [array, rowCount]
	 */
	public static function fetchArray($sql, array $values = NULL){
		$sth = self::$conn->prepare($sql);

		//Bind Parameters
		if(!is_null($values)){
			$i = 1;
			foreach($values as $value){
				$type = gettype($value);
				$param = self::$PDOParams[$type];
				$value = ($type == 'float' OR $type == 'double') ? (string) $value : $value;
				$sth->bindValue($i, $value, $param);
				$i++;
			}
		}

		$sth->execute();
		$count = $sth->rowCount();

		if ($count == 0) return false;

		$sth->setFetchMode(PDO::FETCH_ASSOC);

		if($count == 1)
			return [$sth->fetch(), 1];
		else
			return [$sth->fetchAll(), $count];
	}

	/**
	 * Execute
	 * Data Manipulation on MySQL
	 *
	 * UPDATE role_user SET id = :id WHERE role_id = :role_id AND user_id = :user_id;
	 *
	 * ['id'=>$i, 'role_id' => $ru['role_id'], 'user_id'=> $ru['user_id']
	 *
	 * @param string $sql
	 * @param array $data
	 * @return array
	 * [lastInsertId|rowCount]
	 */
	public static function Execute($sql, array $data){
		$sth = self::$conn->prepare($sql);

		foreach($data as $key=>$value){
			$type = gettype($value);

			$param = self::$PDOParams[$type];
			$value = ($type == 'float' OR $type == 'double') ? (string) str_replace(',', '.', $value) : $value;

			$key = ":". $key;
			$sth->bindValue($key, $value, $param);
		}

		$sth->execute();
		$rowCount = $sth->rowCount();
		$lastInsertId = (int) Connection::$conn->lastInsertId();

		return [$lastInsertId, $rowCount];
	}
}
