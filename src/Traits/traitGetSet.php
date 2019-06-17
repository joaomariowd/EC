<?php
namespace EC\Traits;
use Exception;
use ReflectionMethod;

/**
 * traitGetSet
 *
 * @package		EC/EC
 * @author		João Mário Nedeff Menegaz
 */
trait traitGetSet{

    /**
     * __set
     * Verifica se property existe no objeto, ou se existe como chave do array data
     * Se houver método setter, chama,
     * Se não houver setter lança direto
     * @param string $varName
     * @param string $value
     */
    public function __set($key, $value){
        $class_name = get_class($this);
        $class_vars = get_class_vars($class_name);
        $methodName = 'set_' . $key;

        if (array_key_exists($key, $class_vars)){
            if (method_exists($class_name, $methodName))
                call_user_func([$this, $methodName], $value);
            else
                $this->$key = $value;
        }
        else if (array_key_exists($key, $class_vars['data'])){
            if (method_exists($this, $methodName))
                call_user_func([$this, $methodName], $value);
            else
                $this->data[$key] = $value;
        }
        else{
            throw new Exception ("Setter exception, class: $class_name, key: $key, value: $value");
        }
    }

    /**
     * __get
     * Verifica se property existe no objeto, ou se existe como chave do array data
     * Se houver método getter, chama,
     * Se não houver getter, chama direto
     * @param string $property
     */
    public function __get($key){
        $class_name = get_class($this);
        $class_vars = get_class_vars($class_name);
        $methodName = 'get_' . $key;

        if (array_key_exists($key, $class_vars)){
            if (method_exists($class_name, $methodName))
                return call_user_func([$this, $methodName]);
            else
                return $this->$key;
        }
        else if (array_key_exists($key, $class_vars['data'])){
            if (method_exists($class_name, $methodName))
                return call_user_func([$this, $methodName]);
            else
                return $this->data[$key];
        }
        else{
            throw new Exception ("Getter exception, class: $class_name, key: $key");
        }
    }
}
