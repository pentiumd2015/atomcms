<?
namespace NewEntity;

class CEntityExtraFieldVariant extends Manager{
    static protected $entityName    = "new_entity_extra_field_variant";
    static protected $pk            = "id";
    
    static public function getBaseFields(){
        return array(
            "id" => array(
                "type"      => "integer",
                "primary"   => true
            ),
            "extra_field_id" => array(
                "type"      => "integer",
                "required"  => true
            ),
            "title" => array(
                "type"      => "string",
                "required"  => true
            ),
            "caption" => array(
                "type" => "string"
            ),
            "priority" => array(
                "type" => "integer"
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