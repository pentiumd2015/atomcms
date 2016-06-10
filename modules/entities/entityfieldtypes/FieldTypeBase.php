<?
namespace Entities\EntityFieldTypes;
use \Entities\EntityField;
use \Entities\EntityItem;
use \Entities\EntitySectionElement;
use \Helpers\CHtml;
use \Helpers\CBuffer;
use \Helpers\CArrayHelper;

class FieldTypeBase extends FieldType{
    protected $obEntity;
    protected $relation;
    
    public function __construct($obEntity, $relation = EntityItem::TYPE_ELEMENT){
        $this->obEntity = $obEntity;
        $this->relation = $relation;
    }
    
    public function getInfo(){
        $arFields = array();
        
        $arFields = array(
            "entity_item_id"    => array(
                "title"         => "ID",
                "is_multi"      => 0
            ),
            "title"     => array(
                "title"         => "Название",
                "description"   => "Описание поля",
                "is_multi"      => 0
            ),
            "active"    => array(
                "title"     => "Активность",
                "is_multi"  => 0
            ),
            "description"    => array(
                "title"     => "Описание",
                "is_multi"  => 0
            ),
        );
        
        if($this->relation == EntityItem::TYPE_ELEMENT && $this->obEntity->use_sections){
            $arFields["sections"] = array(
                "title"         => "Родительский раздел",
                "description"   => "Описание поля",
                "is_multi"      => 1
            );
        }else if($this->relation == EntityItem::TYPE_SECTION){
            $arFields["parent_id"] = array(
                "title"         => "Родительский раздел",
                "description"   => "Описание поля",
                "is_multi"      => 0
            );
        }
        
        return $arFields;
    }
    
    public function prepareListData($arData = array()){
        $this->arPrepareData = $arData;
        
        switch($this->arPrepareData["fieldName"]){
            case "sections":
                if($this->relation != EntityItem::TYPE_ELEMENT || !$this->obEntity->use_sections){
                    return;
                }
                
                if($this->arPrepareData["arItems"]){
                    $this->arPrepareData["arItems"] = \Helpers\CArrayHelper::index($this->arPrepareData["arItems"], "entity_item_id");
                    
                    $arEntityItemIDs = array_keys($this->arPrepareData["arItems"]);
                }
                
                $arEntitySections = array();
                
                if($arEntityItemIDs){
                    $arEntitySections = \Entities\EntitySectionElement::findAll(array(
                        "select"    => "t1.entity_section_id, t2.*, t1.entity_element_id",
                        "alias"     => "t1",
                        "join"      => "INNER JOIN entity_item t2 ON(t2.entity_item_id=t1.entity_section_id)",
                        "condition" => "t1.entity_element_id IN(" . implode(", ", $arEntityItemIDs) . ")"
                    ));
                    
                    $arEntitySections = \Helpers\CArrayHelper::index($arEntitySections, "entity_element_id", true);
                }
                
                $this->arPrepareData["arEntitySections"] = $arEntitySections;
                break;
        }
    }
    
    public function prepareDetailData($arData = array()){
        $this->arPrepareData = $arData;
        
        switch($arData["fieldName"]){
            case "sections": //если поле раздел, то достанем предварительно весь список разделов, а также разделы, которые отмечены у элемента
                if($this->relation == EntityItem::TYPE_ELEMENT && $this->obEntity->use_sections){
                    $arItem                             = $this->arPrepareData["arItem"];
                    $this->arPrepareData["arSections"]  = \Entities\EntitySectionTree::getTreeList("entity_id=?", array($this->obEntity->entity_id));
                    $arSectionOptionsList               = array("" => "Не выбран");
    
                    foreach($this->arPrepareData["arSections"] AS $obEntitySection){
                        $arSectionOptionsList[$obEntitySection->entity_item_id] = str_repeat("   -", $obEntitySection->depth_level - 1) . $obEntitySection->title;
                    }
                    
                    $this->arPrepareData["arSectionOptions"] = $arSectionOptionsList;
                    $this->arPrepareData["arChosenSections"] = \Entities\EntitySectionElement::findAll("entity_element_id=?", array($arItem["entity_item_id"]));
                    $this->arPrepareData["arChosenSections"] = CArrayHelper::getColumn($this->arPrepareData["arChosenSections"], "entity_section_id");
                            
                    if(!count($this->arPrepareData["arChosenSections"])){
                        $this->arPrepareData["arChosenSections"] = array("");
                    }
                }
                
                break;
            case "parent_id": //если поле раздел, то достанем предварительно весь список разделов, а также разделы, которые отмечены у элемента
                if($this->relation == EntityItem::TYPE_SECTION){
                    $arItem                             = $this->arPrepareData["arItem"];
                    $this->arPrepareData["arSections"]  = \Entities\EntitySectionTree::getTreeList("entity_item.entity_id=? AND entity_item.entity_item_id!=?", array($this->obEntity->entity_id, $arItem["entity_item_id"]));
                    $arSectionOptionsList               = array(0 => "Нет");
    
                    foreach($this->arPrepareData["arSections"] AS $obEntitySection){
                        $arSectionOptionsList[$obEntitySection->entity_item_id] = str_repeat("   -", $obEntitySection->depth_level - 1) . $obEntitySection->title;
                    }
                    
                    $this->arPrepareData["arSectionOptions"]= $arSectionOptionsList;
                    $this->arPrepareData["arSectionTree"]   = \Entities\EntitySectionTree::findByPk($arItem["entity_item_id"]);
                }
                
                break;
        }
    }
    
