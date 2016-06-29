<?
namespace Entity\Field\Additional;

use \DB\JoinCondition;
use \Entity\AdditionalField;

abstract class Field extends \Entity\Field\BaseField{
    public $settings = [];
    public $id;
    public $unique = false;
    
    abstract public function getColumnName();
    
    public function onBeforeAddValues($values){}
    public function onAfterAddValues($values){}
    public function onBeforeDeleteValues($values){}
    public function onAfterDeleteValues($values){}
    
    public function onSelect(){
        return false;
    }
    
    public function __construct($name, $params = []){
        parent::__construct($name, $params);
        
        $safeParams = [ //присваиваем только разрешенные параметры
            "id",
            "settings",
            "unique"
        ];
        
        foreach($safeParams AS $param){
            if(isset($params[$param])){
                $this->{$param} = $params[$param];
            }
        }
    }
    
    public function condition($method, array $args = []){
        $dispatcher = $this->getDispatcher();
        $entity     = $dispatcher->getQuery()->getManager();

        $subQuery = AdditionalField::valueQuery()->select("v.item_id")
                                                 ->alias("v")
                                                 ->join(AdditionalField::FIELD_TABLE . " AS f", "v.field_id", "f.id")
                                                 ->where("f.entity_id", $entity->getEntityName())
                                                 ->where("f.name", $this->name);
        
        $args["column"] = $this->getColumnName();

        if(method_exists($subQuery, $method)){
            call_user_func_array([$subQuery, $method], $args);
        }
        
        $dispatcher->getQuery()->whereIn($entity->getPk(), $subQuery, $args["logic"]);
    }
    
    public function orderBy($by){ 
        $dispatcher   = $this->getDispatcher();
        $entity       = $dispatcher->getQuery()->getManager();

        $orderTableAlias = "field_" . $this->name;

        $join = new JoinCondition(AdditionalField::FIELD_VALUE_TABLE . " AS " . $orderTableAlias, "left");

        $join->on($orderTableAlias . ".item_id", $entity->getPk())
             ->where($orderTableAlias . ".field_id", $this->id);

        $dispatcher->getQuery()
                   ->join($join)
                   ->groupBy($entity->getPk())
                   ->orderBy($orderTableAlias . "." . $this->getColumnName(), $by);
    }
    
    public function groupBy(){
        $dispatcher   = $this->getDispatcher();
        $entity       = $dispatcher->getQuery()->getManager();
             
        $groupTableAlias = "field_" . $this->name;

        $join = new JoinCondition(AdditionalField::FIELD_VALUE_TABLE . " AS " . $groupTableAlias, "left");

        $join->on($groupTableAlias . ".item_id", $entity->getPk())
             ->where($groupTableAlias . ".field_id", $this->id);

        $dispatcher->getQuery()
                   ->join($join)
                   ->groupBy($groupTableAlias . "." . $this->getColumnName());
    }

    public function filter($value){
        if(is_scalar($value) && strlen($value)){
            $this->getDispatcher()
                 ->getQuery()
                 ->where($this->name, $value);
        }
    }
}