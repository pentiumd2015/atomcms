<?
namespace Entities\EntityFieldTypes;
use \Entities\EntityItemFieldValue;
use \Helpers\CArrayHelper;
use \View\CView;
use \Entities\EntityFieldVariant;

class FieldTypeList extends FieldType{
    const VALUE_NAME = "value_num";
    
    public $obEntityField;
    
    public function __construct($obEntityField){
        $this->obEntityField = $obEntityField;
    }
    
    public function getInfo(){
        return array(
            "title" => "Список"
        );
    }
    
    public function renderParams(){
        $containerID    = uniqid("f" . $this->obEntityField->entity_field_id);
        
        $arData = $this->getData();
        
        if($arData["arRequestField"]["is_multi"]){
            $arViews = array(
                "multiselect"   => "Выпадающий список",
                "checkbox"      => "Флажки",
            );
        }else{
            $arViews = array(
                "select"        => "Выпадающий список",
                "radio"         => "Переключатели",
            );
        }
        
        if(isset($this->obEntityField->params["view"])){
            $currentView = $this->obEntityField->params["view"];
        }else{
            $currentView = key($arViews);
        }
        
        /*Variants*/
        $arFieldVariants = EntityFieldVariant::findAll(array(
            "condition" => "entity_field_id=?",
            "order"     => "priority ASC"
        ), array($this->obEntityField->entity_field_id));
        /*Variants*/
        
        $obView = new CView;
        $obView->setData(array(
            "containerID"       => $containerID,
            "arParams"          => $this->obEntityField->params,
            "arViews"           => $arViews,
            "arErrors"          => $this->arPrepareData["arErrors"],
            "currentView"       => $currentView,
            "arFieldVariants"   => $arFieldVariants,
            "arRequestField"    => $arData["arRequestField"]
        ));
        
        return $obView->getContent(__DIR__ . "/view/list/params.php");
    }
    
    public function renderList(){
        $arData     = $this->getData();        
        $itemID     = $arData["arItem"]["entity_item_id"];
        $itemURL    = $arData["itemURL"];
        
        if($this->arPrepareData["arValues"][$itemID]){
            $str = "";
            
            foreach($this->arPrepareData["arValues"][$itemID] AS $valueID => $obFieldVariant){
                $str.= $obFieldVariant->title . ", ";
            }
            
            return rtrim($str, ", ");
        }
        
        return "";
    }
    
    public function prepareDetailData($arData = array()){
        $this->arPrepareData = $arData;
        
        /*Variants*/
        $arFieldVariants = EntityFieldVariant::findAll(array(
            "condition" => "entity_field_id=?",
            "order"     => "priority ASC"
        ), array($this->obEntityField->entity_field_id));
        /*Variants*/
        
        $this->arPrepareData["arFieldVariants"] = $arFieldVariants;
    }
    
    public function prepareListData($arData = array()){ //получаем для всех элементов для этого поля варианты списка
        $this->arPrepareData = $arData; //  [itemID][itemFieldValueID] ... value
        
        $arResult = array();
        
        if(is_array($arData["arValues"])){
            /*Variants*/
            $arFieldVariants = EntityFieldVariant::findAll(array(
                "condition" => "entity_field_id=?",
                "order"     => "priority ASC"
            ), array($this->obEntityField->entity_field_id));
            /*Variants*/
            
            $arFieldVariants = CArrayHelper::index($arFieldVariants, "entity_field_variant_id");
            
            foreach($arData["arValues"] AS $entityItemID => $arFieldValues){
                foreach($arFieldValues AS $fieldValueID => $arValue){
                    $arResult[$entityItemID][$fieldValueID] = $arFieldVariants[$arValue[self::VALUE_NAME]];
                }
            }
        }
        
        $this->arPrepareData["arValues"] = $arResult;
    }
    
    public function renderDetail(){
        $obView = new CView;
        $obView->setData(array(
            "containerID"       => uniqid("f" . $this->obEntityField->entity_field_id),
            "obFieldType"       => $this,
            "arValues"          => $this->arPrepareData["arValues"],
            "arErrors"          => $this->arPrepareData["arErrors"],
            "arFieldVariants"   => $this->arPrepareData["arFieldVariants"],
        ));
        
        return $obView->getContent(__DIR__ . "/view/list/detail.php");
    }
    
