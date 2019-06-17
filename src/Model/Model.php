<?php
namespace EC\Model;
use PDO;
use Exception;
use EC\Exceptions\InvalidModelException;
use EC\Exceptions\ModelNotFoundException;
use EC\Helpers\Logger;
use EC\Helpers\Validators;
use EC\Traits\traitGetSet;

/**
 * Objetos do BD extendem essa classe para obter funcionalidades AR
 *
 * @package		EC/EC
 * @author		João Mário Nedeff Menegaz
 */
class Model {
    
    /**
     * Armazena validações do modelo
     * @var array
     */
    protected $validations = array();
    
    /**
     * Armazena Erros de preenchimento
     * @var array
     */
    protected $errors = array();

    /**
     * Última ID inserida no BD
     * @var int
     */
    protected $lastInsertId;

    /**
     * Armazena número de linhas retornadas pelo BD
     * @var int
     */
    protected $rowCount;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Trait Getters e Setters
     */
    use traitGetSet;

    /**
     * Finds DB Object by ID
     * @param int $id
     * @return object|ModelNotFoundException
     * Retorna Objeto do DB ou Exception ModelNotFoundException
     */
    public static function find(int $id){
        $sql = "SELECT * FROM `" . static::$table . "` WHERE `id` = ?;";

        $class = static::class;

        $result = Connection::fetchObject($sql, $class, [$id]);

        //Log SQL AND DATA
        $logger = Logger::getLogger();
        $logger->debug('Model / find', ['id' => $id, 'sql' => $sql, 'class' => $class]);

        if(is_object($result[0]))
            return $result[0];

        if(isset(static::$exception))
            throw new static::$exception();
        else
            throw new ModelNotFoundException($class . " não encontrado!");
    }

    /**
     * Se for passado um array com itens ao construtor, ele carrega os mesmos nas propriedades
     * @param array|null $aItens
     */
    public function __construct(array $aItens = NULL){
        $this->logger = Logger::getLogger();

        if(is_array($aItens)){
            foreach($aItens as $key=>$value){
                $this->data[$key] = $value;
            }
        }
    }

    /**
     * toString
     * @return int
     */
    public function __toString(){
        return $this->data['id'];
    }
    
    /**
     * Getter ID
     * Caso esteja criando um objeto, ainda não tem ID, retorna lastInsertId
     * @return int
     */
    public function get_id(){
        if(!is_numeric ($this->data['id']) AND is_numeric ($this->lastInsertId)) {
            $this->data['id'] = $this->lastInsertId;
            $this->lastInsertId = NULL;
        }

        return $this->data['id'];
    }

    /**
     * Cria ou atualiza objeto no DB
     * @return bool
     */
    public function save(){
        if ( $this->validate() ){
            //UPDATE
            if( is_int($this->id) ){
                $saved = $this->update();

                //Logger
                $this->logger->debug('SAVE / update', ['saved' => $saved]);

                if($saved === 0)
                    return $saved;

                $this->reload();
                return $saved;
            }
            //CREATE
            else{
                $saved = $this->create();

                //Logger
                $this->logger->debug('SAVE / create', ['saved' => $saved]);

                if(!$saved)
                    throw new Exception("Error Processing Save", 1);

                return $saved;
            }
        }

        //Logger
        $this->logger->debug('SAVE / invalid model', ['saved' => false]);
    }

    /**
     * Cria objeto no DB
     * @return bool
     */
    protected function create(){
        $count = count($this->data) - 1;
        $i = 0;
        $iniciou_sql = false;

        foreach ($this->data as $key => $value) {

            //Não tem campo ID no INSERT
            if($key == 'id'){
                $i++;
                continue;
            }

            //created_at e updated_at
            if ($key == 'created_at' OR $key == 'updated_at') {
                $value = 'NOW()';
            //Valores do array DATA
            }else {
                $data[$key] = $this->testValue($value);

                $value = ':' . $key;
            }

            if(!is_null($value)){
                //Início SQL
                if($iniciou_sql == false){
                    $fields = "INSERT INTO `" .  static::$table . "` (`" . $key . "`, ";
                    $values = $value . ', ';
                    $iniciou_sql = true;
                //Demais campos
                }else if ($i >= 1 AND $i < $count){
                    $fields .= '`' . $key . '`, ';
                    $values .= $value . ', ';
                //Último campo
                }else if ($i >= 1){
                    $fields .= '`' . $key . '`)';
                    $values .= $value . ')';
                }

                $i++;
            }
        }
        $sql = $fields . ' ' . 'VALUES (' . $values . ';';

        //Log SQL AND DATA
        $this->logger->debug('CREATE', ['sql' => $sql, 'data' => $data]);

        $result = Connection::Execute($sql, $data);
        
        if($result[1] > 0){
            $this->lastInsertId = $result[0];
            $this->rowCount = $result[1];

            //Logger
            $this->logger->debug('CREATE OK', ['LID' => $result[0], 'rowCount' => $result[1]]);

            return true;
        }

        //Logger
        $this->logger->debug('CREATE NOT OK', ['LID' => $result[0], 'rowCount' => $result[1]]);

        return false;
    }

