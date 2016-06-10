<?
namespace NewEntity\ExtraFieldType;

class Field implements IField{
    protected $storageType = self::STORAGE_TYPE_STRING;
    protected $arField = array();
    
    const ERROR_REQUIRED    = "required",
          ERROR_INVALID     = "invalid",
          ERROR_UNIQUE      = "unique";
    
    const FIELD_TYPE_STRING   = 1,
          FIELD_TYPE_TEXT     = 2,
          FIELD_TYPE_NUM      = 3,
          FIELD_TYPE_LIST     = 4;
          
    const STORAGE_TYPE_STRING   = "string",
          STORAGE_TYPE_NUM      = "num",
          STORAGE_TYPE_TEXT     = "text";
    
    static protected $arFieldTypes = array(
        self::FIELD_TYPE_STRING   => "String",
        self::FIELD_TYPE_TEXT     => "Text",
        self::FIELD_TYPE_NUM      => "Num",
        self::FIELD_TYPE_LIST     => "List",
    );
    
    public function __construct($arField = array()){
        if(is_array($this->arField)){
            $this->arField = $arField;
        }
    }
    
    static public function getFieldTypes(){
        $arFieldTypes = array();
        
        foreach(self::$arFieldTypes AS $fieldType => $fieldTypeClass){
            $className = "\\" . __NAMESPACE__ . "\\" . $fieldTypeClass . "Field";

            if(class_exists($className)){
                $arFieldTypes[$fieldType] = new $className;
            }
        }
        
        return $arFieldTypes;
    }
    
    static public function getFieldType($fieldType){
        $arFieldTypes = self::getFieldTypes();
        
        return isset($arFieldTypes[$fieldType]) ? $arFieldTypes[$fieldType] : false;
    }
    
    public function setFieldData($arField){
        $this->arField = $arField;
        
        return $this;
    }
    
    public function getFieldData(){
        return $this->arField;
    }
    
    public function getStorageType(){
        return $this->storageType;
    }
    
    public function validate($arData){
        $storageType = $this->getStorageType();
        
        $error = false;
        
        //check required value
        if($this->arField["required"]){
            $hasValue = false;
            
            foreach($arData AS $arValue){
                if(strlen($arValue["value_" . $storageType])){
                    $hasValue = true;
                    break;
                }
            }
            
            if(!$hasValue){
                $error = self::ERROR_REQUIRED;
            }
        }
        
        //check unique value
        if(!$error && $this->arField["uniq"]){
            $arTmp = array();
            
            foreach($arData AS $arValue){
                if(isset($arTmp[$arValue["value_" . $storageType]])){
                    $error = self::ERROR_UNIQUE;
                    unset($arTmp);
                    break;
                }
                
                $arTmp[$arValue["value_" . $storageType]] = 1;
            }
        }

        $arResult = array();
        
        if($error){
            $arResult["success"]    = false;
            $arResult["error"]      = $error;
        }else{
            $arResult["success"]    = true;
        }
        
        return $arResult;
    }
}
?>