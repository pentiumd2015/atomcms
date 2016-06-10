<?
namespace Entities;

class EntityGroup extends \DB\Manager{
    static protected $_table    = "entity_group";
    static protected $_pk       = "entity_group_id";
    
    static public function add($arData){
        return static::_save($arData, false);
    }
    
    static public function updateByPk($entityGroupID, $arData){
        return static::_save($arData, $entityGroupID);
    }
    
    static public function deleteByPk($entityGroupID){
        $result = parent::deleteByPk($entityGroupID);
        
        \CEvent::trigger("ENTITY.GROUP.DELETE", array($entityGroupID));
        
        return $result;
    }
    
    static protected function _save($arData, $entityGroupID = false){
        $arReturn = array(
            "success" => false
        );
        
        $arErrors = array();
    
        if(!$entityGroupID){
            if(empty($arData["title"])){
                $arErrors["title"][] = "Введите название группы";
            }
        }else{
            if(isset($arData["title"]) && empty($arData["title"])){
                $arErrors["title"][] = "Введите название группы";
            }
        }
    
        if(!count($arErrors)){
            $arData = static::getSafeFields($arData, array(
                "title",
                "description"
            ));
            
            if($entityGroupID){
                $arData["date_update"] = new \DB\Expr("NOW()");
                
                parent::updateByPk($entityGroupID, $arData);
                
                $arReturn["success"] = true;
                
                \CEvent::trigger("ENTITY.GROUP.UPDATE", array($entityGroupID, $arData));
            }else{
                $arData["date_add"] = $arData["date_update"] = new \DB\Expr("NOW()");
                
                $entityGroupID = parent::add($arData);
                
                if($entityGroupID){
                    $arReturn["success"] = true;
                    
                    \CEvent::trigger("ENTITY.GROUP.ADD", array($entityGroupID, $arData));
                }else{
                    $arReturn["hasErrors"]  = true;
                    $arReturn["errors"][]   = "Ошибка добавления данных";
                }
            }
            
            if($entityGroupID){
                $arReturn["id"] = $entityGroupID;
            }
        }else{
            $arReturn["hasErrors"]  = true;
            $arReturn["errors"]     = $arErrors;
        }
        
        return $arReturn;
    }
}
?>