<?php
namespace EC\Helpers;

/**
 * Configs para Connection
 * Lê arquivo ROOT/config/database.ini
 * 
 * @package		EC/EC
 * @author		João Mário Nedeff Menegaz		
 */
class Config{
    /**
     * @var array
     * Dados de config
     */
    protected $data;

    /**
     * Chama Carrega dados
     */
    public function __construct(){
        $this->loadData();
        $this->setDebugLevel();
    }

    protected function setDebugLevel(){
        switch($this->app_mode){
            case 'development':
                ini_set('display_errors', 'On');
                error_reporting(E_ALL | E_STRICT);
                break;
            case 'staging':
            case 'production':
                ini_set('display_errors', 'Off');
                error_reporting(0);
                break;
        }
    }

    /**
     * Carrega dados
     */
    private function loadData(){
        if(!file_exists(CONFIGS . "/database.ini"))
            die('Arquivo de configuração não encontrado.');

        $config = parse_ini_file(CONFIGS . "/database.ini", true);

        //APP MODE
        $app_mode = $config['app']['mode'];
        $this->data['app_mode'] = $app_mode;
        //LogLevel
        $this->data['logLevel'] = $config['app']['logLevel'];

        //Carrega variáveis do app_mode escolhido
        foreach($config[$app_mode] as $key => $value){
            $this->data[$key] = $value;
        }
    }

    /**
     * Magic getter
     * @param string $key 
     */
    public function __get($key){
        if(isset($this->data[$key]))
            return $this->data[$key];
    }

    /**
     * Magic setter
     * @param string $property 
     * @param var $value 
     * @return var
     */
    public function __set($property, $value){
        $methodName = 'set_' . $property;

        if( method_exists($this, $methodName) )
            call_user_func([$this, $methodName], $value);
        else
            $this->data[$property] = $value;
    }
}