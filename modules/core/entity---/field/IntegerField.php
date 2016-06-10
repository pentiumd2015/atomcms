<?
namespace Entity\Field;

use \Helpers\CArrayHelper;
use \Helpers\CArrayFilter;

class IntegerField extends Field{
    protected $arInfo = array(
        "title" => "Целое число"
    );
    
    public function getRenderer(){
        return new Renderer\NumericRenderer($this);
    }
    
    public function filter($value, \Entity\Builder $obBuilder){
        $table = $this->getEntity()->getTableName();
        
        if(is_string($value) && strlen($value)){
            $fieldName = $table . "." . $this->getFieldName();
            
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
                    
                    $obBuilder->where(function($obBuilder) use($fieldName){
                        $obBuilder->where($fieldName, ">=", $idFrom)
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
    
    public function orderBy($by, \Entity\Builder $obBuilder){
        $table = $this->getEntity()->getTableName();
        
        $obBuilder->orderBy($table . "." . $this->getFieldName(), $by);
    }
}
?>