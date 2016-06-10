<?
class CText{
    public static function translit($str, $arParams = array()){
        $str = mb_strtolower($str, 'UTF-8');
        
        $str = strtr($str, array(
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'e',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'y',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'Ф' => 'f',
            'х' => 'h',
            'ц' => 'ts',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'shch',
            'ъ' => '\'',
            'ы' => 'i',
            'ь' => '',
            'э' => 'e',
            'ю' => 'yu',
            'я' => 'ya',
            ' ' => '_'
        ));
        
        if(isset($arParams['chars']) && is_array($arParams['chars'])){
            $str = strtr($str, $arParams['chars']);
        }
        
        if(isset($arParams['trim'])){
            $str = trim($str, $arParams['trim']);
        }
        
        if(isset($arParams['uppercase'])){
            $str = mb_strtoupper($str, 'UTF-8');
        }
        
        return $str;
    }
    
    static public function numToStr($num){
        $num        = floatval($num);
        $isNegative = $num < 0;
        
        if($isNegative){
            $num*= -1;
        }
        
        $zero   = 'ноль';
        $arTen  = array(
            array('', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'), 
            array('', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять')
        );
        
        $arTens     = array('', '', 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто');    
        $ar20       = array('десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать');    
        $arHundreds = array('', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот');
        
        $arUnits = array(
            array('копеек', 'копейка', 'копейки', 1), //0 , 1, 2
            array('рублей', 'рубль', 'рубля', 0),
            array('тысяч', 'тысяча', 'тысячи', 1),
            array('миллионов', 'миллион', 'миллиона',   0),
            array('миллиардов', 'миллиард', 'миллиарда', 0),
        );
    
        list($rub, $kop) = explode('.', sprintf("%015.2f", $num));

        $out = array();
        
        if(intval($rub) > 0){
            $countUnits = count($arUnits);
            
            foreach(str_split($rub, 3) AS $uk => $v) { // by 3 symbols
                if(!intval($v)){
                    continue;
                }
                
                $uk     = $countUnits - $uk - 1; // unit key
                $gender = $arUnits[$uk][3];
                
                list($i1, $i2, $i3) = array_map('intval', str_split($v, 1));
                
                $out[] = $arHundreds[$i1]; // 1xx-9xx
                
                if($i2 > 1){
                    $out[] = $arTens[$i2] . ' ' . $arTen[$gender][$i3]; // 20-99
                }else{
                    $out[] = $i2 > 0 ? $ar20[$i3] : $arTen[$gender][$i3]; // 10-19 | 1-9
                }
                
                if($uk > 1){
                    $out[] = static::declension($v, "", $arUnits[$uk]);
                }
            }
        }else{
            $out[] = $zero;
        }
        
        $out[] = static::declension(intval($rub), "", $arUnits[1]); // rub
        $out[] = $kop . ' ' . static::declension($kop, "", $arUnits[0]); // kop
        
        return ($isNegative ? "минус " : "") . trim(preg_replace('/ {2,}/', ' ', implode(' ', $out)));
    }
    
    public static function declension($num = 0, $word = NULL, $arEndings = array()){
		$mod = $num % 100;
		$mod = $mod > 10 && $mod < 20 ? 0 : $num % 10 ;
        
        if($mod > 2 && $mod <= 4){
            $mod = 2;
        }else if($mod >= 5 && $mod <= 9){
            $mod = 0;
        }

		return $word . $arEndings[$mod];
	}
    
    public static function getEncoding($str){
        $encodeList = array('utf-8', 'cp1251');
        
        foreach($encodeList AS $codepage){
            if(md5($str) === md5(iconv($codepage, $codepage, $str))){
                return $codepage;
            }
        }
        
        return false;
    }
    
    public static function toUTF8($str){
        if(self::getEncoding($str) == 'cp1251'){
            $str = iconv('cp1251', 'utf-8', $str);
        }
        
        return $str;
    }
    
    public static function toCP1251($str){
        return iconv('utf-8', 'cp1251', $str);
    }
    
    static public function ucfirst($str){
        return mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1);
    }
    
    static public function escape($str){
        return strtr($str,  array(
            "\xe2\x80\xa9"  => " ",
            "\\"            => "\\\\",
            "'"             => "\\'",
            "\""            => "\\\"",
            "\r\n"          => "\n", 
            "\r"            => "\n", 
            "\n"            => "\\n",
            "\xe2\x80\xa8"  => "\\n",
            "*/"            => "*\\/",
            "</"            => "<\\/"
            /*"&"     => "&amp;",
            "<"     => "&lt;",
            ">"     => "&gt;",
            "\""    => "&quot;",
            "'"     => "&#039;"*/
        ));
    }
}
?>