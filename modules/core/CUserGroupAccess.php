<?
use \DB\Connection;
use \DB\Builder AS DbBuilder;

class CUserGroupAccess extends \Entity\Entity{
    static protected $_table    = "user_group_access";
    static protected $_pk       = "id";
    
    const ADMIN_ACCESS = "ADMIN_ACCESS";
    
    static protected $arEvents = array(
        "ADD"       => "USER.GROUP.ACCESS.ADD",
        "UPDATE"    => "USER.GROUP.ACCESS.UPDATE",
        "DELETE"    => "USER.GROUP.ACCESS.DELETE",
    );
    
    const GROUP_ACCESS_VALUE_TABLE = "user_group_access_value";

    public function getFields(){
        return array(
            new \Entity\Field\IntegerField("id", array(
                "title"     => "ID"
            ), $this),
            new \Entity\Field\StringField("title", array(
                "title"     => "Title",
                "required"  => true
            ), $this),
            new \Entity\Field\StringField("alias", array(
                "title"     => "Алиас",
                "validate"  => function(){
                    return array(
                        new \Entity\Field\Validate\Unique(),
                        "/^[a-zA-Z0-9-_]+$/si"
                    );
                },
                "required"  => true,
                /*"unique"    => function($obField, $arData){
                    $pk = UserGroupAccess::getPk();
                    
                    if(isset($arData[$pk])){ //check on update
                        $obExist = UserGroupAccess::find("alias=? AND id!=?", array($arData["alias"], $arData[$pk]));
                    }else{ //check on add
                        $obExist = UserGroupAccess::find("alias=?", array($arData["alias"]));
                    }
                    
                    return !$obExist;
                }*/
            ), $this),
            new \Entity\Field\TextField("description", array(
                "title"     => "Description",
                "required"  => true
            ), $this),
        );
    }      

    static public function add(array $arData){        
        return static::_save($arData, false);
    }
    
    static public function update($id, array $arData){
        return static::_save($arData, $id);
    }
    
    static protected function _save($arData, $id = false){
        if(!$id){
            $obResult = parent::add($arData);
        }else{
            $obResult = parent::update($id, $arData);
        }
        
        if(!$obResult->isSuccess()){
            $arErrors = $obResult->getErrors();
            
            switch($arErrors["title"]["error"]){
                case \Entity\Field\Field::ERROR_REQUIRED:
                    $arErrors["title"]["message"] = "Укажите название правила";
                    break;
                case \Entity\Field\Field::ERROR_INVALID:
                    $arErrors["title"]["message"] = "Неверный формат названия правила";
                    break;
                case \Entity\Field\Field::ERROR_NOT_UNIQUE:
                    $arErrors["title"]["message"] = "Правило с таким названием уже существует";
                    break;    
            }
            
            switch($arErrors["alias"]["error"]){
                case \Entity\Field\Field::ERROR_REQUIRED:
                    $arErrors["alias"]["message"] = "Укажите алиас правила";
                    break;
                case \Entity\Field\Field::ERROR_INVALID:
                    $arErrors["alias"]["message"] = "Алиас может содержать латинские буквы, цифры и -_";
                    break;
                case \Entity\Field\Field::ERROR_NOT_UNIQUE:
                    $arErrors["alias"]["message"] = "Правило с таким алиасом уже существует";
                    break;
            }
            
            $obResult->setErrors($arErrors);
        }else{
            $id = $obResult->getID();
            
            if(isset($arData["access"])){
                UserGroupAccessValue::setValues($id, $arData["access"]);
            }
        }
        
        return $obResult;
    }
    
    static public function getGroupAccess($userGroupID){
        $arResult = array();
        
        if(!is_array($userGroupID)){
            $arUserGroupIDs = array($userGroupID);
        }else{
            $arUserGroupIDs = $userGroupID;
        }
        
        if(count($arUserGroupIDs)){
            $obBuilder = static::builder();
            
            $arGroupAccess = $obBuilder->select("t1.*", "t2.user_group_id")
                      ->alias("t1")
                      ->rightJoin(self::GROUP_ACCESS_VALUE_TABLE . " AS t2", "t1.id", "=", "t2.user_group_access_id")
                      ->whereIn("t2.user_group_id", $arUserGroupIDs)
                      ->fetchAll();
                      
            
            
            foreach($arGroupAccess AS $arGroupAccessItem){
                $arResult[$arGroupAccessItem["user_group_id"]][$arGroupAccessItem["alias"]] = $arGroupAccessItem;
            }

            $arGroupAccess = CArrayHelper::index($arGroupAccess, "user_group_id", true);
        }
        
        return $arResult;
    }
    
    static public function setGroupAccess($userGroupID, $arAccessIDs = array()){
        $connection = Connection::getInstance();
        $obBuilder  = new DbBuilder($connection);
        
        if(!is_array($userGroupID)){
            $arUserGroupIDs = array($userGroupID);
        }else{
            $arUserGroupIDs = $userGroupID;
        }
        
        $obBuilder->from(self::GROUP_ACCESS_VALUE_TABLE)
                  ->whereIn("user_group_id", $arUserGroupIDs);
        
        if($arAccessIDs !== false && is_array($arAccessIDs) && count($arAccessIDs)){
            $obBuilder->whereNotIn("user_group_access_id", $arAccessIDs);
        }
        
        $obBuilder->delete();

        if(count($arAccessIDs)){
            $obBuilder = new DbBuilder($connection);
            
            $arTmpUserGroupAccessValues = $obBuilder->from(self::GROUP_ACCESS_VALUE_TABLE)
                                                    ->whereIn("user_group_id", $arUserGroupIDs)
                                                    ->fetchAll();
            
            $arUserGroupAccessValues = array();
            
            foreach($arTmpUserGroupAccessValues AS $arUserGroupAccessValue){
                $hash                           = $arUserGroupAccessValue["user_group_id"] . ":" . $arUserGroupAccessValue["user_group_access_id"];
                $arUserGroupAccessValues[$hash] = $arUserGroupAccessValue;
            }
            
            $obBuilder = new DbBuilder($connection);
            $obBuilder->from(self::GROUP_ACCESS_VALUE_TABLE);
            
            foreach($arAccessIDs AS $accessID){
                foreach($arUserGroupIDs AS $userGroupID){
                    $hash = $userGroupID . ":" . $accessID;
                    
                    if(!isset($arUserGroupAccessValues[$hash])){
                        $obBuilder->insert(array(
                            "user_group_id"         => $userGroupID,
                            "user_group_access_id"  => $accessID
                        ));
                    }
                }
            }
        }
    }    
}
?>