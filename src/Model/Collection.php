<?php
namespace EC\Model;
use Exception;
use EC\Helpers\Logger;
use EC\Traits\traitGetSet;

/**
 * Classes cColecoes (para tabelas do BD) extendem Collection
 *
 * @package		EC/EC
 * @author		João Mário Nedeff Menegaz
 */
class Collection{

	/**
	 * Parametros da paginacao
	 * page, lastPage, limit, offset, rowCount, foundRows, slang
	 * @var array
	 */
	private $data = [
		'page' => '',
		'lastPage' => '',
		'limit' => '',
		'offset' => '',
		'rowCount' => '',
		'foundRows' => '',
		'slang' => ''
	];

	/**
	 * Colecao de objetos retornada do DB
 	 * @var array
	 */
	private $collection;

	private $logger;

	/**
	 * traitGetSet
	 */
	use traitGetSet;

	/**
	 * Verifica se table e classe dos objetos foram definidas na classe/coleção filha
	 * dies with Exception se não
	 */
	public function __construct(){
		$this->logger = Logger::getLogger();

		if(!isset(static::$table))
		 	die("Table não definida na classe " . static::Class);

		if(!isset(static::$objectClass))
		 	die("Classe dos objetos não definida na classe " . static::Class);
	}

	/**
	 * Seta array de objetos da colecao
	 * @param array $collection
	 */
	protected function set_collection(array $collection){
		$this->collection = $collection;
	}

	/**
	 * Retorna array de objetos da colecao
	 * @return array
	 */
	public function get_collection(){
		return $this->collection;
	}

	/**
	 * Retorna rowCount
	 * @return int
	 */
	public function count(){
		return $this->data['rowCount'];
	}

	/**
	 * Retorna numero total de linhas encontradas pelo DB
	 * @return int|false
	 */
	private function foundRows(){
		$sql = "SELECT FOUND_ROWS() AS FOUND_ROWS";
		$row = Connection::fetchArray($sql);
		if(is_array($row)){
			$no_rows = (int)$row[0]['FOUND_ROWS'];
			return $no_rows;
		}

		return false;
	}

	/**
	 * Setter foundRows
	 * @param int $foundRows
	 */
	protected function set_foundRows(int $foundRows){
		$this->data['foundRows'] = $foundRows;
	}

	/**
	 * Retorna numero total de linhas encontradas pelo DB
	 * @return int
	 */
	public function get_foundRows(){
		return $this->data['foundRows'];
	}

	/**
	 * Seta pagina atual
	 * @param int $page
	 */
	public function set_page(int $page){
		$this->data['page'] = $page;
	}

	/**
	 * Retorna pagina atual
	 * @return int $page
	 */
	public function get_page(){
		return $this->data['page'];
	}

	/**
	 * Seta ultima pagina da colecao
	 */
	protected function set_lastPage(){
		$this->data['lastPage'] = (int) ceil($this->foundRows / $this->limit);
	}

	/**
	 * Retorna ultima pagina da colecao
	 * @return int $lastPage
	 */
	public function get_lastPage(){
		if(empty($this->data['lastPage']))
			$this->set_lastPage();

		return $this->data['lastPage'];
	}

	/**
	 * Seta limit: numero de linhas por pagina
	 * @param int $limit
	 */
	protected function set_limit(int $limit){
		$this->data['limit'] = $limit;
	}

	/**
	 * Retorna limit: numero de linhas por pagina
	 * @return int $limit
	 */
	public function get_limit(){
		return $this->data['limit'];
	}

	/**
	 * Seta offset: comecar no 1o. registro da pagina atual
	 */
	protected function set_offset(){
		$this->data['offset'] = ($this->page - 1) * $this->limit;
	}

	/**
	 * Retorna offset: comecar no 1o. registro da pagina atual
	 * @return int
	 */
	public function get_offset(){
		if(empty($this->data['offset']))
			$this->set_offset();

		return $this->data['offset'];
	}

	/**
	 * Seta rowCount: Quantidade de linhas na pagina atual
	 * @param int $rowCount
	 */
	public function set_rowCount(int $rowCount){
		$this->data['rowCount'] = $rowCount;
	}

	/**
	 * Retorna rowCount: Quantidade de linhas na pagina atual
	 * @return int $rowCount
	 */
	public function get_rowCount(){
		return $this->data['rowCount'];
	}

	/**
	 * Seta slang para compor os links da paginação
	 * @param string $slang
	 */
	public function set_slang(string $slang){
		$this->data['slang'] = $slang;
	}

	/**
	 * Retorna slang para compor os links da paginação
	 * @return string
	 */
	public function get_slang(){
		return $this->data['slang'];
	}

	/**
	 * Retorna todos registros da colecao, com ordenacao opcional
	 * @static
	 * @param int|null $orderBy
	 * @return array|false
	 */
	public static function all_simple(int $orderBy= NULL){
		$values = array();
		$sql = "SELECT * FROM " . static::$table;

		if ($orderBy){
			 $sql .= ' ORDER BY ?';
			 $values[] = $orderBy;
		}

		$result = Connection::fetchObjects($sql, static::$objectClass, $values);

		if(is_array($result))
			return $result[0];

		return false;
	}

