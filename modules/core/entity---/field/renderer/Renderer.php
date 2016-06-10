<?
namespace Entity\Field\Renderer;

use \Entity\Field\Field;

class Renderer{
    private $obField;
    
    public function __construct(Field $obField){
        $this->obField = $obField;
    }
    
    public function getField(){
        return $this->obField;
    }
    
    public function setField(Field $obField){
        $this->obField = $obField;
        
        return $this;
    }
    
    public function renderList($value, $arRow, $arParams = array()){
        return $value;
    }
    
    public function renderFilter($value, $arData, $arParams = array()){
        return $value;
    }
    
    public function renderDetail($value, $arData, $arParams = array()){
        return $value;
    }
    
    public function renderParams($arParams = array()){
        
    }
}
?>