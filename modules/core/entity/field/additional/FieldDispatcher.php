<?
namespace Entity\Field\Additional;

use \Entity\Builder;
use \DB\Builder AS DbBuilder;
use \Entity\Result\AddResult;
use \Entity\Result\UpdateResult;
use \Entity\Result\DeleteResult;
use \Entity\Result\SelectResult;
use \CArrayHelper;
use \CArrayFilter;

class FieldDispatcher extends \Entity\Field\BaseFieldDispatcher{
    const FIELD_VALUE_TABLE = "new_entity_extra_field_value";
    const FIELD_TABLE = "new_entity_extra_field";
    
    public function __construct(Builder $obBuilder){
        parent::__construct($obBuilder);
        
        foreach($this->getFieldsInfo() AS $arField){
            $fieldTypeClass = $arField["type"];
            $fieldName      = $arField["alias"];
            
            $this->addField(new $fieldTypeClass($fieldName, $arField));
        }
    }
    
    public function isField($obField){
        return $obField instanceof Field;
    }
    
    //TO DO переписать
    static public function getFieldVariants($arFieldIDs = []){
        return ExtraFieldVariant::builder()->whereIn("field_id", $arFieldIDs)
                                           ->orderBy("priority", "ASC")
                                           ->orderBy("title", "ASC")
                                           ->fetchAll();
    }
    
    /*вынести работу с доп полями в отдельный класс*/
    public function deleteFieldsInfo(array $arFieldAliases = []){
        $obBuilder  = $this->getFieldBuilder();
        $entityName = $this->getBuilder()->getEntity()->getEntityName();
        $obBuilder->where("entity_id", $entityName);
        
        if(count($arFieldAliases)){
            $obBuilder->whereIn("alias", $arFieldAliases);
        }
        
        $rowCount = $obBuilder->delete();
        
        return $rowCount;
    }
    
    public function addFieldsInfo(array $arFields){
        $obBuilder  = $this->getFieldBuilder();
        $entityName = $this->getBuilder()->getEntity()->getEntityName();
        $arIDs      = [];
        
        foreach($arFields AS $arData){
            $arData["entity_id"] = $entityName;
            $id = $obBuilder->insert($arData);
            
            if($id){
                $arIDs[] = $id;
            }
        }
        
        return $arIDs;
    }
    
    public function getFieldsInfo(array $arFieldNames = []){
        $obBuilder = $this->getFieldBuilder();
        $obBuilder->where("entity_id", $this->getBuilder()->getEntity()->getEntityName());
        
        if(count($arFieldNames)){
            $obBuilder->whereIn("alias", $arFieldNames);
        }
        
        return $obBuilder->orderBy("id", "ASC")
                         ->fetchAll();
    }
    
    public function getFieldValues(array $arItemIDs, array $arFieldNames = []){
        $obBuilder = $this->getFieldValueBuilder()
                          ->select("v.*", "f.alias")
                          ->alias("v")
                          ->join(self::FIELD_TABLE . " AS f", function($obJoin){
                            $obJoin->on("v.field_id", "f.id")
                                   ->on("v.entity_id", "f.entity_id");
                          })
                          ->where("v.entity_id", $this->getBuilder()->getEntity()->getEntityName())
                          ->whereIn("v.item_id", $arItemIDs);
        
        if(count($arFieldNames)){
            $obBuilder->whereIn("f.alias", $arFieldNames);
        }

        return $obBuilder->orderBy("v.id", "ASC")
                         ->fetchAll();
    }
    
