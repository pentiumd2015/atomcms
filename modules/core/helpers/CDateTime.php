<?
namespace Helpers;

class CDateTime extends \DateTime{
    static public $arMonthNames = array("января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря");
    
    public function format($format){
        if(strpos($format, 'M') !== false){
            $monthIndex = parent::format("n") - 1;
            $format     = str_replace('M', static::$arMonthNames[$monthIndex], $format);
        }
        
        return parent::format($format);
    }
    
    static public function validate($date, $format = "Y-m-d H:i:s"){
        $d = self::createFromFormat($format, $date);

        return $d && $d->format($format) == $date;
    }
}