    public function renderDetail(){
        $arData = $this->getData();
        $arInfo = $this->getInfo();

        CBuffer::start();
            $fieldName = $this->arPrepareData["fieldName"];
            
            switch($fieldName){
                case "entity_item_id":
                    if(!$this->arPrepareData["arItem"][$fieldName]){
                        return "";
                    }
                    ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"><?=$arInfo[$fieldName]["title"]?>:</label>
                            <div class="col-sm-6">
                                <div style="padding: 8px 0px;"><?=$this->arPrepareData["arItem"][$fieldName];?></div>
                            </div>
                        </div>
                    <?
                    break;
                case "active":
                    ?>
                        <div class="form-group<?=($this->arPrepareData["errors"][$fieldName] ? " has-error" : "")?>">
                            <label class="col-sm-2 control-label" for="entity_item_active"><?=$arInfo[$fieldName]["title"]?></label>
                            <div class="col-sm-6">
                                <div class="checkbox checkbox-primary">
                                    <?=CHtml::boolean("entity_item[" . $fieldName . "]", array(1, 0), $this->arPrepareData["arItem"][$fieldName], array(
                                        "id" => "entity_item_active"
                                    ));?>
                                    <label for="entity_item_active"></label>
                                </div>
                                
                            </div>
                        </div>
                    <?
                    break;
                case "title":
                    ?>
                        <div class="form-group<?=($this->arPrepareData["errors"][$fieldName] ? " has-error" : "")?>">
                            <label class="col-sm-2 control-label"><?=$arInfo[$fieldName]["title"]?>:<span class="mandatory">*</span></label>
                            <div class="col-sm-6">
                                <?=CHtml::text("entity_item[" . $fieldName . "]", $this->arPrepareData["arItem"][$fieldName], array(
                                    "class" => "form-control"
                                ));?>
                            </div>
                        </div>
                    <?
                    break;
                case "description":
                    ?>
                        <div class="form-group<?=($this->arPrepareData["errors"][$fieldName] ? " has-error" : "")?>">
                            <label class="col-sm-2 control-label"><?=$arInfo[$fieldName]["title"]?>:</label>
                            <div class="col-sm-6">
                                <?=CHtml::textarea("entity_item[" . $fieldName . "]", $this->arPrepareData["arItem"][$fieldName], array(
                                    "class" => "form-control",
                                    "style" => "resize:vertical;height: 135px;"
                                ));?>
                            </div>
                        </div>
                    <?
                    break;
                case "sections":
                    if($this->relation == EntityItem::TYPE_ELEMENT && $this->obEntity->use_sections){
                        ?>
                            <div class="form-group<?=($this->arPrepareData["errors"][$fieldName] ? " has-error" : "")?>">
                                <label class="col-sm-2 control-label"><?=$arInfo[$fieldName]["title"]?>: </label>
                                <div class="col-sm-6">
                                    <?=CHtml::multiselect("entity_item[" . $fieldName . "]", $this->arPrepareData["arSectionOptions"], $this->arPrepareData["arChosenSections"], array(
                                        "class" => "form-control"
                                    ));?>
                                    <?
                                        if($arInfo[$fieldName]["description"]){
                                            ?>
                                                <span class="help-block"><?=$arInfo[$fieldName]["description"];?></span>
                                            <?
                                        }
                                    ?>
                                </div>
                            </div>
                        <?
                    }
                    
                    break;
                case "parent_id":
                    if($this->relation == EntityItem::TYPE_SECTION){
                       // p($this->arPrepareData["arItem"]);
                        ?>
                            <div class="form-group<?=($this->arPrepareData["errors"][$fieldName] ? " has-error" : "")?>">
                                <label class="col-sm-2 control-label"><?=$arInfo[$fieldName]["title"]?>: </label>
                                <div class="col-sm-6">
                                    <?=CHtml::select("entity_item[" . $fieldName . "]", $this->arPrepareData["arSectionOptions"], $this->arPrepareData["arSectionTree"]->parent_id, array(
                                        "class" => "form-control"
                                    ));?>
                                    <?
                                        if($arInfo[$fieldName]["description"]){
                                            ?>
                                                <span class="help-block"><?=$arInfo[$fieldName]["description"];?></span>
                                            <?
                                        }
                                    ?>
                                </div>
                            </div>
                            <style>
                            select[name="entity_item[sections][]"] option{
                                padding: 5px;
                            }
                            </style>
                        <?
                    }
                    
                    break;
            }
        
        return CBuffer::end();
    }
    