    public function checkValues($arValues){
        $arReturn = array("success" => 0);

        $arErrors = array();
        
        $arValues = array_filter($arValues, function($arItem){
            return (strlen($arItem["value"]) > 0);
        });
        
        if($this->obEntityField->is_required){
            $hasValue = false;
            
            foreach($arValues AS $arValue){
                if(strlen($arValue["value"])){
                    $hasValue = true;
                    break;
                }
            }
            
            if(!$hasValue){
                $arErrors[] = "Поле обязательно для заполнения";
            }
        }
        
        if($this->obEntityField->is_unique){
            $arTmp = array();
            
            foreach($arValues AS $arValue){
                $arTmp[$arValue["value"]] = 1;
            }
            
            if(count($arTmp) != count($arValues)){
                $arErrors[] = "Значения поля должны быть уникальными";
            }
            
            unset($arTmp);
        }
        
        if(!count($arErrors)){
            $arReturn["success"] = 1;
        }else{
            $arReturn["errors"] = $arErrors;
        }
        
        return $arReturn;
    }
    
    public function setValues($entityItemID, $arValues = array()){
        $arFieldValues = array();

        foreach($arValues AS $arValue){
            if(is_numeric($arValue["value"])){
                $arFieldValues[] = $arValue["value"];
            }
        }
        
        //удаляем значения, которые не были переданы
        $deleteSQL = "entity_item_id=? AND entity_field_id=?";
        
        if(count($arFieldValues)){
            $deleteSQL.= " AND " . self::VALUE_NAME . " NOT IN(" . implode(", ", $arFieldValues) . ")";
        }
        
        EntityItemFieldValue::delete($deleteSQL, array($entityItemID, $this->obEntityField->entity_field_id));

        if(count($arFieldValues)){
            $arItemFieldValues = EntityItemFieldValue::findAll("entity_item_id=? AND entity_field_id=?", array($entityItemID, $this->obEntityField->entity_field_id));
            $arItemFieldValues = CArrayHelper::index($arItemFieldValues, self::VALUE_NAME);
            
            foreach($arFieldValues AS $value){
                $arData = array(
                    "entity_item_id"    => $entityItemID,
                    "entity_field_id"   => $this->obEntityField->entity_field_id,
                    self::VALUE_NAME    => $value
                );
                
                $obItemFieldValue = $arItemFieldValues[$value];
                
                if($obItemFieldValue){
                    EntityItemFieldValue::updateByPk($obItemFieldValue->entity_item_field_value_id, $arData);
                }else{
                    EntityItemFieldValue::add($arData);
                }
            }
        }
    }
    
    public function getSqlParams($arSqlParams = array(), $arParams = array(), $type){
        switch($type){
            case "list.sort":
                $arParams["by"] = $arParams["by"] == "DESC" ? "DESC" : "ASC" ;
                $arSqlParams["params"]["join"][] = "LEFT JOIN entity_item_field_value t2 ON(t1.entity_item_id=t2.entity_item_id AND t2.entity_field_id=" . $this->obEntityField->entity_field_id . ")";
                $arSqlParams["params"]["join"][] = "LEFT JOIN entity_field_variant t3 ON(t2.entity_field_id=t3.entity_field_id AND t2." . self::VALUE_NAME . "=t3.entity_field_variant_id)";
                $arSqlParams["params"]["group"]  = "t1.entity_item_id";
                $arSqlParams["params"]["order"]  = "t3.title " . $arParams["by"];
                break;
            case "list.filter":
                if(is_string($arParams["value"]) && strlen($arParams["value"])){
                    $arSqlParams["params"]["condition"].= "\nAND t1.entity_item_id IN(SELECT entity_item_id
                                                                                      FROM entity_item_field_value 
                                                                                      WHERE entity_field_id=" . $this->obEntityField->entity_field_id . "
                                                                                      AND " . self::VALUE_NAME . " IN(SELECT entity_field_variant_id
                                                                                                                      FROM entity_field_variant
                                                                                                                      WHERE title LIKE ?))";
                    $arSqlParams["statements"][] = "%" . $arParams["value"] . "%";
                }
                break;
        }
        
        return $arSqlParams;
    }
}
?>