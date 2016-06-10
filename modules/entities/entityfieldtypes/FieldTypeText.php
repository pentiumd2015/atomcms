<?
namespace Entities\EntityFieldTypes;
use \View\CView;

class FieldTypeText extends FieldType{
    const VALUE_NAME = "value_text";
    
    public $obEntityField;
    
    public function __construct($obEntityField){
        $this->obEntityField = $obEntityField;
    }
    
    public function getInfo(){
        return array(
            "title"         => "Текст",
            "description"   => "Тест2"
        );
    }
    
    public function renderParams(){
        $arInfo     = $this->getInfo();
        $arParams   = $this->getParams();
        
        $obView = new CView;
        $obView->setData(array(
            "arInfo"    => $arInfo,
            "arParams"  => $arParams
        ));
        
        return $obView->getContent(__DIR__ . "/view/string/params.php");
    }
    
    public function renderList(){
        $arData     = $this->getData();        
        $itemID     = $arData["arItem"]["entity_item_id"];
        $itemURL    = $arData["itemURL"];

        if($this->arPrepareData["arValues"][$itemID]){
            $str = "";
            
            foreach($this->arPrepareData["arValues"][$itemID] AS $valueID => $value){
                $str.= $value . ", ";
            }
            
            return rtrim($str, ", ");
        }
        
        return "";
    }
    
    public function prepareDetailData($arData = array()){
        $this->arPrepareData = $arData;
    }
    
    public function prepareListData($arData = array()){
        $this->arPrepareData = $arData;
        
        
        $arResult = array();
        
        if(is_array($arData["arValues"])){
            foreach($arData["arValues"] AS $entityItemID => $arFieldValues){
                foreach($arFieldValues AS $fieldValueID => $arValue){
                    $arResult[$entityItemID][$fieldValueID] = $arValue[self::VALUE_NAME];
                }
            }
        }
        
        $this->arPrepareData["arValues"] = $arResult;
    }
    
    public function renderDetail(){
        $obView = new CView;
        $obView->setData(array(
            "containerID"   => uniqid("f" . $this->obEntityField->entity_field_id),
            "obFieldType"   => $this,
            "arValues"      => $this->arPrepareData["arValues"],
            "arErrors"      => $this->arPrepareData["arErrors"],
        ));
        
        return $obView->getContent(__DIR__ . "/view/text/detail.php");
    }
    
    public function checkValues($arValues){ //check if is section
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
    
    public function setValues($entityItemID, $arValues = array()){ //check if is section
        $arFieldValueIDs    = array();
        
        foreach($arValues AS &$arValue){
            if($arValue["entity_item_field_value_id"]){
                $arValue["entity_item_field_value_id"]                      = (int)$arValue["entity_item_field_value_id"];
                $arFieldValueIDs[$arValue["entity_item_field_value_id"]]    = 1;
            }
        }
        
        unset($arValue);
        
        //удаляем значения, которые не были переданы
        $deleteSQL = "entity_item_id=? AND entity_field_id=?";
        
        if(count($arFieldValueIDs)){
            $deleteSQL.= " AND entity_item_field_value_id NOT IN(" . implode(", ", array_keys($arFieldValueIDs)) . ")";
        }
        
        \Entities\EntityItemFieldValue::delete($deleteSQL, array($entityItemID, $this->obEntityField->entity_field_id));

        foreach($arValues AS $arValue){
            $arData = array(
                "entity_item_id"    => $entityItemID,
                "entity_field_id"   => $this->obEntityField->entity_field_id,
                self::VALUE_NAME    => $arValue["value"]
            );
            
            if($arValue["entity_item_field_value_id"]){
                \Entities\EntityItemFieldValue::updateByPk($arValue["entity_item_field_value_id"], $arData);
            }else{
                \Entities\EntityItemFieldValue::add($arData);
            }
        }
    }
    
    public function getSqlParams($arSqlParams = array(), $arParams = array(), $type){
        switch($type){
            case "list.sort":
                $arParams["by"] = $arParams["by"] == "DESC" ? "DESC" : "ASC" ;
                $arSqlParams["params"]["join"][] = "LEFT JOIN entity_item_field_value t2 ON(t1.entity_item_id=t2.entity_item_id AND t2.entity_field_id=" . $this->obEntityField->entity_field_id . ")";
                $arSqlParams["params"]["group"]  = "t1.entity_item_id";
                $arSqlParams["params"]["order"]  = "t2." . self::VALUE_NAME . " " . $arParams["by"];
                break;
            case "list.filter":
                if(is_string($arParams["value"]) && strlen($arParams["value"])){
                    $arSqlParams["params"]["condition"].= "\nAND t1.entity_item_id IN(SELECT entity_item_id
                                                                                      FROM entity_item_field_value 
                                                                                      WHERE entity_field_id=" . $this->obEntityField->entity_field_id . "
                                                                                      AND " . self::VALUE_NAME . " LIKE ?)";
                    $arSqlParams["statements"][] = "%" . $arParams["value"] . "%";
                }
                break;
        }

        return $arSqlParams;
    }
}
?>