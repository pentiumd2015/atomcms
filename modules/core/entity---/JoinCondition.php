<?
namespace Entity;

class JoinCondition{
	public $type;
	public $table;
	public $arConditions = array();
    public $arParams = array();
    
	public function __construct($type, $table){
		$this->type   = $type;
		$this->table  = $table;
	}
    
	public function on($relation, $operator, $reference, $logic = "AND", $where = false){
		$this->arConditions[] = compact("relation", "operator", "reference", "logic", "where");
        
        if($where){
            $this->arParams[] = $reference;
        }
        
		return $this;
	}
	
	public function orOn($relation, $operator, $reference){
		return $this->on($relation, $operator, $reference, "OR");
	}

	public function where($relation, $operator = NULL, $reference = NULL, $logic = "AND"){
        if(func_num_args() == 2){
    		list($reference, $operator) = array($operator, "=");
    	}
        
		return $this->on($relation, $operator, $reference, $logic, true);
	}

	public function orWhere($relation, $operator, $reference){
		return $this->on($relation, $operator, $reference, "OR", true);
	}

	public function whereNull($column, $logic = "AND"){
		return $this->on($column, "IS", new Expr("NULL"), $logic, false);
	}
}
?>