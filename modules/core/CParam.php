<?
use \DB\Builder;
use \DB\Connection;

class CParam{
    static public function builder(){
        $obBuilder = new Builder(Connection::getInstance());
        
        return $obBuilder->from("param");
    }
    
    static public function get($paramName, $userID = 0){
        $arParam = static::builder()->select("value")
                                    ->where("name", $paramName)
                                    ->where("user_id", $userID)
                                    ->limit(1)
                                    ->fetch();
                                    
        return $arParam ? $arParam["value"] : false ;
    }
     
    static public function set($paramName, $value, $userID = 0){
        $pk = static::getPk();
        
        if(($arParam = static::get($paramName, $userID))){
            static::builder()->where("id", $arParam["id"])
                             ->update(array("value" => $value));
        }else{
            static::builder()->insert("param", array(
                "name"      => $paramName,
                "user_id"   => $userID,
                "value"     => $value
            ));
        }
        
        return $value;
    }
    
    static public function delete($paramName, $userID = 0){
        return static::builder()->where("name", $paramName)
                                ->where("user_id", $userID)
                                ->delete();
    }
}
?>