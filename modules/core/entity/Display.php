<?
namespace Entity;

use \Helpers\CJSON;
use \CArrayHelper;
use \DB\Builder AS DbBuilder;

class Display{
    public function __construct(Entity $obEntity){
        $this->obEntity = $obEntity;
    }
    
    public function getEntity(){
        return $this->obEntity;
    }
    
    public function builder(){
        return (new DbBuilder)->from("new_entity_display");
    }
    
    public function getAllFields(){
        return $this->getEntity()->builder()->getFields();
    }
    
    public function getAllVisibleFields(){
        $arFields = [];
        
        foreach($this->getAllFields() AS $fieldName => $obField){
            if($obField->visible){
                $arFields[$fieldName] = $obField;
            }
        }
        
        return $arFields;
    }
    
    public function getEntityDisplayData($userID, $type){
        $arTmpEntityDisplay = $this->builder()
                                   ->select("data", "user_id")
                                   ->where("entity_id", $this->getEntity()->getTableName())
                                   ->whereIn("user_id", [0, $userID])
                                   ->orderby("user_id", "DESC")
                                   ->limit(2)
                                   ->fetchAll();

        foreach($arTmpEntityDisplay AS $arTmpEntityDisplayItem){
            $arDisplayData = CJSON::decode($arTmpEntityDisplayItem["data"], true);
            
            if(is_array($arDisplayData[$type]) && count($arDisplayData[$type])){
                return $arDisplayData[$type];
            }
        }
        
        return false;
    }
    
    public function getDisplayListFields($userID = 0){
        $arFields       = $this->getAllVisibleFields();
        $arDisplayFields= $arFields;
        
        if($arEntityDisplay = $this->getEntityDisplayData($userID, "list")){
            $arDisplayFields = [];
            
            foreach($arEntityDisplay AS $arDisplayField){
                if(($fieldName = $arDisplayField["field"]) && isset($arFields[$fieldName])){
                    $arDisplayFields[$fieldName] = $arFields[$fieldName];
                }
            }
        }else{
            $arDisplayFields = $arFields;
        }
        
        return $arDisplayFields;
    }
    
    public function getDisplayFilterFields($userID = 0){
        $arFields       = $this->getAllVisibleFields();
        $arDisplayFields= $arFields;
       
        if($arEntityDisplay = $this->getEntityDisplayData($userID, "filter")){
            $arDisplayFields = [];
            
            foreach($arEntityDisplay AS $arDisplayField){
                if(($fieldName = $arDisplayField["field"]) && isset($arFields[$fieldName])){
                    $arDisplayFields[$fieldName] = $arFields[$fieldName];
                }
            }
        }else{
            $arDisplayFields = $arFields;
        }
        
        return $arDisplayFields;
    }
    
    public function getDisplayDetailFields($userID = 0){
        $arFields           = $this->getAllVisibleFields();
        $arDisplayFields    = [];
        
        if($arEntityDisplay = $this->getEntityDisplayData($userID, "detail")){
            foreach($arEntityDisplay AS $index => $arTab){
                $arTabFields = [];
                
                if(is_array($arTab["fields"])){
                    foreach($arTab["fields"] AS $arDisplayField){
                        $fieldName = $arDisplayField["field"];
                        
                        if(($fieldName = $arDisplayField["field"]) && isset($arFields[$fieldName])){
                            $arTabFields[$fieldName] = $arFields[$fieldName];
                        }
                    }
                }
                
                $arDisplayFields[] = [
                    "name"  => "tab_" . ($index + 1),
                    "title" => $arTab["title"],
                    "fields"=> $arTabFields
                ];
            }
        }else{
            $arDisplayFields[] = [
                "name"      => "tab_main",
                "title"     => "Основное",
                "fields"    => $arFields
            ];
        }

        return $arDisplayFields;
    }
    
    public function setDisplayFields(array $arData = [], $type, $userID = 0){
        $obEntity = $this->getEntity();
        
        $arTmpEntityDisplay = $this->builder()
                                   ->select("data")
                                   ->where("entity_id", $obEntity->getTableName())
                                   ->where("user_id", $userID)
                                   ->limit(1)
                                   ->fetch();
        
        if($arTmpEntityDisplay){
            $arEntityDisplay = CJSON::decode($arTmpEntityDisplay["data"], true);
            
            if(count($arData)){
                $arEntityDisplay[$type] = $arData;
            }else{
                unset($arEntityDisplay[$type]);
            }
            
            return $this->builder()
                        ->where("entity_id", $obEntity->getTableName())
                        ->where("user_id", $userID)
                        ->update([
                           "data" => CJSON::encode($arEntityDisplay)
                        ]);
        }else{
            return $this->builder()
                        ->insert([
                            "entity_id" => $obEntity->getTableName(),
                            "user_id"   => $userID,
                            "data"      => CJSON::encode([$type => $arData])
                        ]);
        }
        
        return $this;
    }
}
?>