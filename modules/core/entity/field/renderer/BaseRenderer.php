<?
namespace Entity\Field\Renderer;

use Entity\Field\BaseField;

abstract class BaseRenderer{
    protected $field;
    protected $params;
    
    abstract public function renderList($value, array $data = [], array $options = []);
    abstract public function renderFilter($value, array $data = [], array $options = []);
    abstract public function renderDetail($value, array $data = [], array $options = []);
    abstract public function renderParams();
    
    public function __construct(BaseField $field){
        $this->field = $field;
    }
    
    public function getField(){
        return $this->field;
    }
    
    public function getParams(){
        return $this->params;
    }
    
    public function setParams(array $params = []){
        $this->params = $params;
        
        return $this;
    }
}