<?
namespace DB;

use \PDO;
use \CPagination;
use \Closure;
use \CArrayHelper;

class Builder{
    public $arColumns = [];
	public $distinct = false;
	public $from;
    public $alias;
	public $arJoins = [];
	public $arWheres = [];
	public $arGroups = [];
	public $arHavings = [];
	public $arOrders = [];
    public $arIndexes = [];
	public $limit;
	public $offset;
    protected $indexBy = false;
    
    protected $arParams = array(
        "select" => [],
		"join"   => [],
		"where"  => [],
		"having" => [],
		"order"  => [],
    );
    
    protected $pagination;
    
    protected $arOperators = array(
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
	);
          
    protected $connection;
    
    public function setConnection(Connection $connection){
        $this->connection = $connection;
    }
    
    public function getConnection(){
        return $this->connection;
    }
          
    public function __construct(Connection $connection = null){
        if(!$connection){
            $connection = Connection::getInstance();
        }
        
        $this->setConnection($connection);
    }
    
    public function getWheres(){
        return $this->arWheres;
    }
    
    public function getJoins(){
        return $this->arJoins;
    }
    
    public function getOrders(){
        return $this->arOrders;
    }
    
    public function getGroups(){
        return $this->arGroups;
    }
    
    public function getHavings(){
        return $this->arHavings;
    }
    
    public function getIndexes(){
        return $this->arIndexes;
    }
    
    public function mergeWithBuilder(Builder $obBuilder){
        $arParams = $obBuilder->getRawParams(); //мерджим параметры where
        
		$this->mergeParams($arParams["where"]);
        
        foreach($obBuilder->getJoins() AS $obJoin){
            $this->join($obJoin);
        }
        
        $this->orderBy($obBuilder->getOrders())
             ->groupBy($obBuilder->getGroups());
        
        foreach($obBuilder->getHavings() AS $arHaving){
            $this->having($arHaving["column"], $arHaving["operator"], $arHaving["value"], $arHaving["logic"]);
        }
        
        return $this;
    }
    
    public function addParam($value, $type = "where"){
		if(is_array($value)){
			$this->arParams[$type] = array_values(/**/array_merge($this->arParams[$type], $value))/**/;
		}else{
			$this->arParams[$type][] = $value;
		}

		return $this;
	}
    
    public function setParams(array $arParams, $type = "where"){
		$this->arParams[$type] = $arParams;
        
		return $this;
	}
    
    public function mergeParams(array $arParams = []){
		$this->arParams = array_merge_recursive($this->arParams, $arParams);
        
		return $this;
	}
    
    public function getRawParams(){
		return $this->arParams;
	}
    
    public function getParams(){
        $arReturn = [];
        
		array_walk_recursive($this->arParams, function($x) use (&$arReturn){
            $arReturn[] = $x; 
        });
        
		return $arReturn;
	}
    
    public function alias($alias){
        $this->alias = $alias;
        
        return $this;
    }
    
    public function select($arColumns = []){
        $this->arColumns = is_array($arColumns) ? $arColumns : func_get_args() ;
        
		return $this;
	}
    
    public function useIndex($arIndexes = []){
        $this->arIndexes = is_array($arIndexes) ? $arIndexes : func_get_args() ;
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
            $obJoin = $table;
        }else if($relation instanceof Closure){
            $obJoin = new JoinCondition($table, $type);
            
            $relation($obJoin);
		}else{
			$obJoin = new JoinCondition($table, $type);
            
			$obJoin->on($relation, $operator, $reference, "AND");
		}
        
        if(func_get_args() == 3){
            $reference  = $operator;
            $operator   = "=";
        }
        
        $this->arJoins[$obJoin->table . ($obJoin->alias ? "_" . $obJoin->alias : "")] = $obJoin;
        
