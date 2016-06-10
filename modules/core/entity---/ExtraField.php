<?
namespace Entity;

use \CFile;

use \DB\Expr;

class ExtraField extends Entity{
    static protected $_table    = "new_entity_extra_field";
    static protected $_pk       = "id";
    
    const PREFFIX_KEY = "f_";
         
    static protected $arEvents = array(
        "ADD"       => "ENTITY.FIELD.ADD",
        "UPDATE"    => "ENTITY.FIELD.UPDATE",
        "DELETE"    => "ENTITY.FIELD.DELETE",
    );
    
    public function getFields(){
        return array(
            new Field\IntegerField("id", array(
                "title"     => "ID",
                "visible"   => true,
                "disabled"  => true
            ), $this),
            new Field\StringField("entity_id", array(
                "title"     => "Entity ID",
                "required"  => true,
                "visible"   => true,
                "disabled"  => true
            ), $this),
            new Field\StringField("title", array(
                "title"     => "Title",
                "required"  => true,
                "visible"   => true
            ), $this),
            new Field\StringField("caption", array(
                "title"     => "Caption",
                "visible"   => true
            ), $this),
            new Field\TextField("description", array(
                "title"     => "Description",
                "visible"   => true
            ), $this),
            new Field\StringField("params", array(
                "title"     => "Array Params",
                "visible"   => false
            ), $this),
            new Field\StringField("type", array(
                "title"     => "Тип поля",
                "required"  => true,
                "visible"   => true,
                "validate"  => function(){
                    return array(
                        function($value, $pk, $arData, $obField){
                            if($value && class_exists($value)){
                                $obFieldType = new $value($obField->getFieldName(), array(), $obField->getEntity());
                                
                                if($obFieldType instanceof Field\Field){
                                    return true;
                                }
                            }
                            
                            return new Field\Error($obField->getFieldName(), "Неверный тип поля", "type");
                        }
                    );
                }
            ), $this),
            new Field\IntegerField("priority", array(
                "title"     => "Priority",
                "visible"   => true
            ), $this),
            new Field\BooleanField("required", array(
                "title"     => "Required",
                "visible"   => true,
                "values" => array(1 => "Да", 0 => "Нет")
            ), $this),
            new Field\BooleanField("is_unique", array(
                "title"     => "Unique",
                "visible"   => true,
                "values" => array(1 => "Да", 0 => "Нет")
            ), $this),
            new Field\BooleanField("visible", array(
                "title"     => "Видимость",
                "visible"   => false,
                "values" => array(1 => "Да", 0 => "Нет")
            ), $this),
            new Field\BooleanField("multi", array(
                "title"     => "Multi",
                "visible"   => true,
                "values" => array(1 => "Да", 0 => "Нет")
            ), $this),
            new Field\DateTimeField("date_add", array(
                "title"     => "Дата добавления",
                "visible"   => true,
                "disabled"  => true
            ), $this),
            new Field\DateTimeField("date_update", array(
                "title"     => "Дата изменения",
                "visible"   => true,
                "disabled"  => true
            ), $this),
        );
    }
    
    static public function getFieldTypes(){
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
    }
    
    static public function getFieldAliasByName($fieldName){
        $arMatch = array();
        
        if(preg_match("/^" . static::PREFFIX_KEY . "(.+)$/", $fieldName, $arMatch)){
            return $arMatch[1];
        }
        
        return false;
    }
    
    static public function getFieldIdByName($fieldName){
        $arMatch = array();
        
        $fieldID = static::getFieldAliasByName($fieldName);
        
        if($fieldID && is_numeric($fieldID)){
            return $fieldID;
        }
        
        return false;
    }
    
    static public function getFieldNameById($fieldID){
        return static::PREFFIX_KEY . $fieldID;
    }

    public function onBeforeAdd(array $arData){
        $arData["date_add"] = $arData["date_update"] = new Expr("NOW()");

        return $arData;
    }
    
    public function onBeforeUpdate(array $arData){
        $arData["date_update"] = new Expr("NOW()");

        return $arData;
    }
    /*
    static public function getByAlias($alias){
        return static::find("alias=?", array($alias));
    }
    
    static public function getAllByAlias($arAliases){
        $statementParams = implode(", ", array_fill(0, count($arAliases), "?"));
        
        return static::findAll("alias IN(" . $statementParams . ")", $arAliases);
    }*/
}
?>