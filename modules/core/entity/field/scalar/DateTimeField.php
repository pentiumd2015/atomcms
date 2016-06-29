<?
namespace Entity\Field\Scalar;

use Helpers\CDateTime;
use DB\Expr;
use Entity\Field\Renderer\DateTimeRenderer;

class DateTimeField extends Field{
    public $format = "d.m.Y H:i:s";

    protected $info = [
        "title" => "Дата/Время"
    ];

    public function __construct($name, $params = []){
        parent::__construct($name, $params);

        $safeParams = [ //присваиваем только разрешенные параметры
            "format"
        ];

        foreach($safeParams AS $param){
            if(isset($params[$param])){
                $this->{$param} = $params[$param];
            }
        }
    }
    
    public function getRenderer(){
        return new DateTimeRenderer($this);
    }
    
    public function validate($value, $result){
        $validate = parent::validate($value, $result);

        if($validate === true){
            if($value instanceof Expr){
                return true;
            }

            if(is_scalar($value)){
                $dateTime = CDateTime::createFromFormat($this->format, $value);

                if(!$dateTime || $dateTime->format($this->format) != $value){
                    $validate = new Error($this->name, "Неверный формат даты", Error::ERROR_INVALID);
                }
            }
        }

        return $validate;
    }
    
    public function onSelect(){
        return new Expr("DATE_FORMAT(" . $this->getDispatcher()->getQuery()->prepareColumn($this->name) . ", '" . preg_replace("/(\w+)/", "%$1", $this->format) . "')");
    }

    public function onBeforeAdd($value, $result)
    {
        $this->onSave($value, $result);
    }

    public function onBeforeUpdate($value, $result)
    {
        $this->onSave($value, $result);
    }

    protected function onSave($value, $result){
        if($value && !$value instanceof Expr){
            $dateTime = CDateTime::createFromFormat($this->format, $value);

            if($dateTime){
                $result->setDataValues([
                    $this->name => $dateTime->format("Y-m-d H:i:s")
                ]);
            }
        }
    }
}