<?
namespace DB;

class JoinCondition{
	public $type;
	public $table;
    public $alias;
    public $wheres  = [];
    public $ons     = [];
    
	public function __construct($table = NULL, $type = "INNER"){
		$this->type = $type;
        
        if($table){
            $this->table($table);
        }
	}
    
    public function alias($alias){
        $this->alias = $alias;
        
        return $this;
    }
    
    public function table($table){
        if(stripos($table, " as ") !== false){
            list($this->table, $this->alias) = preg_split("/\s+as\s+/si", $table, 2, PREG_SPLIT_NO_EMPTY);
        }else{
            $this->table = $table;
        }
        
        return $this;
    }
    
	public function on($relation, $operator, $reference = NULL, $logic = "AND"){
        if(func_num_args() == 2 || $reference == NULL){
            $reference  = $operator;
            $operator   = "=";
    	}
        
        $this->ons[] = [
            "relation"  => $relation, 
            "operator"  => $operator, 
            "reference" => $reference, 
            "logic"     => $logic
        ];
        
		return $this;
	}
	
	public function orOn($relation, $operator, $reference = NULL){
		return $this->on($relation, $operator, $reference, "OR");
	}

	public function where($column, $operator = NULL, $value = NULL, $logic = "AND"){
        if(func_num_args() == 2 || $value == NULL){
            $value      = $operator;
            $operator   = "=";
    	}
        
        $this->wheres[] = [
            "column"    => $column, 
            "operator"  => $operator, 
            "value"     => $value, 
            "logic"     => $logic
        ];
        
		return $this;
	}

	public function orWhere($column, $operator = NULL, $value = NULL){
		return $this->where($column, $operator, $value, "OR");
	}

	public function whereNull($column, $logic = "AND"){
		return $this->where($column, "IS", new Expr("NULL"), $logic);
	}
}
?>