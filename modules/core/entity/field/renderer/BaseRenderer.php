<?
namespace Entity\Field\Renderer;

use \Entity\Field\BaseField;

abstract class BaseRenderer{
    private $obField;
    
    abstract public function renderList($value, array $arData = [], array $arOptions = []);
    abstract public function renderFilter($value, array $arData = [], array $arOptions = []);
    abstract public function renderDetail($value, array $arData = [], array $arOptions = []);
    abstract public function renderParams();
    
    public function __construct(BaseField $obField){
        $this->obField = $obField;
    }
    
    public function getField(){
        return $this->obField;
    }
    
    public function getParams(){
        return $this->arParams;
    }
    
    public function setParams(array $arParams = []){
        $this->arParams = $arParams;
        
        return $this;
    }
}
?>