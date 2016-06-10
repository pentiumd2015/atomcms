<?
class CJSON{
    static public function encode($mixed, $flag = JSON_UNESCAPED_UNICODE /*| JSON_PRETTY_PRINT*/, $depth = 512){
        return json_encode($mixed, $flag, $depth);
    }
    
    static public function decode($str, $isAssoc = false){
        return json_decode($str, $isAssoc);
    }
}
?>