    /**
     * Atualiza objeto no DB
     * @return bool
     */
    protected function update(){
        $class = get_called_class();
        $old = $class::find($this->id);

        if($this->data === $old->data)
            return 0;

        $count = count($this->data) - 1;
        $i = 0;
        $j = 1;
        foreach ($this->data as $key => $value) {
            if($key != 'id' AND $key != 'created_at' AND $value != $old->data[$key]){
                if ($j == 1){
                    $sql = "UPDATE `" .  static::$table . "` SET `" . $key . "` = " . ":" . $key . ", ";
                    $data[$key] = $this->testValue($value);
                }else if($i <= $count AND $key != 'updated_at'){
                    $sql .= "`" . $key . "` = " . ":" . $key . ", ";
                    $data[$key] = $this->testValue($value);
                }
                $j++;
            }
            $i++;
        }

        if ($j == 1) return 0;

        if (array_key_exists('updated_at', $this->data))
            $sql .= "`updated_at` = NOW()";
        else
            $sql = rtrim($sql, ", ");
        $sql .= ' WHERE `id` = :id;';

        $this->logger->debug('UPDATE', ['id' => $this->id, 'sql' => $sql, 'data' => $data]);

        $data['id'] = $this->id;

        $values = Connection::Execute($sql, $data);
       
        if($values[1] > 0){
            $this->lastInsertId = $values[0];
            $this->rowCount = $values[1];

            //Logger
            $this->logger->debug('UPDATE > 0', ['LastInsertID' => $values[0], 'rowCount' => $values[1]]);

            return true;
        }

        //Logger
        $this->logger->debug('UPDATE 0 lines', ['LastInsertID' => $values[0], 'rowCount' => $values[1]]);

        return false;
    }

    /**
     * Recarrega dados no objeto, quando o mesmo é atualizado
     * @return bool
     */
    public function reload(){
        $sql = "SELECT * FROM " . static::$table . " WHERE id = ?;";

        $result = Connection::fetchArray($sql, [$this->id]);

        $result = $result[0];

        foreach($this->data as $key => $value){
            if($key != 'id')
                $this->data[$key] = $result[$key];
        }

        return true;
    }
    
    /**
     * Remove objeto do DB
     * @return bool
     */
    public function delete(){
        $sql = "DELETE FROM `" . static::$table . "` WHERE `id` = :id LIMIT 1";
        $data = ['id' => $this->id];

        $values = Connection::Execute($sql, $data);

        if($values[1]){
            return true;
        }
        return false;
    }

    protected function testValue($value){
        if($value === 0)
            return 0;
        else if ($value === false)
            return 0;
        else if ($value === true)
            return 1;
        else if(empty($value) OR is_null($value))
            return NULL;
        else
            return $value;
    }

    /**
     * Efetua validações requeridas pelo Modelo
     * @return bool
     */
    public function validate(){
        foreach ($this->validations as $field => $validations){

            $validations = explode('|', $validations);

            foreach($validations as $validation){
                $validation = explode(':', $validation);
                switch($validation[0]){
                    case 'Required':
                        $r = Validators::Required($field, $this->data[$field]);
                        if ($r !== true) $this->errors[$field][] = $r;
                        break;
                    case 'Min':
                        $r = Validators::Min($this->data[$field], $validation[1]);
                        if ($r !== true) $this->errors[$field][] = $r;
                        break;
                    case 'Max':
                        $r = Validators::Max($this->data[$field], $validation[1]);
                        if ($r !== true) $this->errors[$field][] = $r;
                        break;
                    case 'Email':
                        $r = Validators::Email($this->data[$field]);
                        if ($r !== true) $this->errors[$field][] = $r;
                        break;
                    case 'CPF':
                        $r = Validators::CPF($this->data[$field]);
                        if ($r !== true) $this->errors[$field][] = $r;
                        break;
                    case 'CNPJ':
                        $r = Validators::CNPJ($this->data[$field]);
                        if ($r !== true) $this->errors[$field][] = $r;
                        break;
                    case 'Unique':
                        $r = Validators::Unique(static::$table, $field, $this->data[$field], $this->id);
                        if ($r !== true) $this->errors[$field][] = $r;
                        break;
                    case 'MinTime':
                        $r = Validators::MinTime($this->data[$field], $validation[1]);
                        if ($r !== true) $this->errors[$field][] = $r;
                        break;
                    case 'RequiredWithout':
                        $keys = array();
                        for ($i = 1; $i <= count($validation)-1; $i++) {
                            $keys[$validation[$i]] = '';
                        }
                        $data = array_intersect_key($this->data, $keys);
                        $r = Validators::RequiredWithout($data);
                        if ($r !== true) $this->errors[$field][] = $r;
                        break;
                }
            }
        }

        if ($this->hasErrors()){
            //Logger
            $this->logger->debug('VALIDATE invalid', [static::class, $this->validations, $this->errors]);

            throw new InvalidModelException;

            return false;
        }

        //Logger
        $this->logger->debug('VALIDATE valid', [static::class]);

        return true;
    }

    /**
     * Objeto tem erros?
     * @return bool
     */
    public function hasErrors(){
        if(count($this->errors) > 0)
            return true;
        return false;
    }
    
    public function hasErrorsOn($field){
        if (isset ($this->errors[$field]))
            return true;
        return false;
    }
    
    /**
     * Retorna erro do campo field
     * @param string $field
     * @param int $item
     * @return string|false
     */
    public function getError(string $field, int $item){
        if(isset($this->errors[$field][$item]))
            return $this->errors[$field][$item];
        return false;
    }

    /**
     * Retorna erros do campo field
     * @param type $field
     * @return array
     */
    public function getErrorsOn($field){
        if (isset ($this->errors[$field]))
            return $this->errors[$field];
        return false;

    }

    public function getErrorsAsText(){
        $text = '';
        foreach ($this->errors as $key=>$errors){
            $text .= $key . ': ';
            foreach ($errors as $error){
                $text .= $error . ', ';
            }
        }
        return $text;
    }
}