<?
namespace Entity\Field\Custom;

use \CUser;
use \CUserGroup;
use \CArrayHelper;
use \DB\Connection;
use \DB\Builder AS DbBuilder;

class UserGroupField extends Field{
    protected $arInfo = array(
        "title" => "Группа пользователей"
    );
    
    public function getRenderer(){
        return new \Entity\Field\Renderer\ListRenderer($this);
    }
    
    public function prepareFetch(array $arItems = array(), $primaryKey = false){ //fetch by multi items
        if(count($arItems)){ 
            $fieldName  = $this->getFieldName();
            $obBuilder  = new DbBuilder(Connection::getInstance());
            
            $arUserGroupValues = $obBuilder->select("id", "user_id", "user_group_id")
                                          ->from(CUser::GROUP_VALUE_TABLE)
                                          ->whereIn("user_id", CArrayHelper::getColumn($arItems, $primaryKey))
                                          ->fetchAll();
        }
        
        foreach($arUserGroupValues AS $arUserGroupValue){
            $arItems[$arUserGroupValue["user_id"]][$fieldName][$arUserGroupValue["id"]] = $arUserGroupValue["user_group_id"];
        }
        
        return $arItems;
    }
    
    public function filter($value, \Entity\Builder $obBuilder){
        $obEntity   = $this->getEntity();
        $table      = $obEntity->getTableName();
        $pk         = $obEntity->getPk();
        
        if(is_string($value) && strlen($value)){
            $obBuilder->whereIn($table . "." . $pk, function($obBuilder) use($obEntity, $value){
                $obBuilder->select(CUser::GROUP_VALUE_TABLE . ".user_id")
                          ->from(CUser::GROUP_VALUE_TABLE)
                          ->where(CUser::GROUP_VALUE_TABLE . ".user_group_id", $value);
            });
        }
    }
    
    public function orderBy($by, \Entity\Builder $obBuilder){
        $groupTable         = CUserGroup::getTableName();
        $groupPk            = CUserGroup::getPk();
        $obEntity           = $this->getEntity();
        $table              = $obEntity->getTableName();
        $pk                 = $obEntity->getPk();
        
        $obBuilder->leftJoin(CUser::GROUP_VALUE_TABLE, CUser::GROUP_VALUE_TABLE . ".user_id", "=", $table . "." . $pk)
                  ->leftJoin($groupTable, $groupTable . "." . $groupPk, "=", CUser::GROUP_VALUE_TABLE . ".user_group_id")
                  ->orderBy($groupTable . ".title", $by)
                  ->groupBy($table . "." . $pk);
    }
    
    public function loadValues(){
        /*load at once*/
        static $arValues = NULL;
        
        if($arValues == NULL){
            $arValues = CUserGroup::builder()->orderBy("title", "ASC")->fetchAll();
            
            $arValues = CArrayHelper::index($arValues, CUserGroup::getPk());
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
        
        CUser::setGroups($arIDs, $arValues);
    }
    
    public function delete($primaryKey, array $arItems){   //$arItems - multi items with indexed by pk      
        $arIDs = CArrayHelper::getColumn($arItems, $primaryKey);
        
        CUser::setGroups($arIDs, false);
    }
}
?>