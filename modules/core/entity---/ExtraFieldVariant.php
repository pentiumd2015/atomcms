<?
namespace Entity;

class ExtraFieldVariant extends Manager{
    static protected $_table    = "new_entity_extra_field_variant";
    static protected $_pk       = "id";
    
    static protected $arEvents = array(
        "ADD"       => "ENTITY.FIELD.VARIANT.ADD",
        "UPDATE"    => "ENTITY.FIELD.VARIANT.UPDATE",
        "DELETE"    => "ENTITY.FIELD.VARIANT.DELETE",
    );
    
    static public function getFields(){
        return array(
            new Field\IntegerField("id", array(
                "title"     => "ID",
                "visible"   => true,
                "disabled"  => true
            )),
            new Field\StringField("extra_field_id", array(
                "title"     => "Entity Field ID",
                "required"  => true,
                "visible"   => true,
                "disabled"  => true
            )),
            new Field\StringField("title", array(
                "title"     => "Title",
                "required"  => true,
                "visible"   => true
            )),
            new Field\StringField("caption", array(
                "title"     => "Caption",
                "visible"   => true
            )),
            new Field\IntegerField("priority", array(
                "title"     => "Priority",
                "visible"   => true
            )),
            new Field\DateTimeField("date_add", array(
                "title"     => "Date add",
                "visible"   => true,
                "disabled"  => true
            )),
            new Field\DateTimeField("date_update", array(
                "title"     => "Date update",
                "visible"   => true,
                "disabled"  => true
            )),
        );
    }
    /*
    static public function add($arData){
        $arData["date_add"] = $arData["date_update"] = new \DB\Expr("NOW()");
        
        return parent::add($arData);
    }
    
    static public function update($id, $arData){
        $arData["date_update"] = new \DB\Expr("NOW()");
        
        unset($arData["extra_field_id"]);
        
        return parent::update($id, $arData);
    }*/
}
?>