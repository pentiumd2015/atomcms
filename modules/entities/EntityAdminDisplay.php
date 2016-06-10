<?
namespace Entities;

use \Helpers\CJSON;

class EntityAdminDisplay extends \DB\Manager{
    static protected $_table    = "entity_admin_display";
    static protected $_pk       = "entity_admin_display_id";
    
    static public function getSectionInheritance($arSectionIDs, $userID = 0, $relation = EntityItem::TYPE_ELEMENT, $type = "list"){ //$relation - section or item, $type - list or detail        
        $arResult = array();

        array_walk($arSectionIDs, "intval");
        $arSectionIDs = array_filter($arSectionIDs);
        
        if(!count($arSectionIDs)){
            return array();
        }
        
        $arEntitySections = EntitySectionTree::findAll(array(
            "alias"     => "t1",
            "select"    => "t1.path, t1.entity_item_id, t2.entity_id",
            "join"      => "INNER JOIN entity_item t2 ON(t1.entity_item_id=t2.entity_item_id)",
            "condition" => "t1.entity_item_id IN(" . implode(", ", $arSectionIDs) . ")",
            "order"     => "t1.path DESC"
        ));

        $entityID = $arEntitySections[0]->entity_id;
        
        $userID = (int)$userID;
        $params = \CParam::get("admin_display:entity_" . $entityID, $userID);

        if($params){
            $arParams = CJSON::decode($params, true);

            $arInheritSections = array();
            
            foreach($arEntitySections AS $obEntitySection){
                if(isset($arParams[$type][$relation][$obEntitySection->entity_item_id])){
                    $arResult[$obEntitySection->entity_item_id] = $obEntitySection->entity_item_id;
                }else{ //если своей нет, то собираем для получения ид раздела от кого идет наследование
                    $arInheritSections[] = $obEntitySection;
                }
            }
            
            $arParentPaths = array();
            
            //получаем пути родителей для каждого раздела
            foreach($arInheritSections AS $obEntitySection){
                $path = $obEntitySection->path;
                
                $obEntitySection->parent_paths = array();
    
                while(($pos = strrpos($path, EntitySectionTree::PATH_SEPARATOR)) !== false){
                    $path = substr($path, 0, $pos);
                    $arParentPaths[$path] = $obEntitySection->parent_paths[$path] = 1;
                }
            }
            
            $arParentSections = array();

            //получаем родителей
            $arTmpParentSections = EntitySectionTree::findAll(array(
                "select"    => "path, entity_item_id",
                "condition" => "path IN(\"" . implode('", "', array_keys($arParentPaths)) . "\")",
                "order"     => "path DESC"
            ), array($userID, $relation));
             
            //индексируем по полю path
            foreach($arTmpParentSections AS $obTmpParentSection){
                if(!isset($arParentSections[$obTmpParentSection->path])){
                    $arParentSections[$obTmpParentSection->path] = $obTmpParentSection;
                }
            }

            $arBaseDisplay = array();
            
            //снова проходимся по тем разделам, для которых нет своей структуры
            foreach($arInheritSections AS $obInheritSection){
                if(count($obInheritSection->parent_paths)){
                    //если у раздела есть родитель, то записываем первый попавшийся, 
                    //(в массиве сперва те, которые наследуются от раздела а затем уже которые наследуются от сущности)
                    $isInherited = false;
                    foreach($obInheritSection->parent_paths AS $parentPath => $true){
                        if($arParentSections[$parentPath]){
                            $obEntitySection = $arParentSections[$parentPath];
                           
                            if(!$arResult[$obInheritSection->entity_item_id]){
                                if(isset($arParams[$type][$relation][$obEntitySection->entity_item_id])){
                                    $arResult[$obInheritSection->entity_item_id] = $obEntitySection->entity_item_id;
                                    $isInherited = true;
                                    break;
                                }
                            }
                        }else{
                            if($userID){
                                $arBaseDisplay[] = $obInheritSection->entity_item_id;
                            }
                            break;
                        }
                    }
                    
                    if(!$isInherited){// наследуем от сущности
                        if(isset($arParams[$type][$relation][0])){
                            $arResult[$obInheritSection->entity_item_id] = 0;
                        }else{
                            if($userID){
                                $arBaseDisplay[] = $obInheritSection->entity_item_id;
                            }
                        }
                    }
                }else{ //если родителя нет, то наследуем от сущности
                    if($userID){
                        $arBaseDisplay[] = $obInheritSection->entity_item_id;
                    }
                }
            }
            
            if(count($arBaseDisplay)){
                $arResult+= static::getSectionInheritance($arBaseDisplay, 0, $relation, $type);
            }
            
            return $arResult;
        }
        
        return $userID ? static::getSectionInheritance($arSectionIDs, 0, $relation, $type) : array();
    }
    