        foreach($obJoin->arWheres AS $arWhere){
            if(!$this->isExpression($arWhere["value"])){
    			$this->addParam($arWhere["value"], "join");
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
    
    protected function whereNested(Builder $obBuilder, $logic){
        $this->arWheres[] = [
            "type"      => "nested",
            "builder"   => $obBuilder,
            "logic"     => $logic
        ];
        
        $this->mergeWithBuilder($obBuilder, $logic);
        
        return $this;
    }
    
    protected function whereSub($column, $operator, $obBuilder, $logic){
        $this->arWheres[] = [
            "type"      => "subquery",
            "column"    => $column,
            "operator"  => $operator,
            "builder"   => $obBuilder,
            "logic"     => $logic
        ];
        
        $this->mergeParams($obBuilder->getRawParams());
        
        return $this;
    }
    
    protected function whereSimple($column, $operator, $value, $logic){
        $this->arWheres[] = [
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
    
    protected function whereInSub($column, $obBuilder, $logic, $not){
        $this->arWheres[] = [
            "type"      => "in",
            "column"    => $column,
            "builder"   => $obBuilder,
            "logic"     => $logic,
            "not"       => $not
        ];
            
        $this->mergeParams($obBuilder->getRawParams());
        
        return $this;
    }
    
    protected function whereInSimple($column, $values, $logic, $not){
        $this->arWheres[] = [
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
                $obBuilder  = new self($this->connection);
                $obBuilder->from($this->from);
    
        		$column($obBuilder);
    		}else if($column instanceof self){
                $isNested   = true;
                $obBuilder  = $column;
    		}
            
            if($isNested){
                return $this->whereNested($obBuilder, $logic);
            }
        }

        if(func_num_args() == 2 || $value == NULL || !isset($this->arOperators[strtoupper($operator)])){
			$value      = $operator;
            $operator   = "=";
		}
        
        if(is_object($value)){//if subquery
            $isSub = false;
            
            if($value instanceof Closure){
                $isSub      = true;
                $obBuilder  = new self($this->connection);
                
        		$value($obBuilder);
    		}else if($value instanceof self){
                $isSub      = true;
                $obBuilder  = $value;
    		}
            
            if($isSub){
                return $this->whereSub($column, $operator, $obBuilder, $logic);
            }
        }
        
        return $this->whereSimple($column, $operator, $value, $logic);
	}
    
    public function orWhere($column, $operator = NULL, $value = NULL){
        return $this->where($column, $operator, $value, "OR");
    }
    
    public function andWhere($column, $operator = NULL, $value = NULL){
        return $this->where($column, $operator, $value, "AND");
    }
    
    public function whereRaw($sql, array $arParams = [], $logic = "AND"){
		$this->arWheres[] = [
            "type"  => "raw",
            "sql"   => $sql,
            "logic" => $logic
        ];
        
		$this->addParam($arParams, "where");
        
		return $this;
	}
    
    public function whereIn($column, $values, $logic = "AND", $not = false){
        if(is_object($values)){ //if subquery
            $isSub = false;
            
            if($values instanceof Closure){
                $isSub      = true;
                $obBuilder  = new self($this->connection);
        
        		$values($obBuilder);
            }else if($values instanceof self){
                $isSub      = true;
                $obBuilder  = $values;
            }
            
            if($isSub){
                return $this->whereInSub($column, $obBuilder, $logic, $not);
            }
        }
        
        return $this->whereInSimple($column, $values, $logic, $not);
	}
    
    public function whereNotIn($column, $values, $logic = "AND"){
		return $this->whereIn($column, $values, $logic, true);
	}
    
    public function whereNull($column, $logic = "AND", $not = false){
		$this->arWheres[] = [
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
    
    public function whereBetween($column, array $arValues, $logic = "AND", $not = false){
		$this->arWheres[] = [
            "type"  => "between", 
            "column"=> $column, 
            "logic" => $logic, 
            "not"   => $not
        ];
        
		$this->addParam($arValues, "where");
        
		return $this;
	}
    
    public function whereNotBetween($column, array $arValues, $logic = "AND"){
		return $this->whereBetween($column, $arValues, $logic, true);
	}
    
    public function whereExists($callback, $logic = "AND", $not = false){
        if($callback instanceof Closure){
            $obBuilder = new self($this->connection);
         
        	$callback($obBuilder);
        }else if($callback instanceof self){
            $obBuilder = $callback;
        }
        
        if($obBuilder){
            $this->arWheres[] = [
                "type"      => "exists", 
                "builder"   => $obBuilder, 
                "logic"     => $logic, 
                "not"       => $not
            ];
            
    		$this->mergeParams($obBuilder->getRawParams());
        }
        
		return $this;
	}
    
    public function whereNotExists($callback, $logic = "AND", $not = false){
		return $this->whereExists($callback, $logic, true);
	}
    
    public function groupBy($arGroups){
        $arGroups = is_array($arGroups) ? $arGroups : func_get_args() ;
        
		foreach($arGroups AS $group){
            $this->arGroups[] = $group;
		}
        
		return $this;
	}
    
    public function having($column, $operator = null, $value = null, $logic = "AND"){
		$this->arHavings[] = [
            "column"    => $column, 
            "operator"  => $operator, 
            "value"     => $value, 
            "logic"     => $logic
        ];
  
		$this->addParam($value, "having");
        
		return $this;
	}
    
    public function orderBy($column, $direction = "ASC"){
        $arOrders = !is_array($column) ? [$column => $direction] : $column ;
        
        foreach($arOrders AS $column => $direction){
            $this->arOrders[$column] = strtoupper($direction) == "ASC" ? "ASC" : "DESC";
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
        $this->arOrders = [];
        
        return $this;
    }
    
    public function cleanGroupBy(){
        $this->arGroups = [];
        
        return $this;
    }
    
    public function cleanWhere(){
        $this->arWheres = [];
        $this->setParams([], "where");
        
        return $this;
    }
    
    public function cleanJoin(){
        $this->arJoins = [];
        $this->setParams([], "join");
        
        return $this;
    }
    
    public function cleanHaving(){
        $this->arHavings = [];
        $this->setParams([], "having");
        
        return $this;
    }
    
    public function cleanPagination(){
        $this->pagination = false;
        
        return $this;
    }
    
    protected function paginate(){
        if($this->pagination && $this->pagination->perPage > 0){
            /*get count rows*/
            
            $obCountBuilder = clone $this;
            $obCountBuilder->cleanOrderBy()
                           ->cleanPagination()
                           ->limit(false)
                           ->offset(false);
           
            if(count($obCountBuilder->arGroups)){ //we have group operation
                $arDistinctColumns = [];
                
                foreach($this->arGroups AS $group){
                    $arDistinctColumns[] = $this->connection->quoteColumn($group);
                }
                
                $obCountBuilder->select(new Expr("COUNT(DISTINCT " . $this->replacePatterns($obCountBuilder->getGroupSql()) . ")"))
                               ->cleanGroupBy();
            }else{
                $obCountBuilder->select(new Expr("COUNT(*)"));
            }
            
            $this->pagination->count    = $obCountBuilder->fetchColumn();
            $this->pagination->numPage  = ceil($this->pagination->count / $this->pagination->perPage);
            $this->pagination->correctPage();
            /*get count rows*/
            
            $this->limit($this->pagination->perPage)
                 ->offset(($this->pagination->page - 1) * $this->pagination->perPage);
        }
        
        return $this;
    }
    
    public function pagination(CPagination $obPagination){
        $this->pagination = $obPagination;
        
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
            if(count($this->arJoins)){ //if we have join, then we must add table alias for each base column
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
        $arWheres = [];
        
        $index = 0;
        
        foreach($this->arWheres AS $arWhere){
            $where = NULL;
            
            switch($arWhere["type"]){
                case "raw":
                    $where = $arWhere["sql"];
                case "in":
                    if(isset($arWhere["builder"])){
                        $values = $arWhere["builder"]->getSelectSql();
                    }else if(count($arWhere["values"])){
                        $values = implode(", ", array_map([$this, "parameter"], $arWhere["values"]));
                    }
                    
                    if(strlen($values)){
                        $where = $this->prepareColumn($arWhere["column"]) . " " . ($arWhere["not"] ? "NOT " : "") . "IN (" . $values . ")";
                    }
                    break;
                case "exists":
                    $where = ($arWhere["not"] ? "NOT " : "") . "EXISTS (" . $arWhere["builder"]->getSelectSql() . ")";
                    break;
                case "nested":
                    $nestedWhere = $arWhere["builder"]->getWhereSql();
                    
                    if(strlen($nestedWhere)){
                        $where = "(" . $nestedWhere . ")";
                    }
                    
                    break;
                case "between":
                    $where = $this->prepareColumn($arWhere["column"]) . " " . ($arWhere["not"] ? "NOT " : "") . "BETWEEN ? AND ?";
                    break;
                case "subquery":
                    $where = $this->prepareColumn($arWhere["column"]) . " " . $arWhere["operator"] . " (" . $arWhere["builder"]->getSelectSql() . ")";
                    break;
                case "null":
                    $where = $this->prepareColumn($arWhere["column"]) . " IS " . ($arWhere["not"] ? "NOT " : "") . "NULL";
                    break;
                default:
                    $where = $this->prepareColumn($arWhere["column"]) . " " . $arWhere["operator"] . " " . $this->parameter($arWhere["value"]);
                    break;
            }
            
            if($where){
                if($index > 0){
                    $where = "\n" . $arWhere["logic"] . " " . $where;
                }
                
                $arWheres[] = $where;
                
                $index++;
            }
		}
        
        return implode(" ", $arWheres);
    }
    
    protected function getJoinSql(){
        $arJoins = [];
        
        foreach($this->arJoins AS $obJoin){
            $joinAlias  = $obJoin->alias ? " AS " . $obJoin->alias : "" ;
            $join       = strtoupper($obJoin->type) . " JOIN " . $this->connection->quoteTable($obJoin->table . $joinAlias);
            
            $arOns = [];
            
            foreach($obJoin->arOns AS $arOn){
        		$joinOn = $this->connection->quoteColumn($arOn["relation"]) . $arOn["operator"] . $this->connection->quoteColumn($arOn["reference"]);
                
                if(count($arOns)){
                    $joinOn = $arOn["logic"] . " " . $joinOn;
                }
                
                $arOns[] = $joinOn;
			}
            
            foreach($obJoin->arWheres AS $arWhere){
                $joinWhere = $this->connection->quoteColumn($arWhere["column"]) . $arWhere["operator"] . $this->parameter($arWhere["value"]);
                
                if(count($arOns)){
                    $joinWhere = $arWhere["logic"] . " " . $joinWhere;
                }
                
        		$arOns[] = $joinWhere;
			}
            
            if(count($arOns)){
                $join.= " ON (" . implode(" ", $arOns) . ")";
            }
            
            $arJoins[] = $join;
        }
        
        return implode(" \n", $arJoins);
    }
    
    protected function getGroupSql(){
        $arGroups = [];
        
        foreach(array_unique($this->arGroups) AS $group){
            $arGroups[] = $this->prepareColumn($group);
        }
        
        return implode(", ", $arGroups);
    }
    
    protected function getHavingSql(){
        $arHavings = [];
        
        foreach($this->arHavings AS $arHaving){
            $having = $this->prepareColumn($arHaving["column"]) . " " . $arHaving["operator"] . " " . $this->parameter($arHaving["value"]);
            
            if($index > 0){
                $having = $arHaving["logic"] . " " . $having;
            }
            
            $arHavings[] = $having;
        }
        
        return implode(" ", $arHavings);
    }
    
    protected function getOrderSql(){
        $arOrders = [];
            
        foreach($this->arOrders AS $column => $direction){
            $arOrders[] = $this->prepareColumn($column) . " " . $direction;
        }
        
        return implode(", ", $arOrders);
    }
    
    protected function getIndexSql(){
        $arIndexes = [];
            
        foreach($this->arIndexes AS $index){
            $arIndexes[] = $this->prepareColumn($index);
        }
        
        return implode(", ", $arIndexes);
    }
    
    protected function getSelectColumnsSql(){
        $arColumns = [];

        foreach($this->arColumns AS $column){
            if($this->isExpression($column)){
                $arColumns[] = $column->getValue();
                
                continue;
            }
            
            list($columnTable, $columnName, $columnAlias) = $this->getSelectColumnData($column);
            
            $column = $this->prepareColumn($columnTable ? $columnTable . "." . $columnName : $columnName);
            
            if($columnAlias){
                $column.= " AS " . $this->connection->quoteColumn($columnAlias);
            }
            
            $arColumns[] = $column;
        }
        
        return count($arColumns) ? implode(", ", $arColumns) : "*";
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
    
    public function getUpdateSql(){
        //1. table
        $table = $this->connection->quoteTable($this->from . ($this->alias ? " AS " . $this->alias : ""));
        
        $sql = "UPDATE " . $table;
        
        //2. join
        $joinSql = $this->getJoinSql();
        
        if($joinSql){
            $sql.= " \n" . $joinSql;
        }
        
        //3. columns
        $arColumns = [];
        
		foreach($this->arColumns AS $key => $value){
			$arColumns[] = $this->connection->quoteColumn($key) . " = " . $this->parameter($value);
		}
        
		$sql.= " \nSET " . implode(", ", $arColumns);
        
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
    
    public function fetchAll(){
        $arItems = $this->connection
                        ->query($this->getSelectSql(), $this->getParams())
                        ->fetchAll(PDO::FETCH_ASSOC);
        
        if(count($arItems) && $this->indexBy && ($arItem = reset($arItems)) && array_key_exists($this->indexBy, $arItem)){
            return CArrayHelper::index($arItems, $this->indexBy);
        }
        
        return $arItems;
    }
    
    public function fetch(){
        return $this->connection
                    ->query($this->getSelectSql(), $this->getParams())
                    ->fetch(PDO::FETCH_ASSOC);
    }
    
    public function fetchColumn(){
        return $this->connection
                    ->query($this->getSelectSql(), $this->getParams())
                    ->fetchColumn();
    }
    
    public function update(array $arValues){
        $this->arColumns = $arValues;
        
        foreach($arValues AS $key => $value){
            if($this->isExpression($value)){
                unset($arValues[$key]);
            }
        }
        
        $arParams = array_values(array_merge($arValues, $this->getParams()));
        
        return $this->connection->query($this->getUpdateSql(), $arParams)->rowCount();
	}
    
    public function delete(){
		return $this->connection
                    ->query($this->getDeleteSql(), $this->getParams())
                    ->rowCount();
	}
    
    public function insert(array $arData){
		return $this->connection
                    ->insert($this->from, $arData);
	}
    
    public function column(){
        list($table, $columnName, $columnAlias) = $this->getSelectColumnData(reset($this->arColumns));
        
        $column = $columnName;
        
        if($columnAlias){
            $column = $columnAlias;
        }
        
        $arItems = [];
        
        foreach($this->fetchAll() AS $arItem){
            $arItems[] = $arItem[$column];
        }
        
        return $arItems;
    }
    
    public function indexBy($column){
        $this->indexBy = $column;
        
        if(!in_array($column, $this->arColumns)){
            $this->arColumns[] = $column;
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
?>