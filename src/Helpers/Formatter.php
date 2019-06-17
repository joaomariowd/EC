<?php
namespace EC\Helpers;
use DateTime;
use DateInterval;
use NumberFormatter;

/**
 * Retorna valores formatados ou objs para formatação
 *
 * @package		EC/EC
 * @author		João Mário Nedeff Menegaz
 */
class Formatter{
    /**
     * Retorna datetime formatado
     * @param string|null $date
     * @param string|null $format
     * @return string
     */
    public static function Date(string $date = NULL, string $format = NULL){
        $format = is_null($format) ? 'd-m-Y H:i' : $format;
        $date = new DateTime($date);
        return $date->format($format);
    }

    /**
     * Retorna tempo decorrido desde date até agora em secs ou mins
     * @param string $date
     * @param type|null $format
     * @return int
     */
    public static function sinceWhen(string $date, $format = NULL){
        $date = new DateTime($date);
        $now = new DateTime("now");
        $interval = $now->diff($date);

        switch($format){
            case 'secs':
                return $interval->format('%a') * 86400 + $interval->format('%h') * 3600 + $interval->format('%i') * 60 + $interval->format('%s');
                break;
            case 'days':
                return $interval->format('%a') + ($interval->format('%h') / 24);
                break;
            case 'min':
            default:
                return $interval->format('%a') * 1440 + $interval->format('%h') * 60 + $interval->format('%i');
                break;
        }
    }

    public static function yesterday($format = NULL){
        $format = (is_null($format)) ? 'Y-m-d' : $format;
        $yesterday = new DateTime('yesterday');
        return $yesterday->format($format);
    }

    public static function addYears($date, int $years, $format = NULL){
        $format = (is_null($format)) ? 'Y-m-d' : $format;
        $date = new DateTime($date);
        $interval = new DateInterval("P" . $years . "Y");
        $date->add($interval);
        return $date->format($format);
    }

    public static function addMonths($date, int $meses, $format = NULL){
        $format = (is_null($format)) ? 'Y-m-d' : $format;
        $date = new DateTime($date);
        $interval = new DateInterval("P" . $meses . "M");
        $date->add($interval);
        return $date->format($format);
    }

    public static function addDays($date, int $days, $format = NULL){
        $format = (is_null($format)) ? 'Y-m-d' : $format;
        $date = new DateTime($date);
        $interval = new DateInterval("P" . $days . "D");
        $date->add($interval);
        return $date->format($format);
    }

    public static function diff (string $date1 = NULL, string $date2 = NULL, $format = NULL) {
        $format = (is_null($format)) ? '%a' : $format;
        $dt1 = new DateTime($date1);
        $dt2 = new DateTime($date2);
        $interval = $dt1->diff($dt2);
        return $interval->format($format);
    }

    public static function subDays($date, int $days, $format = NULL){
        $format = (is_null($format)) ? 'Y-m-d' : $format;
        $date = new DateTime($date);
        $interval = new DateInterval("P" . $days . "D");
        $date->sub($interval);
        return $date->format($format);
    }

    public static function percent($number, $decimals = NULL){
        $decimals = (is_null($decimals)) ? 1 : $decimals;
        $pf = new NumberFormatter('pt-BR', NumberFormatter::PERCENT);
        $pf->setAttribute(NumberFormatter::FRACTION_DIGITS, $decimals);
        return $pf->format($number);
    }

    public static function decimal($number, $decimals = NULL){
        $decimals = (is_null($decimals)) ? 1 : $decimals;
        $pf = new NumberFormatter('pt-BR', NumberFormatter::DECIMAL);
        $pf->setAttribute(NumberFormatter::FRACTION_DIGITS, $decimals);
        return $pf->format($number);
    }

    public static function integer(int $number){
        $nf = new NumberFormatter('pt-BR', NumberFormatter::DEFAULT_STYLE);
        $nf->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);
        return $nf->format($number);
    }

    public static function toFloat(string $number){
        //pt_br
        $number = str_replace('.', '', $number);
        $number = str_replace(',', '.', $number);
        return floatval($number);
    }

    public static function extractNumbers($string){
        preg_match_all('!\d+!', $string, $matches);
        return implode('', $matches[0]);
    }

    public static function mask($val, $mask) {
        $maskared = '';
        $k = 0;

        for($i = 0; $i <= strlen($mask) -1 ; $i++){
            if ($mask[$i] == '#') {
                if (isset($val[$k]))
                    $maskared .= $val[$k++];
            }
            else {
                if ( isset($mask[$i]) )
                    $maskared .= $mask[$i];
            }
        }

        return $maskared;
    }
}