    protected function setFieldValues(array $arItemIDs, array $arFieldValues){
        $obEntity       = $this->getBuilder()->getEntity();
        $arFieldNames   = array_keys($arFieldValues);
        $arTmpFields    = $this->getFieldsInfo($arFieldNames);
        $arFields       = [];
        
        foreach($arTmpFields AS $arField){
            $fieldTypeClass = $arField["type"];
            
            if(class_exists($fieldTypeClass)){
                $arFields[$arField["alias"]] = new $fieldTypeClass($arField["alias"], $arField, $obEntity);
            }
        }
        
        //get field values from database with hash key (item_id, field_id, value)
        $arCurrentExistValues = $this->getFieldValues($arItemIDs, $arFieldNames);
        
        $arExistFieldValues = [];
        
        foreach($arCurrentExistValues AS $arCurrentExistValue){
            if(($obField = $arFields[$arCurrentExistValue["alias"]])){
                $uid = $arCurrentExistValue["item_id"] . ":" . $arCurrentExistValue["alias"] . ":" . $arCurrentExistValue[$obField->getColumnName()]; 
            
                $arExistFieldValues[$uid][] = $arCurrentExistValue;
            }
        }
    
        $arNewFieldValueIDs = [];
        
        //for each request field
        foreach($arFields AS $fieldAlias => $obField){
            $arParams   = $obField->getParams();
            $columnName = $obField->getColumnName();
      
            if(!is_array($arFieldValues[$fieldAlias])){
                $arFieldValues[$fieldAlias] = [$arFieldValues[$fieldAlias]];
            }
            
            if(!$arParams["multi"] && count($arFieldValues[$fieldAlias]) > 1){
                $arFieldValues[$fieldAlias] = [reset($arFieldValues[$fieldAlias])];
            }
            
            $arFieldValues[$fieldAlias] = CArrayFilter::filter($arFieldValues[$fieldAlias], function($value){
                return (strlen($value) > 0);
            });
            
            $obBuilder = $this->getFieldValueBuilder();
    
            //for each request field value
            foreach($arFieldValues[$fieldAlias] AS $fieldValue){
                //we are get hash key
                foreach($arItemIDs AS $id){
                    $uid = $id . ":" . $fieldAlias . ":" . $fieldValue;
                    
                    //and check if not yet exist in $arExistFieldValues, then add new value
                    if(!isset($arExistFieldValues[$uid])){
                        $arValue = [
                            "item_id"   => $id,
                            "field_id"  => $arParams["id"],
                            "entity_id" => $obEntity->getEntityName(),
                            $columnName => $fieldValue
                        ];
                        
                        if(($obField->onBeforeAddValue($arValue)) !== false && 
                                ($newValueID = $obBuilder->insert($arValue))){
                            $arNewFieldValueIDs[] = $newValueID;
                            
                            $obField->onAfterAddValue($arValue);
                        }
                    }
                    
                    //unset value for delete other values which not set
                    if(count($arExistFieldValues[$uid]) > 1){
                        array_pop($arExistFieldValues[$uid]);
                    }else{
                        unset($arExistFieldValues[$uid]);
                    }
                }
            }
        }
        
        //delete other values which not set
        if(count($arExistFieldValues)){
            $arDeleteFieldItemIDs   = [];
            $arDeleteFields         = [];
            
            foreach($arExistFieldValues AS $arExistFieldValue){
                foreach($arExistFieldValue AS $arExistFieldValueItem){
                    if(isset($arFields[$arExistFieldValueItem["alias"]])){
                        $obField = $arFields[$arExistFieldValueItem["alias"]];
                        
                        if($obField->onBeforeDeleteValue($arExistFieldValueItem) !== false){
                            $arDeleteFieldItemIDs[] = $arExistFieldValueItem["id"];
                            $arDeleteFields[] = [
                                'field' => $obField,
                                'value' => $arExistFieldValueItem
                            ];
                        }
                    }
                }
            }
            
            if(count($arDeleteFieldItemIDs)){
                $this->getFieldValueBuilder()
                     ->whereIn("id", $arDeleteFieldItemIDs)
                     ->delete();
                     
                foreach($arDeleteFields AS $arDeleteItem){
                    $arDeleteItem['field']->onAfterDeleteValue($arDeleteItem['value']);
                }
            }
            
            
        }
        
        return $arNewFieldValueIDs;
    }
    
    public function getFieldBuilder(){
        return (new DbBuilder)->from(self::FIELD_TABLE);
    }
    
    public function getFieldValueBuilder(){
        return (new DbBuilder)->from(self::FIELD_VALUE_TABLE);
    }
    /*вынести работу с доп полями в отдельный класс*/
    
    protected function onFetch(SelectResult $obResult){
        $arData = $obResult->getData();
        
        if(!count($arData)){
            return ;
        }
        
        $arFields = [];
        
        foreach($obResult->getSelectFieldNames() AS $fieldName){
            if($obField = $this->getField($fieldName)){
                $arFields[$fieldName] = $obField;
            }
        }
        
        if(count($arFields)){
            $pkColumn   = $this->getBuilder()->getEntity()->getPk();
            $arItems    = CArrayHelper::index($arData, $pkColumn);
            $arItemIDs  = CArrayHelper::getColumn($arItems, $pkColumn);

            foreach($this->getFieldValues($arItemIDs, array_keys($arFields)) AS $arFieldValue){
                $obField    = $arFields[$arFieldValue["alias"]];
                $fieldName  = $obField->getAlias() ? $obField->getAlias() : $obField->getName();
                $itemID     = $arFieldValue["item_id"];
                
                if(!is_array($arItems[$itemID][$fieldName])){
                    $arItems[$itemID][$fieldName] = [];
                }
                
                $arItems[$itemID][$fieldName][$arFieldValue["id"]] = $arFieldValue[$obField->getColumnName()];
            }
            
            $obResult->setData(array_values($arItems));
            
            foreach($arFields AS $fieldName => $obField){
                $obField->onFetch($obResult);
                
                if(is_callable($obField->onFetchData)){
                    $obField->onFetchData($obResult, $obField);
                }
            }
        }
    }
    
    public function add(AddResult $obResult){
        $this->onBeforeAdd($obResult);
        
        $arData = $obResult->getData();
        
        $arFieldValues = [];
        
        foreach($arData AS $fieldName => $value){
            if($obField = $this->getField($fieldName)){
                $arFieldValues[$fieldName] = $value;
            }
        }
        
        if(count($arFieldValues)){
            $id = $arData[$this->getBuilder()->getEntity()->getPk()];
            $this->setFieldValues([$id], $arFieldValues);
            $this->onAfterAdd($obResult);
        }
        
        return true;
    }
    
    public function update($id, UpdateResult $obResult){
        $this->onBeforeUpdate($id, $obResult);
        
        $arFieldValues = [];
        
        foreach($obResult->getChangedData() AS $fieldName => $value){
            if($obField = $this->getField($fieldName)){
                $arFieldValues[$fieldName] = $value;
            }
        }
        
        if(count($arFieldValues)){
            $this->setFieldValues([$id], $arFieldValues);
            $this->onAfterUpdate($id, $obResult);
        }
        
        return true;
    }
    
    public function delete($id, DeleteResult $obResult){
        $this->onBeforeDelete($id, $obResult);
        $this->setFieldValues([$id], []);
        $this->onAfterDelete($id, $obResult);
        
        return true;
    }
}