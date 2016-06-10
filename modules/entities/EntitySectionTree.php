<?
namespace Entities;

class EntitySectionTree extends \DB\Manager{
    static protected $_table    = "entity_section_tree";
    static protected $_pk       = "entity_item_id";
    
    /**
     * Разделитель сегментов в материализованном пути
     */
    const PATH_SEPARATOR = ".";
    
    static public function getTreeList($arParams = array(), $arValues = array()){
        if(!is_array($arParams)){
            $arParams = array("condition" => $arParams);
        }
        
        $arParams["order"]  = "entity_section_tree.path ASC";
        $arParams["join"]   = "INNER JOIN entity_item ON(entity_section_tree.entity_item_id=entity_item.entity_item_id)";

        $arEntitySections = static::findAll($arParams, $arValues);
        
        $countSections = count($arEntitySections);
        
        for($i=0;$i<$countSections-1;$i++){
            $a = &$arEntitySections[$i];
            $b = &$arEntitySections[$i + 1];
            
            if($a->parent_id == $b->parent_id && $a->priority > $b->priority){
                $tmp    = $b;
                $b      = $a;
                $a      = $tmp;
            }
        }

        return $arEntitySections;
    }
    
    static public function add($arData){
        $arReturn = array(
            "success" => false
        );
        
        if($arData["parent_id"] && ($obParentNode = static::findByPk($arData["parent_id"]))){
            $arData["path"]         = static::_getNextPath($obParentNode);
            $arData["depth_level"]  = $obParentNode->depth_level + 1;
        }else{
            $arData["path"]         = static::_getNextPath(false);
            $arData["depth_level"]  = 1;
        }
        
        $arData = static::getSafeFields($arData, array(
            "parent_id",
            "path",
            "depth_level",
            "entity_item_id"
        ));
        
        $entitySectionID = parent::add($arData);
        
        if($entitySectionID){
            \CEvent::trigger("ENTITY.SECTION_TREE.ADD", array($entitySectionID, $arData));
            
            $arReturn["itemID"]     = $entitySectionID;
            $arReturn["success"]    = true;
        }else{
            $arReturn["hasErrors"]  = true;
            $arReturn["errors"][]   = "Ошибка добавления данных";
        }
        
        return $arReturn;
    }
    
    static protected function _getNextPath($obParentNode = false){
        if($obParentNode){
            $obItem = static::find(
                array(
                    "condition" => "parent_id=?",
                    "order"     => "path DESC",
                    "select"    => "path"
                ), 
                array($obParentNode->{static::getPk()})
            );
            
            /*new node path*/
            if($obItem){
                $path = $obItem->path;
                
                if(($pos = strrpos($path, self::PATH_SEPARATOR)) !== false){
                    $newIndex   = substr($path, $pos + 2) + 1; //смещаемся на разделитель и цифру показывающую кол-во цифр в сегменте
                    $path       = substr($path, 0, $pos) . self::PATH_SEPARATOR . strlen($newIndex) . $newIndex;
                }
            }else{
                $path = $obParentNode->path . self::PATH_SEPARATOR . "11";
            }
            /*new node path*/
        }else{
            $obItem = static::find(array(
                "condition" => "depth_level=1",
                "order"     => "path DESC",
                "select"    => "path"
            ));
            
            if($obItem){
                $newIndex   = substr($obItem->path, 1) + 1; //смещаемся на разделитель и цифру показывающую кол-во цифр в сегменте
                $path       = strlen($newIndex) . $newIndex;
            }else{
                $path       = "11";
            }
        }
        
        return $path;
    }
    