	/**
	 * Armazena em collection todos os objetos da tabela, com paginação opcional
	 * @param int|null $orderBy
	 * @param int|null $page
	 * @param int|null $limit
	 * @return bool
	 */
	public function all(int $orderBy= NULL, int $page = NULL, int $limit = NULL, $dir = NULL){
		$values = array();
		$sql = "SELECT * FROM " . static::$table;

		if ($orderBy){
			$dir = (is_null($dir)) ? NULL : $dir;
			 $sql .= ' ORDER BY ? ' . $dir;
			 $values[] = $orderBy;
		}

		if($page AND $limit){
			$this->page = $page;
			$this->limit = $limit;
			$r = $this->paginate($sql, $page, $limit, $values);

			return $r;
		}else{
			$result = Connection::fetchObjects($sql, static::$objectClass, $values);

			if(is_array($result)){
				$this->set_collection($result[0]);
				$this->set_rowCount($result[1]);
				return true;
			}

			return false;
		}
	}

	/**
	 * Pagina objetos do DB em collection com select
	 * @param string $sql
	 * @param int $page
	 * @param int $limit
	 * @param array|null $values
	 * @return bool
	 */
	public function paginate($sql, $page, $limit, array $values = NULL){
		$sql = preg_replace('/SELECT /', 'SELECT SQL_CALC_FOUND_ROWS ', $sql, 1);
		$sql .= " LIMIT ? OFFSET ?";

		$this->limit = $limit;
		$this->page = $page;

		$values[] = $this->limit;
		$values[] = $this->offset;

		$result = Connection::fetchObjects($sql, static::$objectClass, $values);
        
		if(is_array($result)){
            
			$this->set_collection($result[0]);
			$this->set_rowCount($result[1]);
			$this->set_foundRows($this->foundRows());
            
			return true;
		}

		return false;
	}

	/**
	 * Pagina array do DB em collection, com select customizado
	 * @param string $sql
	 * @param int $page
	 * @param int $limit
	 * @param array|null $values
	 * @return bool
	 */
	public function paginateAsArray($sql, $page, $limit, array $values = NULL, $slang = NULL){
		if(!is_null($slang))
			$this->data['slang'] = $slang;

		$sql = preg_replace('/SELECT /', 'SELECT SQL_CALC_FOUND_ROWS ', $sql, 1);
		$sql .= " LIMIT ? OFFSET ?;";

		$this->limit = (int)$limit;
		$this->page = (int)$page;

		$values[] = $this->limit;
		$values[] = $this->offset;

		$result = Connection::fetchArray($sql, $values);

		if(!$result){
			$this->data['rowCount'] = 0;
			$this->data['foundRows'] = 0;

			//Debug
			$this->logger->debug(static::class . " coleção não encontrada", ['sql' => $sql, 'values' => $values]);

			return false;
		}

		if(is_array($result)){
			if($result[1] > 1)
				$this->set_collection($result[0]);
			else
				$this->set_collection([$result[0]]);
			$this->set_rowCount($result[1]);
			$this->set_foundRows($this->foundRows());

			//Debug
			$this->logger->debug(static::class . " coleção encontrada",
				[
					'rowCount' => $result[1],
					'foundRows' => $this->foundRows,
					'sql' => $sql,
					'values' => $values
				]
			);

			return true;
		}
	}

	/**
	 * Retorna primeira página
	 * @return int
	 */
	function first(){
		return 1;
	}

	/**
	 * Retorna slang da primeira página
	 * @return string
	 */
	public function firstSlang(){
		$slang = str_replace('|', 1, $this->slang);

		return $slang;
	}

	/**
	 * Retorna última página
	 * @return int
	 */
	public function last(){
		return $this->get_lastPage();
	}

	/**
	 * Retorna slang da última página
	 * @return string
	 */
	public function lastSlang(){
		$slang = str_replace('|', $this->lastPage, $this->slang);

		return $slang;
	}

	/**
	 * Retorna próxima [n] página
	 * @param int|null $n
	 * @return int
	 */
	public function next(int $n = NULL){
		$n = (is_null($n)) ? 1 : $n;
		$result = $this->page + $n;

		$p = ($result <= $this->lastPage) ? $result : $this->lastPage;

		return $p;
	}

	/**
	 * Retorna próxima [n] slang
	 * @param int|null $n
	 * @return string
	 */
	public function nextSlang(int $n = NULL){
		$n = (is_null($n)) ? 1 : $n;
		$result = $this->page + $n;

		$p = ($result <= $this->lastPage) ? $result : $this->lastPage;

		$slang = str_replace('|', $p, $this->slang);

		return $slang;
	}

	/**
	 * Retorna [n] páginas anterior
	 * @param int|null $n
	 * @return int
	 */
	public function previous(int $n = NULL){
		$n = (is_null($n)) ? 1 : $n;
		$result = $this->page - $n;

		$p = ($result >= 1) ? $result : 1;

		return $p;
	}

	/**
	 * Retorna [n] slang anterior
	 * @param int|null $n
	 * @return string
	 */
	public function previousSlang(int $n = NULL){
		$n = (is_null($n)) ? 1 : $n;
		$result = $this->page - $n;

		$p = ($result >= 1) ? $result : 1;

		$slang = str_replace('|', $p, $this->slang);

		return $slang;
	}
}
