<?
namespace Helpers;


class CText{
    public static function translit($str, $params = []){
        $str = strtr(strtolower($str), [
            "а" => "a",
            "б" => "b",
            "в" => "v",
            "г" => "g",
            "д" => "d",
            "е" => "e",
            "ё" => "e",
            "ж" => "zh",
            "з" => "z",
            "и" => "i",
            "й" => "y",
            "к" => "k",
            "л" => "l",
            "м" => "m",
            "н" => "n",
            "о" => "o",
            "п" => "p",
            "р" => "r",
            "с" => "s",
            "т" => "t",
            "у" => "u",
            "Ф" => "f",
            "х" => "h",
            "ц" => "ts",
            "ч" => "ch",
            "ш" => "sh",
            "щ" => "shch",
            "ъ" => "'",
            "ы" => "i",
            "ь" => "",
            "э" => "e",
            "ю" => "yu",
            "я" => "ya",
            " " => "_"
        ]);
        
        if(isset($params["chars"]) && is_array($params["chars"])){
            $str = strtr($str, $params["chars"]);
        }
        
        if(isset($params["trim"]) && $params["trim"] == true){
            $str = trim($str, $params["trim"]);
        }
        
        if(isset($params["uppercase"]) && $params["uppercase"] == true){
            $str = strtoupper($str);
        }
        
        return $str;
    }
    
    public static function numToStr($num){
        $num        = floatval($num);
        $isNegative = $num < 0;
        
        if($isNegative){
            $num*= -1;
        }
        
        $zero   = "ноль";
        $between1and9  = [
            ["", "один", "два", "три", "четыре", "пять", "шесть", "семь", "восемь", "девять"],
            ["", "одна", "две", "три", "четыре", "пять", "шесть", "семь", "восемь", "девять"]
        ];

        $between10and19 = ["десять", "одиннадцать", "двенадцать", "тринадцать", "четырнадцать", "пятнадцать", "шестнадцать", "семнадцать", "восемнадцать", "девятнадцать"];
        $tens           = ["", "", "двадцать", "тридцать", "сорок", "пятьдесят", "шестьдесят", "семьдесят", "восемьдесят", "девяносто"];
        $hundreds       = ["", "сто", "двести", "триста", "четыреста", "пятьсот", "шестьсот", "семьсот", "восемьсот", "девятьсот"];
        
        $units = [
            ["копеек", "копейка", "копейки", 1], //0 , 1, 2
            ["рублей", "рубль", "рубля", 0],
            ["тысяч", "тысяча", "тысячи", 1],
            ["миллионов", "миллион", "миллиона", 0],
            ["миллиардов", "миллиард", "миллиарда", 0]
        ];
    
        list($rub, $kop) = explode(".", sprintf("%015.2f", $num));

        $out = array();
        
        if(intval($rub) > 0){
            $countUnits = count($units);
            
            foreach(str_split($rub, 3) AS $uk => $v) { // by 3 symbols
                if(!intval($v)){
                    continue;
                }
                
                $uk     = $countUnits - $uk - 1; // unit key
                $gender = $units[$uk][3];
                
                list($i1, $i2, $i3) = array_map("intval", str_split($v, 1));
                
                $out[] = $hundreds[$i1]; // 1xx-9xx
                
                if($i2 > 1){
                    $out[] = $tens[$i2] . " " . $between1and9[$gender][$i3]; // 20-99
                }else{
                    $out[] = $i2 > 0 ? $between10and19[$i3] : $between1and9[$gender][$i3]; // 10-19 | 1-9
                }
                
                if($uk > 1){
                    $out[] = static::declension($v, "", $units[$uk]);
                }
            }
        }else{
            $out[] = $zero;
        }
        
        $out[] = static::declension(intval($rub), "", $units[1]); // rub
        $out[] = $kop . " " . static::declension($kop, "", $units[0]); // kop
        
        return ($isNegative ? "минус " : "") . trim(preg_replace("/ {2,}/", " ", implode(" ", $out)));
    }
    
    public static function declension($num = 0, $word = null, $endings = []){
		$mod = $num % 100;
		$mod = $mod > 10 && $mod < 20 ? 0 : $num % 10 ;
        
        if($mod > 2 && $mod <= 4){
            $mod = 2;
        }else if($mod >= 5 && $mod <= 9){
            $mod = 0;
        }

		return $word . $endings[$mod];
	}
    
    public static function getEncoding($str){
        $encodeList = ["utf-8", "cp1251"];
        
        foreach($encodeList AS $codepage){
            if(md5($str) === md5(iconv($codepage, $codepage, $str))){
                return $codepage;
            }
        }
        
        return false;
    }
    
    public static function toUtf8($str){
        if(self::getEncoding($str) == "cp1251"){
            $str = iconv("cp1251", "utf-8", $str);
        }
        
        return $str;
    }
    
    public static function toCp1251($str){
        return iconv("utf-8", "cp1251", $str);
    }
    
    public static function ucfirst($str){
        return mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1);
    }
}