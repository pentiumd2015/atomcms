<?
namespace Entity\Field\Scalar;

use Helpers\CArrayHelper;
use Entity\Field\Renderer\NumericRenderer;

class IntegerField extends Field{
    protected $info = [
        "title" => "Целое число"
    ];
    
    public function getRenderer(){
        return new NumericRenderer($this);
    }
    
    public function filter($value){
        if(is_scalar($value) && strlen($value)){
            $query = $this->getDispatcher()->getQuery();
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
                    
                    $query->where(function($subQuery) use($fieldName, $idFrom, $idTo){
                        $subQuery->where($fieldName, ">=", $idFrom)
                                 ->where($fieldName, "<=", $idTo);
                    });
                }else if($lFrom){
                    $query->where($fieldName, ">=", $idFrom);
                }else if($lTo){
                    $query->where($fieldName, "<=", $idTo);
                }
            }else if(strpos($value, ";") !== false){
                $itemIDs = explode(";", $value);
                $itemIDs = CArrayHelper::map($itemIDs, "trim");

                $query->whereIn($fieldName, $itemIDs);
            }else if($value){
                $query->where($fieldName, $value);
            }
        }
    }
}