<?
namespace Entity\Field\Custom;

use \DB\Connection;
use \DB\Builder AS DbBuilder;
use \CUser;
use \CUserGroupAccess;
use \CArrayHelper;

class UserGroupAccessField extends Field{
    protected $arInfo = array(
        "title" => "Уровень доступа"
    );
    
    public function getRenderer(){
        return new \Entity\Field\Renderer\ListRenderer($this);
    }
    
    public function prepareFetch(array $arItems = array(), $primaryKey = false){ //fetch by multi items
        if(count($arItems)){ 
            $fieldName  = $this->getFieldName();
            $obBuilder  = new DbBuilder(Connection::getInstance());
            $arIDs      = CArrayHelper::getColumn($arItems, $primaryKey);
            
            $arUserGroupAccessValues = $obBuilder->select("id", "user_group_id", "user_group_access_id")
                                                 ->from(CUserGroupAccess::GROUP_ACCESS_VALUE_TABLE)
                                                 ->whereIn("user_group_id", $arIDs)
                                                 ->fetchAll();
        }

        foreach($arUserGroupAccessValues AS $arUserGroupAccessValue){
            $arItems[$arUserGroupAccessValue["user_group_id"]][$fieldName][$arUserGroupAccessValue["id"]] = $arUserGroupAccessValue["user_group_access_id"];
        }
        
        return $arItems;
    }
    
    public function filter($value, \Entity\Builder $obBuilder){
        $obEntity   = $this->getEntity();
        $table      = $obEntity->getTableName();
        $pk         = $obEntity->getPk();
        
        if(is_string($value) && strlen($value)){
            $obBuilder->whereIn($table . "." . $pk, function($obBuilder) use($obEntity, $value){
                $obBuilder->select("user_group_id")
                          ->from(CUserGroupAccess::GROUP_ACCESS_VALUE_TABLE)
                          ->where("user_group_access_id", $value);
            });
        }
    }
    
    public function orderBy($by, \Entity\Builder $obBuilder){
        $accessTable        = CUserGroupAccess::getTableName();
        $accessPk           = CUserGroupAccess::getPk();
        $obEntity           = $this->getEntity();
        $table              = $obEntity->getTableName();
        $pk                 = $obEntity->getPk();
        $valueTable         = CUserGroupAccess::GROUP_ACCESS_VALUE_TABLE;
        
        $pkColumn = $table . "." . $pk;
        
        $obBuilder->leftJoin($valueTable, $valueTable . ".user_group_id", "=", $table . "." . $pk)
                  ->leftJoin($accessTable, $accessTable . "." . $accessPk, "=", $valueTable . ".user_group_access_id")
                  ->orderBy($accessTable . ".title", $by)
                  ->groupBy($table . "." . $pk);
    }
    
    public function loadValues(){
        /*load at once*/
        static $arValues = NULL;
        
        if($arValues == NULL){
            $arValues = CUserGroupAccess::builder()->orderBy("title", "ASC")->fetchAll();
            
            $arValues = CArrayHelper::index($arValues, CUserGroupAccess::getPk());
        }else{
            static $arValues = array();
        }
        /*load at once*/
        
        return $arValues;
    }
    
    public function add(array $arData, $primaryKey, array $arItems){ //$arItems - multi items with indexed by pk
        $this->save($arData, $primaryKey, $arItems);
    }
    
    public function update(array $arData, $primaryKey, array $arItems){ //$arItems - multi items with indexed by pk
        $this->save($arData, $primaryKey, $arItems);
    }
    
    public function save(array $arData, $primaryKey, array $arItems){   //$arItems - multi items with indexed by pk      
        $fieldName  = $this->getFieldName();
        $arIDs      = CArrayHelper::getColumn($arItems, $primaryKey);
        $arValues   = $arData[$fieldName];
        
        if(is_numeric($arValues)){
            $arValues = array($arValues);
        }
        
        if(is_array($arValues)){
            CUserGroupAccess::setGroupAccess($arIDs, $arValues);
        }
    }
    
    public function delete($primaryKey, array $arItems){   //$arItems - multi items with indexed by pk      
        $arIDs = CArrayHelper::getColumn($arItems, $primaryKey);
        
        CUserGroupAccess::setGroupAccess($arIDs, false);
    }
}
?>