    public function renderList(){
        $arData = $this->getData();
        $arItem = $arData["arItem"];
        
        $fieldName = $this->arPrepareData["fieldName"];
        
        switch($fieldName){
            case "entity_item_id":
                return $arItem[$fieldName];
                break;
            case "active":
                return $arItem[$fieldName] ? '<span class="label label-success">Да</span>' : '<span class="label label-warning">Нет</span>' ;
                break;
            case "title":
                return '<a href="' . $arData["itemURL"] . '">' . $arItem[$fieldName] . '</a>';
                break;
            case "description":
                return $arItem[$fieldName];
                break;
            case "sections":
                if($this->relation == EntityItem::TYPE_ELEMENT){
                    if($this->arPrepareData["arEntitySections"][$arItem["entity_item_id"]]){
                        $str = "";
                        
                        foreach($this->arPrepareData["arEntitySections"][$arItem["entity_item_id"]] AS $obSection){
                            $str.= '<span class="label label-primary" style="margin-top: 5px;">' . $obSection->title . '</span> &nbsp;';
                        }
                        
                        return $str;
                    }else{
                        return '-';
                    }
                }
            
                break;
            case "parent_id":
                if($this->relation == EntityItem::TYPE_SECTION){
                    if($this->arPrepareData["arEntitySections"][$arItem["entity_item_id"]]){
                        $obSection = $this->arPrepareData["arEntitySections"][$arItem["entity_item_id"]];
                        
                        return '<span class="label label-primary">' . $obSection->title . '</span>';
                    }else{
                        return '-';
                    }
                }
            
                break;
        }
        
        return "";
    }
    
