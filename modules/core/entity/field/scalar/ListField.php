<?
namespace Entity\Field\Scalar;

use Entity\Field\Renderer\ListRenderer;

class ListField extends Field{
    protected $info = [
        "title" => "Список значений"
    ];
    
    public $values;
    
    public function getRenderer(){
        return new ListRenderer($this);
    }
    
    public function __construct($name, $params = []){
        parent::__construct($name, $params);
        
        $safeParams = [ //присваиваем только разрешенные параметры
            "values"
        ];
        
        foreach($safeParams AS $param){
            if(isset($params[$param])){
                $this->{$param} = $params[$param];
            }
        }
    }
}