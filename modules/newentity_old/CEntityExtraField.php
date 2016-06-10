<?
namespace NewEntity;

class CEntityExtraField extends Manager{
    static protected $entityName    = "new_entity_extra_field";
    static protected $pk            = "id";
    
    static public function getBaseFields(){
        return array(
            "id" => array(
                "type"      => "integer",
                "primary"   => true
            ),
            "entity_id" => array(
                "type"      => "integer",
                "required"  => true
            ),
            "relation" => array(
                "type"  => "enum",
                "value" => array(1 => "Элемент", 2 => "Раздел"),
            ),
            "title" => array(
                "type"      => "string",
                "required"  => true
            ),
            "caption" => array(
                "type" => "string"
            ),
            "description" => array(
                "type" => "text"
            ),
            "params" => array(
                "type" => "text"
            ),
            "type" => array(
                "type" => "integer",
                "required"  => true
            ),
            "priority" => array(
                "type" => "integer"
            ),
            "required" => array(
                "type" => "boolean",
                "required"  => true,
                "value" => array(0, 1)
            ),
            "uniq" => array(
                "type" => "boolean",
                "required"  => true,
                "value" => array(0, 1)
            ),
            "multi" => array(
                "type" => "boolean",
                "required"  => true,
                "value" => array(0, 1)
            ),
            "date_add" => array(
                "type" => "datetime"
            ),
            "date_update" => array(
                "type" => "datetime"
            )
        );
    }
    
    static public function add($arData){
        $arData["date_add"] = $arData["date_update"] = new \DB\Expr("NOW()");
        
        return parent::add($arData);
    }
    
    static public function update($id, $arData){
        $arData["date_update"] = new \DB\Expr("NOW()");
        
        return parent::update($id, $arData);
    }
}
?>