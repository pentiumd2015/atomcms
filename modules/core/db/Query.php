<?
namespace DB;

use Helpers\CPagination;
use Closure;
use Helpers\CArrayHelper;
use CAtom;

class Query{
    protected $columns = [];
	protected $distinct = false;
	public $from;
    public $alias;
	public $joins = [];
	public $wheres = [];
	public $groups = [];
	public $havings = [];
	public $orders = [];
    public $indexes = [];
	public $limit;
	public $offset;
    protected $indexBy = false;
    
    protected $params = [
        "select" => [],
		"join"   => [],
		"where"  => [],
		"having" => [],
		"order"  => [],
    ];
    
    protected $pagination;
    
    protected $operators = [
        "="         => 1, 
        "<"         => 1, 
        ">"         => 1, 
        "<="        => 1, 
        ">="        => 1, 
        "<>"        => 1, 
        "!="        => 1,
        "LIKE"      => 1, 
        "NOT LIKE"  => 1, 
        "BETWEEN"   => 1, 
        "ILIKE"     => 1,
        "&"         => 1, 
        "|"         => 1, 
        "^"         => 1, 
        "<<"        => 1, 
        ">>"        => 1,
        "RLIKE"     => 1, 
        "REGEXP"    => 1, 
        "NOT REGEXP"=> 1,
	];
          
    protected $connection;
    
    public function setConnection(Connection $connection){
        $this->connection = $connection;
    }
    
    public function getConnection(){
        return $this->connection;
    }
          
    public function __construct(Connection $connection = null){
        if($connection == null){
            $connection = CAtom::$app->db;
        }
        
        $this->setConnection($connection);
    }
    
    public function getWheres(){
        return $this->wheres;
    }
    
    public function getJoins(){
        return $this->joins;
    }
    
    public function getOrders(){
        return $this->orders;
    }
    
    public function getGroups(){
        return $this->groups;
    }
    
    public function getHavings(){
        return $this->havings;
    }
    
    public function getIndexes(){
        return $this->indexes;
    }
    /*
     public function mergeWithQuery(Query $query){
         $params = $query->getRawParams(); //мерджим параметры where

         $this->mergeParams($params["where"])         /*
         foreach($query->getJoins() AS $join){
             $this->join($join);
         }

         $this->orderBy($query->getOrders())
              ->groupBy($query->getGroups());

         foreach($query->getHavings() AS $having){
             $this->having($having["column"], $having["operator"], $having["value"], $having["logic"]);
         }
        
        return $this;
    }*/
    
    public function addParam($value, $type = "where"){
		if(is_array($value)){
			$this->params[$type] = array_values(/**/array_merge($this->params[$type], $value))/**/;
		}else{
			$this->params[$type][] = $value;
		}

		return $this;
	}
    
    public function setParams(array $params, $type = "where"){
		$this->params[$type] = $params;
        
		return $this;
	}
    
    public function mergeParams(array $params = []){
		$this->params = array_merge_recursive($this->params, $params);
        
		return $this;
	}
    
    public function getRawParams(){
		return $this->params;
	}
    
    public function getParams(){
        $return = [];
        
		array_walk_recursive($this->params, function($x) use (&$return){
            $return[] = $x; 
        });
        
		return $return;
	}
    
    public function alias($alias){
        $this->alias = $alias;
        
        return $this;
    }
    
    public function select($columns = []){
        if(is_scalar($columns)){
            if(func_num_args() == 1){
                $columns = (strpos($columns, ",") !== false) ? preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY) : [$columns] ;
            }else{
                $columns = func_get_args();
            }
        }else if($this->isExpression($columns)){
            $columns = [$columns];
        }
        
        $this->columns = $columns;

