<?
namespace Entity\Field\Additional;

use \DB\JoinCondition;
/*use \CArrayHelper;*/

abstract class Field extends \Entity\Field\BaseField{
    abstract public function getColumnName();
    
    public function condition($method, array $args = []){
        $obDispatcher   = $this->getDispatcher();
        $obEntity       = $obDispatcher->getBuilder()->getEntity();

        $obSubBuilder = $obDispatcher->getFieldValueBuilder()
                                     ->select("v.item_id")
                                     ->alias("v")
                                     ->join($obDispatcher::FIELD_VALUE_TABLE . " AS f", function($obJoin){
                                        $obJoin->on("v.field_id", "f.id")
                                               ->on("v.entity_id", "f.entity_id");
                                     })
                                     ->where("v.entity_id", $obEntity->getEntityName())
                                     ->where("f.alias", $this->getFieldName());
        
        $args["column"] = $this->getColumnName();

        if(method_exists($obSubBuilder, $method)){
            call_user_func_array([$obSubBuilder, $method], $args);
        }
        
        $obDispatcher->getBuilder()->whereIn($obEntity->getPk(), $obSubBuilder, $args["logic"]);
    }
    
    public function orderBy($by){
        $obDispatcher   = $this->getDispatcher();
        $arParams       = $this->getParams();
        $obEntity       = $obDispatcher->getBuilder()->getEntity();
        $obBuilder      = $obDispatcher->getBuilder();
             
        $orderTableAlias = "field_" . $this->getFieldName();
        
        $obJoin = new JoinCondition($obDispatcher::FIELD_VALUE_TABLE . " AS " . $orderTableAlias, "left");
        
        $obJoin->on($orderTableAlias . ".item_id", $obBuilder->prepareColumn($obEntity->getPk()))
               ->where($orderTableAlias . ".entity_id", $obEntity->getEntityName())
               ->where($orderTableAlias . ".field_id", $arParams["id"]);
        
        $obBuilder->join($obJoin)
                  ->groupBy($obEntity->getPk())
                  ->orderBy($orderTableAlias . "." . $this->getColumnName(), $by);
    }
    
    public function groupBy(){
        $obDispatcher   = $this->getDispatcher();
        $arParams       = $this->getParams();
        $obEntity       = $obDispatcher->getBuilder()->getEntity();
        $obBuilder      = $obDispatcher->getBuilder();
             
        $groupTableAlias = "field_" . $this->getFieldName();
        
        $obJoin = new JoinCondition($obDispatcher::FIELD_VALUE_TABLE . " AS " . $groupTableAlias, "left");
        
        $obJoin->on($groupTableAlias . ".item_id", $obBuilder->prepareColumn($obEntity->getPk()))
               ->where($groupTableAlias . ".entity_id", $obEntity->getEntityName())
               ->where($groupTableAlias . ".field_id", $arParams["id"]);
        
        $obBuilder->join($obJoin)
                  ->groupBy($groupTableAlias . "." . $this->getColumnName());
    }
    
    
    
    public function filter($value, Builder $obBuilder){
       // $obBuilder->where($this->getFieldName(), $value);
    }
}