    public function renderFilter(){
        $arData = $this->getData();
        $arInfo = $this->getInfo();
        
        CBuffer::start();
            $fieldName = $this->arPrepareData["fieldName"];
            
            switch($fieldName){
                case "entity_item_id":
                    $val = (string)$arData["value"];
                    ?>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"><?=$arInfo[$fieldName]["title"]?>:</label>
                            <div class="col-sm-9">
                                <?=CHtml::text("f[" . $fieldName . "]", $val, array(
                                    "class" => "form-control input-sm"
                                ));?>
                                <span class="help-block">пример: 15 ; 1-69 ; 1,56 ; -54 ; 126-</span>
                            </div>
                        </div>
                    <?
                    break;
                case "active":
                    $val = (string)$arData["value"];
                    ?>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="list_filter_active"><?=$arInfo[$fieldName]["title"]?>:</label>
                            <div class="col-sm-9">
                                <div class="radio radio-inline radio-primary">
                                    <?=CHtml::radio("f[" . $fieldName . "]", ($val == 1), array(
                                        "id"    => "list_filter_active_1",
                                        "value" => 1
                                    ));?>
                                    <label for="list_filter_active_1">Да</label>
                                </div>
                                <div class="radio radio-inline radio-primary">
                                    <?=CHtml::radio("f[" . $fieldName . "]", (strlen($val) && $val == 0), array(
                                        "id"    => "list_filter_active_0",
                                        "value" => 0
                                    ));?>
                                    <label for="list_filter_active_0">Нет</label>
                                </div>
                                <div class="radio radio-inline radio-primary">
                                    <?=CHtml::radio("f[" . $fieldName . "]", (strlen($val) == 0), array(
                                        "id"    => "list_filter_active",
                                        "value" => ""
                                    ));?>
                                    <label for="list_filter_active">Не важно</label>
                                </div>
                            </div>
                        </div>
                    <?
                    break;
                case "title":
                    $val = (string)$arData["value"];
                    ?>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"><?=$arInfo[$fieldName]["title"]?>:</label>
                            <div class="col-sm-9">
                                <?=CHtml::text("f[" . $fieldName . "]", $val, array(
                                    "class" => "form-control input-sm"
                                ));?>
                            </div>
                        </div>
                    <?
                    break;
                case "sections":
                    if($this->obEntity->use_sections){
                        $val = (array)$arData["value"];
                        
                        $arSections = \Entities\EntitySectionTree::getTreeList("entity_id=?", array($this->obEntity->entity_id));
                        $arSectionOptionsList = array("" => "Не выбран");
                       
                        foreach($arSections AS $obEntitySection){
                            $arSectionOptionsList[$obEntitySection->entity_item_id] = str_repeat("   -", $obEntitySection->depth_level - 1) . $obEntitySection->title;
                        }
                        ?>
                            <div class="form-group">
                                <label class="col-sm-3 control-label"><?=$arInfo[$fieldName]["title"]?>: </label>
                                <div class="col-sm-9">
                                    <?=CHtml::select("f[" . $fieldName . "][id]", $arSectionOptionsList, $val["id"], array(
                                        "class" => "form-control"
                                    ));?>
                                    <div class="checkbox checkbox-primary">
                                        <?=CHtml::checkbox("f[" . $fieldName . "][sub]", ($val["sub"] == 1), array(
                                            "id"    => "list_filter_sections_sub",
                                            "value" => 1
                                        ));?>
                                        <label for="list_filter_sections_sub">Искать в подразделах</label>
                                    </div>
                                </div>
                            </div>
                        <?
                    }
                    break;
                case "parent_id":
                    if($this->relation == EntityItem::TYPE_SECTION){
                        $val = (array)$arData["value"];
                        
                        $arSections = \Entities\EntitySectionTree::getTreeList("entity_id=?", array($this->obEntity->entity_id));
                        $arSectionOptionsList = array("" => "Не выбран");
                       
                        foreach($arSections AS $obEntitySection){
                            $arSectionOptionsList[$obEntitySection->entity_item_id] = str_repeat("   -", $obEntitySection->depth_level - 1) . $obEntitySection->title;
                        }
                        ?>
                            <div class="form-group">
                                <label class="col-sm-3 control-label"><?=$arInfo[$fieldName]["title"]?>: </label>
                                <div class="col-sm-9">
                                    <?=CHtml::select("f[" . $fieldName . "][id]", $arSectionOptionsList, $val["id"], array(
                                        "class" => "form-control"
                                    ));?>
                                    <div class="checkbox checkbox-primary">
                                        <?=CHtml::checkbox("f[" . $fieldName . "][sub]", ($val["sub"] == 1), array(
                                            "id"    => "list_filter_sections_sub",
                                            "value" => 1
                                        ));?>
                                        <label for="list_filter_sections_sub">Отображать подразделы</label>
                                    </div>
                                </div>
                            </div>
                        <?
                    }
                    break;
            }
        
        return CBuffer::end();
    }
    