		return $this;
	}
    
    public function useIndex($indexes = []){
        $this->indexes = is_array($indexes) ? $indexes : func_get_args() ;
    }

    public function distinct(){
		$this->distinct = true;

		return $this;
	}
    
    public function from($table){
        if(stripos($table, " as ") !== false){
            list($this->from, $this->alias) = preg_split("/\s+as\s+/si", $table, 2, PREG_SPLIT_NO_EMPTY);
        }else{
            $this->from = $table;
        }

		return $this;
	}
    
    public function getSelectColumnData($column){
        $columnTable    = false;
        $columnAlias    = false;
        
        if(strpos($column, ".") !== false){
            list($columnTable, $column) = explode(".", $column, 2);
        }
        
        if(stripos($column, " as ") !== false){ //if alias
            list($column, $columnAlias) = preg_split("/\s+as\s+/si", $column, 2, PREG_SPLIT_NO_EMPTY);
        }
        
        return [$columnTable, $column, $columnAlias];
    }
    
    //->join('contacts', 'users.id', '=', 'contacts.user_id')
    public function join($table, $relation = NULL, $operator = "=", $reference = null, $type = "INNER"){
        if($table instanceof JoinCondition){
            $join = $table;
        }else if($relation instanceof Closure){
            $join = new JoinCondition($table, $type);
            
            $relation($join);
		}else{
			$join = new JoinCondition($table, $type);
            
			$join->on($relation, $operator, $reference, "AND");
		}
        
        if(func_get_args() == 3){
            $reference  = $operator;
            $operator   = "=";
        }
        
        $this->joins[$join->table . ($join->alias ? "_" . $join->alias : "")] = $join;
        
        foreach($join->wheres AS $where){
            if(!$this->isExpression($where["value"])){
    			$this->addParam($where["value"], "join");
    		}
		}
        
		return $this;
	}
    
    public function leftJoin($table, $relation, $operator = "=", $reference = null){
		return $this->join($table, $relation, $operator, $reference, "LEFT");
	}
    
    public function rightJoin($table, $relation, $operator = "=", $reference = null){
		return $this->join($table, $relation, $operator, $reference, "RIGHT");
	}
    
    protected function whereNested(Query $query, $logic){
        $this->wheres[] = [
            "type"  => "nested",
            "query" => $query,
            "logic" => $logic
        ];

      //  $params = $query->getRawParams(); //мерджим параметры where

        $this->mergeParams($query->getRawParams());
        
     //  $this->mergeWithQuery($query, $logic);

        return $this;
    }
    
    protected function whereSub($column, $operator, $query, $logic){
        $this->wheres[] = [
            "type"      => "subquery",
            "column"    => $column,
            "operator"  => $operator,
            "query"     => $query,
            "logic"     => $logic
        ];
        
        $this->mergeParams($query->getRawParams());
        
        return $this;
    }
    
    protected function whereInSub($column, $query, $logic, $not){
        $this->wheres[] = [
            "type"      => "in",
            "column"    => $column,
            "query"     => $query,
            "logic"     => $logic,
            "not"       => $not
        ];
            
        $this->mergeParams($query->getRawParams());
        
        return $this;
    }
    
    protected function whereInSimple($column, $values, $logic, $not){
        $this->wheres[] = [
            "type"  => "in",
            "column"=> $column,
            "values"=> $values,
            "logic" => $logic,
            "not"   => $not
        ];
        
        $this->addParam($values, "where");
        
        return $this;
    }
    
    public function where($column, $operator = "=", $value = NULL, $logic = "AND"){
        if(is_object($column)){ //if nested query
            $isNested = false;
            
            if($column instanceof Closure){
                $isNested   = true;
                $query  = new self($this->connection);
                $query->from($this->from);
    
        		$column($query);
    		}else if($column instanceof self){
                $isNested   = true;
                $query  = $column;
    		}
            
            if($isNested){
                return $this->whereNested($query, $logic);
            }
        }

        if(func_num_args() == 2 || $value == NULL || !isset($this->operators[strtoupper($operator)])){
			$value      = $operator;
            $operator   = "=";
		}
        
        if(is_object($value)){//if subquery
            $isSub = false;
            
            if($value instanceof Closure){
                $isSub  = true;
                $query  = new self($this->connection);
                
        		$value($query);
    		}else if($value instanceof self){
                $isSub  = true;
                $query  = $value;
    		}
            
            if($isSub){
                return $this->whereSub($column, $operator, $query, $logic);
            }
        }

        $this->wheres[] = [
            "type"      => "simple",
            "column"    => $column,
            "operator"  => $operator,
            "value"     => $value,
            "logic"     => $logic
        ];

        if(!$this->isExpression($value)){
            $this->addParam($value, "where");
        }
        
        return $this;
	}
    
    public function orWhere($column, $operator = NULL, $value = NULL){
        return $this->where($column, $operator, $value, "OR");
    }
    
    public function andWhere($column, $operator = NULL, $value = NULL){
        return $this->where($column, $operator, $value, "AND");
    }
    
    public function whereRaw($sql, array $params = [], $logic = "AND"){
		$this->wheres[] = [
            "type"  => "raw",
            "sql"   => $sql,
            "logic" => $logic
        ];
        
		$this->addParam($params, "where");
        
		return $this;
	}
    
    public function whereIn($column, $values, $logic = "AND", $not = false){
        if(is_object($values)){ //if subquery
            $isSub = false;
            
            if($values instanceof Closure){
                $isSub      = true;
                $query  = new self($this->connection);
        
        		$values($query);
            }else if($values instanceof self){
                $isSub      = true;
                $query  = $values;
            }
            
            if($isSub){
                return $this->whereInSub($column, $query, $logic, $not);
            }
        }
        
        return $this->whereInSimple($column, $values, $logic, $not);
	}
    
    public function whereNotIn($column, $values, $logic = "AND"){
		return $this->whereIn($column, $values, $logic, true);
	}
    
    public function whereNull($column, $logic = "AND", $not = false){
		$this->wheres[] = [
            "type"  => "null",
            "column"=> $column,
            "logic" => $logic,
            "not"   => $not
        ];
        
		return $this;
	}
    
    public function whereNotNull($column, $logic = "AND"){
		return $this->whereNull($column, $logic, true);
	}
    
    public function whereBetween($column, array $values, $logic = "AND", $not = false){
		$this->wheres[] = [
            "type"  => "between", 
            "column"=> $column, 
            "logic" => $logic, 
            "not"   => $not
        ];
        
		$this->addParam($values, "where");
        
		return $this;
	}
    
    public function whereNotBetween($column, array $values, $logic = "AND"){
		return $this->whereBetween($column, $values, $logic, true);
	}
    
    public function whereExists($callback, $logic = "AND", $not = false){
        if($callback instanceof Closure){
            $query = new self($this->connection);
         
        	$callback($query);
        }else if($callback instanceof self){
            $query = $callback;
        }
        
        if($query){
            $this->wheres[] = [
                "type"  => "exists", 
                "query" => $query, 
                "logic" => $logic, 
                "not"   => $not
            ];
            
    		$this->mergeParams($query->getRawParams());
        }
        
		return $this;
	}
    
    public function whereNotExists($callback, $logic = "AND", $not = false){
		return $this->whereExists($callback, $logic, true);
	}
    
    public function groupBy($groups){
        $groups = is_array($groups) ? $groups : func_get_args() ;
        
		foreach($groups AS $group){
            $this->groups[] = $group;
		}
        
		return $this;
	}
    
    public function having($column, $operator = null, $value = null, $logic = "AND"){
		$this->havings[] = [
            "column"    => $column, 
            "operator"  => $operator, 
            "value"     => $value, 
            "logic"     => $logic
        ];
  
		$this->addParam($value, "having");
        
		return $this;
	}
    
    public function orderBy($column, $direction = "ASC"){
        $orders = !is_array($column) ? [$column => $direction] : $column ;
        
        foreach($orders AS $column => $direction){
            $this->orders[$column] = strtoupper($direction) == "ASC" ? "ASC" : "DESC";
        }
        
		return $this;
	}
    
    public function offset($value){
        if($value > 0){
            $this->offset = $value;
        }else{
            $this->offset = false;
		}
        
		return $this;
	}
    
    public function limit($value){
		if($value > 0){
            $this->limit = $value;
		}else{
            $this->limit = false;
		}
        
		return $this;
	}
    
    public function cleanOrderBy(){
        $this->orders = [];
        
        return $this;
    }
    
    public function cleanGroupBy(){
        $this->groups = [];
        
        return $this;
    }
    
    public function cleanWhere(){
        $this->wheres = [];
        $this->setParams([], "where");
        
        return $this;
    }
    
    public function cleanJoin(){
        $this->joins = [];
        $this->setParams([], "join");
        
        return $this;
    }
    
    public function cleanHaving(){
        $this->havings = [];
        $this->setParams([], "having");
        
        return $this;
    }
    
    public function cleanPagination(){
        $this->pagination = false;
        
        return $this;
    }
    
    protected function paginate(){
        if($this->pagination && $this->pagination->perPage > 0){
            
            $countQuery = clone $this;
            $countQuery->cleanOrderBy()
                       ->cleanPagination()
                       ->limit(false)
                       ->offset(false);
           
            if(count($countQuery->groups)){ //we have group operation
                $countQuery->select(new Expr("COUNT(DISTINCT " . $this->replacePatterns($countQuery->getGroupSql()) . ")"))
                           ->cleanGroupBy();
            }else{
                $countQuery->select(new Expr("COUNT(*)"));
            }
            
            $this->pagination->count = $countQuery->fetchColumn();
            $this->pagination->correctPage();
            
            $this->limit($this->pagination->perPage)
                 ->offset(($this->pagination->page - 1) * $this->pagination->perPage);
        }
        
        return $this;
    }
    
    public function pagination(CPagination $pagination){
        $this->pagination = $pagination;
        
        return $this;
    }
    
    public function isExpression($value){
		return $value instanceof Expr;
	}
    
    public function parameter($value){
		return $this->isExpression($value) ? $value->getValue() : "?";
	}
    
    public function prepareColumn($column){
        $column = $this->connection->quoteColumn($column);
        
        if(strpos($column, ".") === false){
            if(count($this->joins)){ //if we have join, then we must add table alias for each base column
                $column = $this->connection->quoteTable($this->alias ? $this->alias : $this->from) . "." . $column;
            }else if($this->alias){
                $column = $this->connection->quoteTable($this->alias) . "." . $column;
            }
        }
        
        return $column;
    }
    
    protected function replacePatterns($str){
        return strtr($str, [
            "{{table}}" => $this->alias ? $this->alias : $this->from
        ]);
    }
    
    protected function getWhereSql(){
        $wheres = [];
        
        $index = 0;
        
        foreach($this->wheres AS $where){
            $whereSql = NULL;
            
            switch($where["type"]){
                case "raw":
                    $whereSql = $where["sql"];
                case "in":
                    if(isset($where["query"])){
                        $values = $where["query"]->getSelectSql();
                    }else if(count($where["values"])){
                        $values = implode(", ", array_map([$this, "parameter"], $where["values"]));
                    }
                    
                    if(strlen($values)){
                        $whereSql = $this->prepareColumn($where["column"]) . " " . ($where["not"] ? "NOT " : "") . "IN (" . $values . ")";
                    }
                    break;
                case "exists":
                    $whereSql = ($where["not"] ? "NOT " : "") . "EXISTS (" . $where["query"]->getSelectSql() . ")";
                    break;
                case "nested":
                    $nestedWhere = $where["query"]->getWhereSql();
                    
                    if(strlen($nestedWhere)){
                        $whereSql = "(" . $nestedWhere . ")";
                    }
                    
                    break;
                case "between":
                    $whereSql = $this->prepareColumn($where["column"]) . " " . ($where["not"] ? "NOT " : "") . "BETWEEN ? AND ?";
                    break;
                case "subquery":
                    $whereSql = $this->prepareColumn($where["column"]) . " " . $where["operator"] . " (" . $where["query"]->getSelectSql() . ")";
                    break;
                case "null":
                    $whereSql = $this->prepareColumn($where["column"]) . " IS " . ($where["not"] ? "NOT " : "") . "NULL";
                    break;
                default:
                    $whereSql = $this->prepareColumn($where["column"]) . " " . $where["operator"] . " " . $this->parameter($where["value"]);
                    break;
            }
            
            if($whereSql){
                if($index > 0){
                    $whereSql = "\n" . $where["logic"] . " " . $whereSql;
                }
                
                $wheres[] = $whereSql;
                
                $index++;
            }
		}
        
        return implode(" ", $wheres);
    }
    
    protected function getJoinSql(){
        $joins = [];
        
        foreach($this->joins AS $join){
            $joinAlias  = $join->alias ? " AS " . $join->alias : "" ;
            $joinSql       = strtoupper($join->type) . " JOIN " . $this->connection->quoteTable($join->table . $joinAlias);
            
            $ons = [];
            
            foreach($join->ons AS $on){
        		$joinOn = $this->prepareColumn($on["relation"]) . $on["operator"] . $this->prepareColumn($on["reference"]);
                
                if(count($ons)){
                    $joinOn = $on["logic"] . " " . $joinOn;
                }
                
                $ons[] = $joinOn;
			}
            
            foreach($join->wheres AS $where){
                $joinWhere = $this->prepareColumn($where["column"]) . $where["operator"] . $this->parameter($where["value"]);
                
                if(count($ons)){
                    $joinWhere = $where["logic"] . " " . $joinWhere;
                }
                
        		$ons[] = $joinWhere;
			}
            
            if(count($ons)){
                $joinSql.= " ON (" . implode(" ", $ons) . ")";
            }
            
            $joins[] = $joinSql;
        }
        
        return implode(" \n", $joins);
    }
    
    protected function getGroupSql(){
        $groups = [];
        
        foreach(array_unique($this->groups) AS $group){
            $groups[] = $this->prepareColumn($group);
        }
        
        return implode(", ", $groups);
    }
    
    protected function getHavingSql(){
        $havings = [];
        
        foreach($this->havings AS $having){
            $having = $this->prepareColumn($having["column"]) . " " . $having["operator"] . " " . $this->parameter($having["value"]);
            
            if($index > 0){
                $having = $having["logic"] . " " . $having;
            }
            
            $havings[] = $having;
        }
        
        return implode(" ", $havings);
    }
    
    protected function getOrderSql(){
        $orders = [];
            
        foreach($this->orders AS $column => $direction){
            $orders[] = $this->prepareColumn($column) . " " . $direction;
        }
        
        return implode(", ", $orders);
    }
    
    protected function getIndexSql(){
        $indexes = [];
            
        foreach($this->indexes AS $index){
            $indexes[] = $this->prepareColumn($index);
        }
        
        return implode(", ", $indexes);
    }
    
    protected function getSelectColumnsSql(){
        $columns = [];

        foreach($this->columns AS $column){
            if($this->isExpression($column)){
                $columns[] = $column->getValue();
                
                continue;
            }
            
            list($columnTable, $columnName, $columnAlias) = $this->getSelectColumnData($column);
            
            $column = $this->prepareColumn($columnTable ? $columnTable . "." . $columnName : $columnName);
            
            if($columnAlias){
                $column.= " AS " . $this->connection->quoteColumn($columnAlias);
            }
            
            $columns[] = $column;
        }
        
        return count($columns) ? implode(", ", $columns) : "*";
    }
    
    public function getSelectSql(){
        //1. columns
        $sql = "SELECT" . ($this->distinct ? " DISTINCT" : "") . " " . $this->getSelectColumnsSql();

        //2. from
        if($this->from){
            $table = $this->connection->quoteTable($this->from . ($this->alias ? " AS " . $this->alias : ""));
            $sql.= " \nFROM " . $table;
        }
        
        //3. use index
        $indexSql = $this->getIndexSql();
        
        if($indexSql){
            $sql.= " \n USE INDEX(" . $indexSql . ")";
        }
        
        //4. join
        $joinSql = $this->getJoinSql();
        
        if($joinSql){
            $sql.= " \n" . $joinSql;
        }
        
        //5. where
        $whereSql = $this->getWhereSql();
        
        if($whereSql){
            $sql.= " \nWHERE " . $whereSql;
        }
        
        //6. group
        $groupSql = $this->getGroupSql();
        
        if($groupSql){
            $sql.= " \nGROUP BY " . $groupSql;
        }
        
        //7. having
        $havingSql = $this->getHavingSql();
        
        if($havingSql){
			$sql.= " \nHAVING " . $havingSql;
        }
        
        //8. order
        $orderSql = $this->getOrderSql();
        
        if($orderSql){
            $sql.= " \nORDER BY " . $orderSql;
        }
        
        $this->paginate();
        
        //9. limit
        if($this->limit){
		    $sql.= " \nLIMIT " . (int)$this->limit;
		}
        
        //10. offset
        if($this->offset){
            $sql.= " \nOFFSET " . (int)$this->offset;
        }

        return $this->replacePatterns($sql);
    }
    
    public function getUpdateSql($values){
        //1. table
        $table = $this->connection->quoteTable($this->from . ($this->alias ? " AS " . $this->alias : ""));
        
        $sql = "UPDATE " . $table;
        
        //2. join
        $joinSql = $this->getJoinSql();
        
        if($joinSql){
            $sql.= " \n" . $joinSql;
        }
        
        //3. columns
        $columns = [];
        
		foreach($values AS $key => $value){
			$columns[] = $this->connection->quoteColumn($key) . " = " . $this->parameter($value);
		}
        
		$sql.= " \nSET " . implode(", ", $columns);
        
        //4. where
        $whereSql = $this->getWhereSql();
        
        if($whereSql){
            $sql.= " \nWHERE " . $whereSql;
        }
        
        return $this->replacePatterns($sql);
    }
    
    public function getDeleteSql(){
        $table = $this->connection->quoteTable($this->from . ($this->alias ? " AS " . $this->alias : ""));
        
        $sql = "DELETE FROM " . $table;
        
        //1. where
        $whereSql = $this->getWhereSql();

        if($whereSql){
            $sql.= " \nWHERE " . $whereSql;
        }
        
        return $this->replacePatterns($sql);
    }
    
    public function getInsertSql($values){
        $sql = "INSERT INTO " . $this->connection->quoteTable($this->from);
        
		if(!is_array(reset($values))){
			$values = [$values];
		}
        
        $sql.= " (" . implode(", ", array_map([$this->connection, "quoteColumn"], array_keys(reset($values)))) . ")";
        
        //column values
        $valuesSql = implode(", ", array_map([$this, "parameter"], reset($values)));
        
        //multi values
        $sql.= " \nVALUES " . implode(", ", array_fill(0, count($values), "(" . $valuesSql . ")"));
        
        return $this->replacePatterns($sql);
    }
    
    public function fetchAll(){
        $items = $this->connection
                      ->query($this->getSelectSql(), $this->getParams())
                      ->fetchAll(PDO::FETCH_ASSOC);
        
        if(count($items) && $this->indexBy && ($item = reset($items)) && array_key_exists($this->indexBy, $item)){
            return CArrayHelper::index($items, $this->indexBy);
        }
        
        return $items;
    }
    
    public function fetch(){
        return $this->connection
                    ->query($this->getSelectSql(), $this->getParams())
                    ->fetch(PDO::FETCH_ASSOC);
    }
    
    public function fetchColumn($columnNumber = 0){
        return $this->connection
                    ->query($this->getSelectSql(), $this->getParams())
                    ->fetchColumn($columnNumber);
    }

    public function getColumn(){
        $column = reset($this->columns);
        $items  = $this->select($column)->fetchAll();

        if($this->isExpression($column)){
            $column = $column->getValue();
        }

        return CArrayHelper::getColumn($items, $column);
    }
    
    public function insert(array $values){
        if(!is_array(reset($values))){
			$values = [$values];
		}
        
        $params = [];
        
		foreach($values AS $records){
			foreach($records AS $value){
                if(!$this->isExpression($value)){
                    $params[] = $value;
                }
			}
		}
        
        $this->connection->query($this->getInsertSql($values), $params);
        
        return $this->connection->lastInsertId();
	}
    
    public function update(array $values){
        $params = [];
        
        foreach($values AS $value){
            if(!$this->isExpression($value)){
                $params[] = $value;
            }
        }
        
        $params = array_values(array_merge($params, $this->getParams()));
       
        return $this->connection->query($this->getUpdateSql($values), $params)->rowCount();
	}
    
    public function delete(){
		return $this->connection
                    ->query($this->getDeleteSql(), $this->getParams())
                    ->rowCount();
	}
    
    public function column(){
        list($table, $columnName, $columnAlias) = $this->getSelectColumnData(reset($this->columns));
        
        $column = $columnName;
        
        if($columnAlias){
            $column = $columnAlias;
        }
        
        $items = [];
        
        foreach($this->fetchAll() AS $item){
            $items[] = $item[$column];
        }
        
        return $items;
    }
    
    public function indexBy($column){
        $this->indexBy = $column;
        
        if(!in_array($column, $this->columns)){
            $this->columns[] = $column;
        }
        
        return $this;
    }
    
    public function exist(){
        return $this->limit(1)->count() > 0;
    }
    
    public function count($column = "*"){
		return $this->select(new Expr("COUNT(" . $this->prepareColumn($column) . ")"))->fetchColumn();
	}
    
    public function sum($column){
        return $this->select(new Expr("SUM(" . $this->prepareColumn($column) . ")"))->fetchColumn();
    }
    
    public function max($column){
        return $this->select(new Expr("MAX(" . $this->prepareColumn($column) . ")"))->fetchColumn();
    }
    
    public function min($column){
        return $this->select(new Expr("MIN(" . $this->prepareColumn($column) . ")"))->fetchColumn();
    }
    
    public function avg($column){
        return $this->select(new Expr("AVG(" . $this->prepareColumn($column) . ")"))->fetchColumn();
    }
}