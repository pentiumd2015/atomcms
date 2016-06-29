<?
namespace Helpers;

class CDateTime extends \DateTime{
    public static $monthNames = ["января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря"];
    
    public function format($format){
        if(strpos($format, "M") !== false){
            $monthIndex = parent::format("n") - 1;
            $format     = str_replace("M", static::$monthNames[$monthIndex], $format);
        }
        
        return parent::format($format);
    }
}