    public function getSqlParams($arSqlParams = array(), $arParams = array(), $type){
        switch($type){
            case "list.sort":
                $arParams["by"] = $arParams["by"] == "DESC" ? "DESC" : "ASC" ;
                
                switch($arParams["fieldName"]){
                    case "entity_item_id":
                    case "title":
                    case "active":
                    case "description":
                        $arSqlParams["params"]["order"] = "t1." . $arParams["fieldName"] . " " . $arParams["by"] ;
                        break;
                    
                }
                break;
            case "list.filter":
                    switch($arParams["fieldName"]){
                        case "entity_item_id":
                        case "entity_section_id":
                            if(strpos($arParams["value"], "-") !== false){
                                list($idFrom, $idTo) = explode("-", $arParams["value"], 2);
                                
                                if($idFrom && $idTo){
                                    $idFrom = (int)$idFrom;
                                    $idTo   = (int)$idTo;
                                    
                                    if($idFrom > $idTo){
                                        $tmpID  = $idTo;
                                        $idTo   = $idFrom;
                                        $idFrom = $tmpID;
                                    }
                                    
                                    $arSqlParams["params"]["condition"].= "\nAND (t1." . $arParams["fieldName"] . ">=" . $idFrom . " AND t1." . $arParams["fieldName"] . "<=" . $idTo . ")";
                                }else if($idFrom){
                                    $arSqlParams["params"]["condition"].= "\nAND (t1." . $arParams["fieldName"] . ">=" . (int)$idFrom . ")";
                                }else if($idTo){
                                    $arSqlParams["params"]["condition"].= "\nAND (t1." . $arParams["fieldName"] . "<=" . (int)$idTo . ")";
                                }
                            }else if(strpos($arParams["value"], ",") !== false){
                                 $arItemIDs = explode(",", $arParams["value"]);
                                 array_walk($arItemIDs, "trim");
                                 array_walk($arItemIDs, "intval");
                                 
                                 $arItemIDs = array_filter($arItemIDs);
                                 
                                 if(count($arItemIDs)){
                                    $arSqlParams["params"]["condition"].= "\nAND (t1." . $arParams["fieldName"] . " IN(" . implode(", ", $arItemIDs) . "))";
                                 }
                            }else if($arParams["value"]){
                                $itemID = (int)$arParams["value"];
                                
                                $arSqlParams["params"]["condition"].= "\nAND (t1." . $arParams["fieldName"] . "=?)";
                                $arSqlParams["statements"][] = $itemID;
                            }
                            break;
                        case "active":
                            if(strlen($arParams["value"]) && ($arParams["value"] == 1 || $arParams["value"] == 0)){
                                $arSqlParams["params"]["condition"].= "\nAND t1." . $arParams["fieldName"] . "=?";
                                $arSqlParams["statements"][] = $arParams["value"];
                            }
                            break;
                        case "title":
                        case "description":
                            if(is_string($arParams["value"]) && strlen($arParams["value"])){
                                $arSqlParams["params"]["condition"].= "\nAND t1." . $arParams["fieldName"] . " LIKE ?";
                                $arSqlParams["statements"][] = "%" . $arParams["value"] . "%";
                            }
                            break;
                        case "sections":
                            if($this->relation == EntityItem::TYPE_ELEMENT){
                                $sectionID = (int)$arParams["value"]["id"];
                                
                                $useSubSections = (boolean)$arParams["value"]["sub"];
                                
                                if($sectionID){
                                    if($useSubSections){
                                        $arSections = \Entities\EntitySectionTree::getChilds($sectionID, true);
                                        
                                        if($arSections){
                                            $arSectionIDs = \Helpers\CArrayHelper::getColumn($arSections, "entity_item_id");
                                            
                                            $arSqlParams["params"]["condition"].= "\nAND t1.entity_item_id IN(SELECT entity_element_id
                                                                                                              FROM entity_section_element
                                                                                                              WHERE entity_section_id IN(" . implode(", ", $arSectionIDs) . "))";
                                        }
                                    }else{
                                        $arSqlParams["params"]["condition"].= "\nAND t1.entity_item_id IN(SELECT entity_element_id
                                                                                                          FROM entity_section_element
                                                                                                          WHERE entity_section_id=?)";
                                        $arSqlParams["statements"][] = $sectionID;
                                    }
                                }
                            }
                            break;
                        case "parent_id":
                            if($this->relation == EntityItem::TYPE_SECTION){
                                $sectionID = (int)$arParams["value"]["id"];
                                
                                $useSubSections = (boolean)$arParams["value"]["sub"];
                                
                                if($sectionID){
                                    if($useSubSections){
                                        $arSections = \Entities\EntitySectionTree::getChilds($sectionID);
                                        
                                        if($arSections){
                                            $arSectionIDs = \Helpers\CArrayHelper::getColumn($arSections, "entity_item_id");
                                            
                                            $arSqlParams["params"]["condition"].= "\nAND t1.entity_item_id IN(" . implode(", ", $arSectionIDs) . ")";
                                        }
                                    }else{
                                        $arSqlParams["params"]["condition"].= "\nAND t1.entity_item_id IN(SELECT entity_item_id
                                                                                                          FROM entity_section_tree
                                                                                                          WHERE parent_id=?)";
                                        $arSqlParams["statements"][] = $sectionID;
                                    }
                                }
                            }
                            break;
                    }
                break;
        }

        return $arSqlParams;
    }
}
?>