    /**
     * Перемещение ноды $node_id под ноду $parent_id 
     * (то есть $parent_id нода становится родителем ноды $node_id, а не смежной нодой)
     */
    static public function setParent($entitySectionID, $parentID = false){
        $obEntitySection = static::findByPk($entitySectionID);
        
        if(!$obEntitySection){
            return false;
        }
        
        $connection = self::getConnection();
        
        if($parentID && ($obParentNode = static::findByPk($parentID))){
            //если ид родительской ноды совпадает с ид куда перемещаем
            //если у перемещаемой ноды совпадает путь с нодой в которую перемещаем, то получается зацикливание
            if($obEntitySection->parent_id == $parentID || $obEntitySection->path == $obParentNode->path){
                return false;
            }
            
            $path       = static::_getNextPath($obParentNode);
            $depthLevel = $obParentNode->depth_level - $obEntitySection->depth_level + 1;
        }else{
            $obItem = static::find(array(
                "condition" => "depth_level=1",
                "order"     => "path DESC",
                "select"    => "path"
            ));
            
            if($obItem){
                $newIndex   = substr($obItem->path, 1) + 1; //смещаемся на разделитель и цифру показывающую кол-во цифр в сегменте
                $path       = strlen($newIndex) . $newIndex;
            }else{
                $path       = "11";
            }
            
            $depthLevel = -$obEntitySection->depth_level + 1;
            $parentID        = 0;
        }
        
        $connection->query("LOCK tables " . static::getTable() . " WRITE");
        
        //обновляем уровень вложенности + путь у ноды и всех дочерних нод
        $sql = 'UPDATE ' . static::getTable() . ' SET
                depth_level=depth_level+?,
                path=CONCAT(?, SUBSTR(path, ?))
                WHERE path LIKE "' . $obEntitySection->path . '%"';
        
        $connection->query($sql, array($depthLevel, $path, strlen($obEntitySection->path) + 1));
        
        //обновляем pid ноды
        parent::updateByPk($obEntitySection->{static::getPk()}, array(
            "parent_id" => $parentID
        ));
        
        $connection->query("UNLOCK TABLES");
        
        \CEvent::trigger("ENTITY.SECTION_TREE.UPDATE", array($entitySectionID, $parentID));
    }
    
    static public function getParents($entitySectionID, $sort = "ASC"){
        if(!$entitySectionID){
            return array();
        }
        
        $obEntitySection = static::findByPk($entitySectionID);
        
        if(!$obEntitySection){
            return false;
        }
        
        $arResult   = array();
        $arPaths    = array();
        $path       = $obEntitySection->path;
       
        while(($pos = strrpos($path, self::PATH_SEPARATOR)) !== false){
            $path = substr($path, 0, $pos);
            $arPaths[] = $path;
        }
        
        if(count($arPaths)){
            $arResult = static::findAll(array(
                "order"     => "path " . ($sort == "DESC" ? "DESC" : "ASC"),
                "condition" => 'path IN("' . implode('", "', $arPaths) . '")'
            ));
        }
        
        return $arResult;
    }
    
    static public function getChilds($entitySectionID, $withNode = false){
        $obEntitySection = static::findByPk($entitySectionID);
        
        if(!$obEntitySection){
            return false;
        }
        
        return static::findAll(array(
            "order"     => "path ASC",
            "condition" => 'path LIKE "' . $obEntitySection->path . ($withNode ? "" : ".") . '%"'
        ));
    }
    
    static public function refreshNodes($parentID = 0){
        $arNodes = static::findAll(
            array(
                "condition" => "parent_id=?",
                "order"     => static::getPk() . " ASC"
            ), 
            array($parentID)
        );
        
        $basePath   = "";
        $depthLevel = 1;
        
        if($parentID && ($obParentNode = static::findByPk($parentID))){
            $basePath   = $obParentNode->path . self::PATH_SEPARATOR;
            $depthLevel+= substr_count($basePath, self::PATH_SEPARATOR);
        }
        
        $pk     = static::getPk();
        $now    = new \DB\Expr("NOW()");
        
        foreach($arNodes AS $index => $obEntitySection){
            parent::updateByPk($obEntitySection->{$pk}, array(
                "depth_level"   => $depthLevel,
                "path"          => $basePath . strlen($index) . $index,
                "date_update"   => $now
            ));
            
            static::refreshNodes($obEntitySection->{$pk});
        }
    }
    
    static public function deleteByPk($entitySectionID){        
        $obEntitySection = static::findByPk($entitySectionID);
        
        if($obEntitySection){
            $connection = self::getConnection();
            
            $connection->query("LOCK tables " . static::getTable() . " WRITE");
            
            $connection->query('DELETE FROM ' . static::getTable() . ' 
                                WHERE path LIKE "' . $obEntitySection->path . '%"');

            $connection->query("UNLOCK TABLES");
            
            return $result;
        }
    }
}
?>