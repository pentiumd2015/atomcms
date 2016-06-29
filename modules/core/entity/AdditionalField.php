<?
namespace Entity;


use DB\Query AS DbQuery;
use Helpers\CArrayHelper;

class AdditionalField{
    const FIELD_VALUE_TABLE     = "new_entity_extra_field_value";
    const FIELD_TABLE           = "new_entity_extra_field";
    const FIELD_VARIANT_TABLE   = "new_entity_extra_field_variant";

    protected $manager;

    public function __construct(Manager $manager){
        $this->manager = $manager;
    }

    public static function query(){
        return (new DbQuery())->from(self::FIELD_TABLE);
    }

    public static function valueQuery(){
        return (new DbQuery)->from(self::FIELD_VALUE_TABLE);
    }

    public static function variantQuery(){
        return (new DbQuery)->from(self::FIELD_VARIANT_TABLE);
    }

    public function getFieldValues(array $itemIDs, array $fieldNames = []){
        $query = $this->valueQuery();

        $query->select("v.*", "f.name")
              ->alias("v")
              ->join(self::FIELD_TABLE . " AS f", "v.field_id", "f.id")
              ->where("f.entity_id", $this->manager->getEntityName())
              ->whereIn("v.item_id", $itemIDs);

        if(count($fieldNames)){
            $query->whereIn("f.name", $fieldNames);
        }

        return $query->orderBy("v.id", "ASC")->fetchAll();
    }

    public function getFieldVariants(array $fieldNames = []){
        $query = $this->variantQuery();

        $query->select("v.*", "f.name")
              ->alias("v")
              ->join(self::FIELD_TABLE . " AS f", "v.field_id", "f.id")
              ->where("v.entity_id", $this->manager->getEntityName());

        if(count($fieldNames)){
            $query->whereIn("f.name", $fieldNames);
        }

        return $query->orderBy("v.priority", "ASC")
                     ->orderBy("v.title", "ASC")
                     ->fetchAll();
    }

    public function createFields(array $fieldNames = []){
        $fields = [];

        $query = $this->query()
                      ->where("entity_id", $this->manager->getEntityName());

        if(count($fieldNames)){
            $query->whereIn("name", $fieldNames);
        }

        foreach($query->fetchAll() AS $field){
            $fieldTypeClass = $field["type"];

            if(class_exists($fieldTypeClass)){
                $fields[$field["name"]] = new $fieldTypeClass($field["name"], $field);
            }
        }

        return $fields;
    }

    public function setFieldValues(array $itemIDs, array $fieldValues = []){
        $fieldNames = array_keys($fieldValues);
        $fields     = $this->createFields($fieldNames);

        $existFieldValues = [];

        foreach($this->getFieldValues($itemIDs, $fieldNames) AS $existFieldValue){
            if(($field = $fields[$existFieldValue["name"]])){
                $columnName = $field->getColumnName();
                $uid        = $existFieldValue["item_id"] . ":" . $existFieldValue["name"] . ":" . $existFieldValue[$columnName];

                $existFieldValue["value"] = $existFieldValue[$columnName];
                $existFieldValues[$uid][] = $existFieldValue;
            }
        }

        $addValues = [];

        foreach($fields AS $fieldName => $field){
            //преобразуем значения в массив
            if(!is_array($fieldValues[$fieldName])){
                $fieldValues[$fieldName] = [$fieldValues[$fieldName]];
            }

            //проверяем, если поле не многозначное, то преобразуем в массив
            if(!$field->multi && count($fieldValues[$fieldName]) > 1){
                $fieldValues[$fieldName] = [reset($fieldValues[$fieldName])];
            }

            foreach($fieldValues[$fieldName] AS $value){
                foreach($itemIDs AS $itemId){
                    $uid = $itemId . ":" . $fieldName . ":" . $value;

                    //если такого значения еще нет, добавим
                    if(!isset($existFieldValues[$uid])){
                        $addValues[$fieldName][] = [
                            "item_id"   => $itemId,
                            "value"     => $value
                        ];
                    }

                    //удаляем значение из массива значений, чтобы удалить оставшиеся
                    //у одного поля может быть несколько одинаковых значений, поэтому не делаем unset по $uid
                    if(count($existFieldValues[$uid]) > 1){
                        array_pop($existFieldValues[$uid]);
                    }else{
                        unset($existFieldValues[$uid]);
                    }
                }
            }
        }

        $deleteValues = [];

        //удаляем оставшиеся значения
        if(count($existFieldValues)){
            foreach($existFieldValues AS $existFieldValueDuplicates){
                foreach($existFieldValueDuplicates AS $existFieldValue){
                    $deleteValues[$existFieldValue["name"]][] = [
                        "value" => $existFieldValue["value"],
                        "id"    => $existFieldValue["id"]
                    ];
                }
            }
        }

        if(count($addValues)){
            $query = $this->valueQuery();

            foreach($addValues AS $fieldName => $items){
                $field          = $fields[$fieldName];
                $columnName     = $field->getColumnName();
                $values         = CArrayHelper::getColumn($items, "value");
                $insertValues   = [];

                foreach($items AS $item){
                    $insertValues[] = [
                        "item_id"   => $item["item_id"],
                        "field_id"  => $field->id,
                        $columnName => $item["value"]
                    ];
                }

                $field->onBeforeAddValues($values);

                if(count($insertValues)){
                    $query->insert($insertValues);
                }

                $field->onAfterAddValues($values);
            }
        }

        if(count($deleteValues)){
            foreach($deleteValues AS $fieldName => $items){
                $field  = $fields[$fieldName];
                $values = CArrayHelper::getColumn($items, "value");
                $deleteValueIds = [];

                foreach($items AS $item){
                    $deleteValueIds[] = $item["id"];
                }

                $field->onBeforeDeleteValues($values);

                if(count($deleteValueIds)){
                    $this->valueQuery()
                         ->whereIn("id", $deleteValueIds)
                         ->delete();
                }

                $field->onAfterDeleteValues($values);
            }
        }
    }
/*
    public static function getFieldTypes(){
        $arFieldTypes = array();
        
        $extraDir = __DIR__ . "/field/extra";
        
        if(is_dir($extraDir)){
            $obDirectoryIterator = CFile::scanDirectory($extraDir);
            
            $namespace = "\\" . __NAMESPACE__ . "\Field\Extra\\";
            
            $obManager = new static;
            
            foreach($obDirectoryIterator AS $obSplFile){
                if($obSplFile->isDot()){
                    continue;
                }
                
                $baseName = $obSplFile->getBasename(".php");
                
                if($baseName == "Field"){
                    continue;
                }
                
                if($obSplFile->isFile()){
                    $fieldTypeClass = $namespace . $baseName;
                    
                    if(class_exists($fieldTypeClass)){
                        $arFieldTypes[$fieldTypeClass] = new $fieldTypeClass("", array(), $obManager);
                    }
                }
            }
        }
        
        uasort($arFieldTypes, function($obA, $obB){
            $arInfoA = $obA->getInfo();
            $arInfoB = $obB->getInfo();
            
            return strnatcasecmp($arInfoA["title"], $arInfoB["title"]);
        });
        
        return $arFieldTypes;
    }*/
}