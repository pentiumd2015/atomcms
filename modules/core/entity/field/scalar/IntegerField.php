<?
namespace Entity\Field\Scalar;

use \CArrayHelper;
use Entity\Field\Renderer\NumericRenderer;

class IntegerField extends Field{
    protected $arInfo = array(
        "title" => "Целое число"
    );
    
    public function getRenderer(){
        return new NumericRenderer($this);
    }
    
    public function filter($value){
        if(is_scalar($value) && strlen($value)){
            $obBuilder = $this->getDispatcher()->getBuilder();
            $fieldName = $this->getName();
            
            if(strpos($value, "-") !== false){
                list($idFrom, $idTo) = explode("-", $value, 2);
                
                $lFrom  = strlen($idFrom);
                $lTo    = strlen($idTo);
                
                if($lFrom && $lTo){
                    if($idFrom > $idTo){
                        $tmpID  = $idTo;
                        $idTo   = $idFrom;
                        $idFrom = $tmpID;
                    }
                    
                    $obBuilder->where(function($obSubBuilder) use($fieldName, $idFrom, $idTo){
                        $obSubBuilder->where($fieldName, ">=", $idFrom)
                                     ->where($fieldName, "<=", $idTo);
                    });
                }else if($lFrom){
                    $obBuilder->where($fieldName, ">=", $idFrom);
                }else if($lTo){
                    $obBuilder->where($fieldName, "<=", $idTo);
                }
            }else if(strpos($value, ";") !== false){
                $arItemIDs = explode(";", $value);
                $arItemIDs = CArrayHelper::map($arItemIDs, "trim");

                $obBuilder->whereIn($fieldName, $arItemIDs);
            }else if($value){
                $obBuilder->where($fieldName, $value);
            }
        }
    }
}
?>