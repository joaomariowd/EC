<?php
/**
 * Validators
 */
namespace EC\Helpers;
use EC\Model\Connection;

/**
 * Oferece vários métodos para Validação
 *
 * @package		EC/EC
 * @author		João Mário Nedeff Menegaz
 */
class Validators{

    /**
     * Required
     * Não pode estar em branco
     * @param string $field
     * @param string $string
     * @return true|string
     * true ou mensagem de erro
     */
    public static function Required($field, $string){
        if(!empty($string))
            return true;
        return ucfirst($field) . ' deve ser preenchido.';
    }

    /**
     * Max
     * Número máximo de caracteres
     * @param string $string
     * @param int 	 $no_chars
     * @return true|string
     * true ou mensagem de erro
     */
    public static function Max($string, $no_chars){
         if (strlen($string) <= $no_chars)
             return true;

         return "No máximo $no_chars caracteres.";
    }

    /**
     * Min
     * Número mínimo de caracteres
     * @param string $string
     * @param int 	 $no_chars
     * @return true|string
     * true ou mensagem de erro
     */
    public static function Min($string, $no_chars){
         if (strlen($string) >= $no_chars)
             return true;

         return "Necessário ao menos $no_chars caracteres.";
    }

    /**
     * Email
     * Valida endereço de e-mail
     * @param string $string
     * @return true|string
     * true ou mensagem de erro
     */
    public static function Email($string){
        if (filter_var($string, FILTER_VALIDATE_EMAIL))
            return true;
        return 'E-mail inválido!';
    }

    /**
     * CPF
     * Valida CPF
     * @param string $cpf
     * @return true|string
     * true ou error message
     */
     public static function CPF($string) {
         if(empty($string)) return true;
         // Extrai somente os números
         $cpf = preg_replace( '/[^0-9]/is', '', $string );

         // Verifica se foi informado todos os digitos corretamente
         if (strlen($string) != 11) {
             return "CPF inválido!";
         }
         // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
         if (preg_match('/(\d)\1{10}/', $string)) {
             return "CPF inválido!";
         }
         // Faz o calculo para validar o CPF
         for ($t = 9; $t < 11; $t++) {
             for ($d = 0, $c = 0; $c < $t; $c++) {
                 $d += $string{$c} * (($t + 1) - $c);
             }
             $d = ((10 * $d) % 11) % 10;
             if ($cpf{$c} != $d) {
                 return "CPF inválido!";
             }
         }
         return true;
     }

    /**
     * CNPJ
     * Valida CNPJ
     * @param string $cnpj
     * @return true|string
     * true ou error message
     */
     public static function CNPJ($string) {
         $cnpj = preg_replace('/[^0-9]/is', '', (string) $string);
         // Valida tamanho
         if (strlen($cnpj) != 14)
             return "CNPJ inválido!";
         // Valida primeiro dígito verificador
         for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++)
         {
             $soma += $cnpj{$i} * $j;
             $j = ($j == 2) ? 9 : $j - 1;
         }
         $resto = $soma % 11;
         if ($cnpj{12} != ($resto < 2 ? 0 : 11 - $resto))
             return "CNPJ inválido!";
         // Valida segundo dígito verificador
         for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++)
         {
             $soma += $cnpj{$i} * $j;
             $j = ($j == 2) ? 9 : $j - 1;
         }
         $resto = $soma % 11;
         return $cnpj{13} == ($resto < 2 ? 0 : 11 - $resto);
     }

    /**
     * Unique
     * Valida se value de field é único para table
     * @param string $table
     * @param string $field
     * @param var $value
     * @param int $id
     * @return true|string
     * true ou mensagem de erro
     */
    public static function Unique($table, $field, $value, $id){
        if(is_int($id)){
            $data = [$value, $id];
            $sql = "SELECT $field FROM $table WHERE $field = ? AND id != ?;";
        }else{
            $data = [$value];
            $sql = "SELECT $field FROM $table WHERE $field = ?;";
        }


        $r = Connection::fetchArray($sql, $data);

        if($r[1] == 0)
            return true;
        return ucfirst($field) . " já cadastrado.";
    }

    /**
     * MinTime
     * Vaerifica se o tempo decorrido de datetime até agora é maior do que secs
     * @param string $datetime
     * @param int $secs
     * @return true|string
     * true ou mensagem de erro
     */
    public static function MinTime($datetime, $secs){
        $secs = (int) $secs;
        $r = (bool) (Formatter::sinceWhen($datetime, 'secs') > $secs);

        if($r)
            return true;

        return 'Aguarde um minuto e tente novamente.';
    }

    public static function RequiredWithout(array $values){
        $r = false;
        foreach($values as $value){
            if (!is_null($value) AND !empty($value))
                return true;
        }

        return 'Ao menos uma resposta deve ser preenchida.';
    }

}