    static public function getDisplayMap($entityID, $entitySectionID = 0, $userID = 0, $relation = EntityItem::TYPE_ELEMENT, $type = "list"){
        $getDefaultValues   = false;
        $userID             = (int)$userID;
        $entitySectionID    = (int)$entitySectionID;
        $params             = \CParam::get("admin_display:entity_" . $entityID, $userID);
        
        $arParentSections = EntitySectionTree::getParents($entitySectionID, "DESC");
        
        if($params){
            $arParams = CJSON::decode($params, true);
            
            if(isset($arParams[$type][$relation][$entitySectionID])){
                return $arParams[$type][$relation][$entitySectionID];
            }

            foreach($arParentSections AS $obParentSection){
                if(isset($arParams[$type][$relation][$obParentSection->entity_item_id])){
                    return $arParams[$type][$relation][$obParentSection->entity_item_id];
                }
            }
            
            if(isset($arParams[$type][$relation][0])){
                return $arParams[$type][$relation][0];
            }
        }
        
        if($userID){
            $params     = \CParam::get("admin_display:entity_" . $entityID, 0);
            $arParams   = CJSON::decode($params, true);
            
            if(isset($arParams[$type][$relation][$entitySectionID])){
                return $arParams[$type][$relation][$entitySectionID];
            }

            foreach($arParentSections AS $obParentSection){
                if(isset($arParams[$type][$relation][$obParentSection->entity_item_id])){
                    return $arParams[$type][$relation][$obParentSection->entity_item_id];
                }
            }
            
            if(isset($arParams[$type][$relation][0])){
                return $arParams[$type][$relation][0];
            }
        }
        
        $arDisplayDefault = array(
            "list"      => array( // 0 - entity without section_id
                array(
                    "isBase"    => 1,
                    "field"     => "entity_item_id",
                ),
                array(
                    "isBase"    => 1,
                    "field"     => "title",
                ),
                array(
                    "isBase"    => 1,
                    "field"     => "active",
                )
            ),
            "detail"    => array( //tabs
                array( //tab
                    "title" => "Общие",
                    "items" => array(
                        array(
                            "type"      => "field",
                            "isBase"    => 1,
                            "field"     => "entity_item_id"
                        ),
                        array(
                            "type"      => "field",
                            "isBase"    => 1,
                            "field"     => "title"
                        ),
                        array(
                            "type"      => "field",
                            "isBase"    => 1,
                            "field"     => "active"
                        ),
                        array(
                            "type"      => "field",
                            "isBase"    => 1,
                            "field"     => "description"
                        ),
                        array(
                            "type"      => "field",
                            "isBase"    => 1,
                            "field"     => "sections"
                        )
                    )
                )
            ),
            "filter"    => array(
                array(
                    "isBase"    => 1,
                    "field"     => "entity_item_id",
                ),
                array(
                    "isBase"    => 1,
                    "field"     => "title",
                ),
                array(
                    "isBase"    => 1,
                    "field"     => "active",
                ),
                array(
                    "isBase"    => 1,
                    "field"     => ($relation == EntityItem::TYPE_ELEMENT ? "sections" : "parent_id")
                )
            )
        );
        
        if($arDisplayDefault[$type]){
            return $arDisplayDefault[$type];
        }else{
            return array();
        }
    }
    
    static public function setDisplayMap($entityID, $entitySectionID = 0, $userID = 0, $relation = EntityItem::TYPE_ELEMENT, $type = "list", $arData = array()){
        $params = \CParam::get("admin_display:entity_" . $entityID, $userID);
        
        if($params){
            $arParams = CJSON::decode($params, true);
        }
        
        if(!is_array($arParams)){
            $arParams = array();
        }
        
        $arParams[$type][$relation][$entitySectionID] = $arData;
        
        \CParam::set("admin_display:entity_" . $entityID, CJSON::encode($arParams), $userID);
    }
    
    static public function deleteDisplayMap($entityID, $entitySectionID = 0, $userID = 0, $relation = EntityItem::TYPE_ELEMENT, $type = "list"){
        $userID             = (int)$userID;
        $entitySectionID    = (int)$entitySectionID;
        $params             = \CParam::get("admin_display:entity_" . $entityID, $userID);

        if($params){
            $arParams = CJSON::decode($params, true);
            
            unset($arParams[$type][$relation][$entitySectionID]);
            
            if(empty($arParams[$type][$relation])){
                unset($arParams[$type][$relation]);
            }
            
            if(empty($arParams[$type])){
                unset($arParams[$type]);
            }

            \CParam::set("admin_display:entity_" . $entityID, CJSON::encode($arParams), $userID);
        }
    }
}
?>