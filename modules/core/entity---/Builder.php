<?
namespace Entity;

use \DB\Connection;
use \DB\Builder AS DbBuilder;
use \DB\Expr;

use \CArrayHelper;
use \CPagination;

use \CEvent;
use \Closure;
use \PDO;

class Builder{
    public $arColumns = array();
	public $distinct = false;
	public $from;
    public $arJoins = array();
	public $arWheres = array();
	public $arGroups = array();
	public $arHavings = array();
	public $arOrders = array();
	public $limit;
	public $offset;
    
    protected $arParams = array(
        "select" => array(),
        "join"   => array(),
		"where"  => array(),
		"having" => array(),
		"order"  => array(),
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
    
    protected $alias;
    protected $obEntity;
    protected $arExtraFieldIndexedByID;
    protected $arExtraFieldIndexedByAlias;
    protected $arSelectBaseColumns    = array();
    protected $arSelectCustomColumns  = array();
    protected $arSelectExtraColumns   = array();
    
    protected $connection;
    
    public function __construct(Connection $connection, Entity $obEntity){
        $this->obEntity     = $obEntity;
        $this->connection   = $connection;
    }
    
    public function setConnection(Connection $connection){
        $this->connection = $connection;
    }
    
    public function getConnection(){
        return $this->connection;
    }
    
    public function getEntity(){
        return $this->obEntity;
    }
    
    public function addParam($value, $type = "where"){
		if(is_array($value)){
			$this->arParams[$type] = /*array_values(*/array_merge($this->arParams[$type], $value)/*)*/;
		}else{
			$this->arParams[$type][] = $value;
		}
        
		return $this;
	}
    
    public function setParams(array $arParams, $type = "where"){
		$this->arParams[$type] = $arParams;
        
		return $this;
	}
    
    public function mergeParams($obBuilder){
		$this->arParams = array_merge_recursive($this->arParams, $obBuilder->getRawParams());
        
		return $this;
	}
    
    public function getRawParams(){
		return $this->arParams;
	}
    
    public function getParams(){
        $arReturn = array();
        
		array_walk_recursive($this->arParams, function($x) use (&$arReturn){
            $arReturn[] = $x; 
        });
        
		return $arReturn;
	}
    
    public function alias($alias){
        $this->alias = $alias;
        
        return $this;
    }
    
    public function select($arColumns = array()){
        $this->arColumns = is_array($arColumns) ? $arColumns : func_get_args() ;
        
		return $this;
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

    public function where($column, $operator = NULL, $value = NULL, $logic = "AND"){
        if(func_num_args() == 2){
            $value      = $operator;
            $operator   = "=";
		}
        
        if($this->isClosure($column)){ //if nested query
            $type = "nested";
            
            $obBuilder = new self($this->connection, $this->obEntity);
    		$obBuilder->from($this->from);
            
    		$column($obBuilder);
            
            if(count($obBuilder->arWheres)){
    			$this->arWheres[] = compact("type", "obBuilder", "logic");
                
    			$this->mergeParams($obBuilder);
    		}
            
            return $this;
		}else if($column instanceof DbBuilder || $column instanceof self){
            $type = "nested";
            
            $obBuilder = $column;
            
            if(count($obBuilder->arWheres)){
    			$this->arWheres[] = compact("type", "obBuilder", "logic");
                
    			$this->mergeParams($obBuilder);
    		}
            
            return $this;
		}
        
        if(!isset($this->arOperators[strtoupper($operator)])){
			list($value, $operator) = array($operator, "=");
		}
        
        if(($arExtraField = $this->getExtraField($column))){ //if extra field
            $fieldID        = $arExtraField[ExtraField::getPk()];
            $obExtraBuilder = $this->getExtraBuilder($fieldID)
                                   ->where($arExtraField["column_value"], $operator, $value, $logic);
            
            $this->whereIn($this->obEntity->getPk(), $obExtraBuilder, $logic);
        }else if($this->isClosure($value)){ //if subquery
            $type = "subquery";
            $obBuilder = new self($this->connection, $this->obEntity);
     
    		$value($obBuilder);
            
    		$this->arWheres[] = compact("type", "column", "operator", "obBuilder", "logic");
            
            $this->mergeParams($obBuilder);
		}else{
            $type = "simple";
    		$this->arWheres[] = compact("type", "column", "operator", "value", "logic");
            
            if(!$this->isExpression($value)){
    			$this->addParam($value, "where");
    		}
        }

		return $this;
	}

    public function whereRaw($sql, array $arParams = array(), $logic = "AND"){
		$type = "raw";
        
		$this->arWheres[] = compact("type", "sql", "logic");
		$this->addParam($arParams, "where");
        
		return $this;
	}

    public function whereIn($column, $values, $logic = "AND", $not = false){
        if(($arExtraField = $this->getExtraField($column))){
            $fieldID        = $arExtraField[ExtraField::getPk()];
            $obExtraBuilder = $this->getExtraBuilder($fieldID)
                                   ->whereIn($arExtraField["column_value"], $values, $logic, $not);
            
            $this->whereIn($this->obEntity->getPk(), $obExtraBuilder, $logic);
        }else{
            $type = "in";
            
            if($this->isClosure($values)){
                $obBuilder = new self($this->connection, $this->obEntity);
        
        		$values($obBuilder);
                
                $this->arWheres[] = compact("type", "column", "obBuilder", "logic", "not");
                
                $this->mergeParams($obBuilder);
            }else if($values instanceof DbBuilder || $values instanceof self){
                $obBuilder = $values;
                
                $this->arWheres[] = compact("type", "column", "obBuilder", "logic", "not");
                
                $this->mergeParams($obBuilder);
            }else{
                $this->arWheres[] = compact("type", "column", "values", "logic", "not");
                $this->addParam($values, "where");
            }
        }
        
		return $this;
	}

    public function whereNotIn($column, $values, $logic = "AND"){
		return $this->whereIn($column, $values, $logic, true);
	}

    public function whereNull($column, $logic = "AND", $not = false){
        if(($arExtraField = $this->getExtraField($column))){
            $fieldID        = $arExtraField[ExtraField::getPk()];
            $obExtraBuilder = $this->getExtraBuilder($fieldID)
                                   ->whereNull($arExtraField["column_value"], $logic, $not);
            
            $this->whereIn($this->obEntity->getPk(), $obExtraBuilder, $logic);
        }else{
            $type = "null";
            $this->arWheres[] = compact("type", "column", "logic", "not");
        }
        
        return $this;
	}

    public function whereNotNull($column, $logic = "AND"){
		return $this->whereNull($column, $logic, true);
	}

    public function whereBetween($column, array $arValues, $logic = "AND", $not = false){
        if(($arExtraField = $this->getExtraField($column))){
            $fieldID        = $arExtraField[ExtraField::getPk()];
            $obExtraBuilder = $this->getExtraBuilder($fieldID)
                                   ->whereBetween($arExtraField["column_value"], $arValues, $logic, $not);
            
            $this->whereIn($this->obEntity->getPk(), $obExtraBuilder, $logic);
        }else{
            $type = "between";
    		$this->arWheres[] = compact("type", "column", "logic", "not");
    		$this->addParam($arValues, "where");
        }
        
        return $this;
	}

    public function whereNotBetween($column, array $arValues, $logic = "AND"){
		return $this->whereBetween($column, $arValues, $logic, true);
	}

    public function whereExists($callback, $logic = "AND", $not = false){
        if($this->isClosure($callback)){
            $obBuilder = new self($this->connection, $this->obEntity);
         
        	$callback($obBuilder); 
        }else if($callback instanceof DbBuilder || $callback instanceof self){
            $obBuilder = $callback;
        }
        
        $type = "exists";
        
		$this->arWheres[] = compact("type", "obBuilder", "logic", "not");
		$this->mergeParams($obBuilder);
        
		return $this;
	}

    public function whereNotExists($callback, $logic = "AND", $not = false){
		return $this->whereExists($callback, $logic, true);
	}
    //->join('contacts', 'users.id', '=', 'contacts.user_id')
    public function join($table, $relation = NULL, $operator = null, $reference = null, $type = "INNER", $where = false){
        if($table instanceof JoinCondition){
            $obJoinCondition = $table;
        }else if($this->isClosure($relation)){
            $obJoin = new JoinCondition($type, $table);
            
            $relation($obJoin);

			$obJoinCondition = $obJoin;
		}else{
			$obJoin = new JoinCondition($type, $table);
            
			$obJoinCondition = $obJoin->on($relation, $operator, $reference, "AND", $where);
		}
        
        $this->arJoins[] = $obJoinCondition;
        
		return $this;
	}
    
    public function leftJoin($table, $relation, $operator = null, $reference = null){
		return $this->join($table, $relation, $operator, $reference, "LEFT");
	}
    
    public function rightJoin($table, $relation, $operator = null, $reference = null){
		return $this->join($table, $relation, $operator, $reference, "RIGHT");
	}
    
    public function groupBy($arGroups){
        $arGroups   = is_array($arGroups) ? $arGroups : func_get_args() ;
        $obEntity   = $this->obEntity;
        $tableAlias = $this->alias ? $this->alias : $this->from;
        
        foreach($arGroups AS $index => $column){
            if(($arExtraField = $this->getExtraField($column))){
                $groupTableAlias = "group_" . $column;
                
                $obJoin = new JoinCondition("left", $obEntity::FIELD_VALUE_TABLE . " AS " . $groupTableAlias);
                
                $obJoin->on($groupTableAlias . ".item_id", "=", $tableAlias . "." . $obEntity->getPk())
                       ->where($groupTableAlias . ".entity_id", $obEntity->getEntityName())
                       ->where($groupTableAlias . ".extra_field_id", $arExtraField[ExtraField::getPk()]);
                
                $this->join($obJoin)
                     ->groupBy($groupTableAlias . "." . $arExtraField["column_value"]);

                unset($arGroups[$index]);
            }
        }
        
		foreach($arGroups AS $group){
            $this->arGroups[$group] = 1;
		}
        
		return $this;
	}
    
    public function having($column, $operator = null, $value = null, $logic = "AND"){
		$this->arHavings[] = compact("column", "operator", "value", "logic");
		$this->addParam($value, "having");
		return $this;
	}
    
    public function orderBy($column, $direction = "ASC"){
        if(($arExtraField = $this->getExtraField($column))){
            $orderTableAlias    = "order_" . $column;
            $obEntity           = $this->obEntity;
            
            $tableAlias = $this->alias ? $this->alias : $this->from;
            $pkColumn   = $tableAlias . "." . $obEntity->getPk();
            
            $obJoin = new JoinCondition("left", $obEntity::FIELD_VALUE_TABLE . " AS " . $orderTableAlias);
            
            $obJoin->on($orderTableAlias . ".item_id", "=", $pkColumn)
                   ->where($orderTableAlias . ".entity_id", $obEntity->getEntityName())
                   ->where($orderTableAlias . ".extra_field_id", $arExtraField[ExtraField::getPk()]);
            
            $this->join($obJoin)
                 ->groupBy($pkColumn)
                 ->orderBy($orderTableAlias . "." . $arExtraField["column_value"], $direction);
        }else{
            $direction = strtoupper($direction) == "ASC" ? "ASC" : "DESC";
            $this->arOrders[] = compact("column", "direction");
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
        $this->arOrders = array();
        
        return $this;
    }
    
    public function cleanGroupBy(){
        $this->arGroups = array();
        
        return $this;
    }
    
    public function cleanWhere(){
        $this->arWheres = array();
        $this->setParams(array(), "where");
        
        return $this;
    }
    
    public function cleanJoin(){
        $this->arJoins = array();
        $this->setParams(array(), "join");
        
        return $this;
    }
    
    public function cleanHaving(){
        $this->arHavings = array();
        $this->setParams(array(), "having");
        
        return $this;
    }
    
    public function cleanPagination(){
        $this->pagination = false;
        
        return $this;
    }
    
    protected function prepareSave(array $arFields = array(), array $arData = array()){        
        $pk = $this->obEntity->getPk();
        
        foreach($arFields AS $fieldName => $obField){
            $fieldName  = $obField->getFieldName();
            $arParams   = $obField->getParams();
            
            if(isset($arParams["prepareSave"]) && is_callable($arParams["prepareSave"])){
                $prepareSave    = $arParams["prepareSave"];
                $value          = $prepareSave($arData[$fieldName], $pk, $arData, $obField);
                
                if($value !== false){
                    $arData[$fieldName] = $value;
                }else{
                    unset($arData[$fieldName]);
                }
            }
            
            if(method_exists($obField, "prepareSave")){
                $value = $obField->prepareSave($arData[$fieldName], $pk, $arData);
                
                if($value !== false){
                    $arData[$fieldName] = $value;
                }else{
                    unset($arData[$fieldName]);
                }
            }
        }

        return $arData;
    }

    protected function prepareFetch(array $arItems = array()){
        $pk = $this->obEntity->getPk();
        
        //base columns
        foreach($this->arSelectBaseColumns AS $fieldAlias => $obField){
            $obField->setFieldName($fieldAlias);
            
            if(method_exists($obField, "prepareFetch")){
                foreach($arItems AS $id => $arItem){
                    $arItems[$id][$fieldAlias] = $obField->prepareFetch($arItem[$fieldAlias], $pk, $arItem);
                }
            }
            
            $arParams = $obField->getParams();
            
            if(isset($arParams["prepareFetch"]) && is_callable($arParams["prepareFetch"])){
                $prepareFetch = $arParams["prepareFetch"];
                
                foreach($arItems AS $index => $arItem){
                    $arItems[$index][$fieldAlias] = $prepareFetch($arItem[$fieldAlias], $pk, $arItem);
                }                                                
            }
        }
        
        if(count($this->arSelectCustomColumns) || count($this->arSelectExtraColumns)){
            $arItems = CArrayHelper::index($arItems, $pk);

            //custom columns
            foreach($this->arSelectCustomColumns AS $fieldAlias => $obCustomField){
                $obCustomField->setFieldName($fieldAlias);
                
                $arItems    = $obCustomField->prepareFetch($arItems, $pk);
                $arParams   = $obCustomField->getParams();
                
                if(isset($arParams["prepareFetch"]) && is_callable($arParams["prepareFetch"])){
                    $prepareFetch   = $arParams["prepareFetch"];
                    $arItems        = $prepareFetch($arItems, $pk, $obCustomField);
                }
            }
            
            //extra columns
            $fieldPk = ExtraField::getPk();
            
            $arExtraFields = array();
            
            foreach($this->arSelectExtraColumns AS $fieldAlias => $obExtraField){
                $obExtraField->setFieldName($fieldAlias);
                
                $arParams = $obExtraField->getParams();
                
                $arExtraFields[$arParams[$fieldPk]] = $obExtraField;
            }
            
            if(count($arExtraFields)){
                foreach($this->obEntity->getExtraFieldValues(array_keys($arItems), array_keys($arExtraFields)) AS $arFieldValue){
                    $obExtraField   = $arExtraFields[$arFieldValue["extra_field_id"]];
                    $fieldName      = $obExtraField->getFieldName();
                    $itemID         = $arFieldValue["item_id"];
                    
                    if(!is_array($arItems[$itemID][$fieldName])){
                        $arItems[$itemID][$fieldName] = array();
                    }
                    
                    $arItems[$itemID][$fieldName][$arFieldValue["id"]] = $arFieldValue[$obExtraField->getColumnForValue()];
                }
                
                /*prepareValues of each field which requested*/
                foreach($arExtraFields AS $fieldID => $obExtraField){
                    $arItems = $obExtraField->prepareFetch($arItems, $pk);
                }
                /*prepareValues of each field which requested*/
            }
            
            $arItems = array_values($arItems);
        }
        
        return $arItems;
    }
    
    protected function loadExtraFields(){
        if(!is_array($this->arExtraFieldIndexedByID)){
            $this->arExtraFieldIndexedByID = CArrayHelper::index($this->obEntity->getExtraFields(), ExtraField::getPk());
            
            foreach($this->arExtraFieldIndexedByID AS $fieldID => &$arExtraField){
                $fieldTypeClass = $arExtraField["type"];
                
                if(class_exists($fieldTypeClass)){
                    $obFieldType                    = new $fieldTypeClass(ExtraField::getFieldNameById($fieldID), $arExtraField, $this->obEntity);
                    $arExtraField["type"]           = $obFieldType;
                    $arExtraField["column_value"]   = $obFieldType->getColumnForValue();
                }else{
                    unset($this->arExtraFieldIndexedByID[$fieldID]);
                }
            }
            
            unset($arExtraField);
            
            $this->arExtraFieldIndexedByAlias = CArrayHelper::index($this->arExtraFieldIndexedByID, "alias");
        }
    }
    
    protected function getExtraField($fieldName){
        $fieldName = ExtraField::getFieldAliasByName($fieldName);
        
        if(!$fieldName){
            return false;
        }
        
        $this->loadExtraFields();
        
        if(!is_numeric($fieldName)){
            $fieldID = $this->arExtraFieldIndexedByAlias[$fieldName][ExtraField::getPk()];
        }else{
            $fieldID = $fieldName;
        }
        
        return $this->arExtraFieldIndexedByID[$fieldID];
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
                $arDistinctColumns = array();
                
                foreach($this->arGroups AS $group => $true){
                    $arDistinctColumns[] = $this->connection->quoteColumn($group);
                }
                
                $obCountBuilder->select(new Expr("COUNT(DISTINCT " . $obCountBuilder->getGroupSql() . ")"))
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
        
    protected function validateFields($arFields, $arData){
        $arErrors = array();
        
        foreach($arFields AS $obField){
            $fieldName          = $obField->getFieldName();
            $obValidateResult   = $obField->validate($arData[$fieldName], $arData);
            
            if($obValidateResult !== true && $obValidateResult instanceof Field\Error){
                /*$arErrors[$fieldName] = array(
                    "code"      => $obValidateResult->getCode(),
                    "message"   => $obValidateResult->getMessage()
                );*/
                
                $arErrors[$fieldName] = $obValidateResult;
            }
        }

        return count($arErrors) ? $arErrors : true;
    }
    
    protected function getExtraBuilder($fieldID){
        $obEntity   = $this->obEntity;
        $obBuilder  = new self($this->connection, $obEntity);
        $obBuilder->select("item_id")
                  ->from($obEntity::FIELD_VALUE_TABLE)
                  ->where("entity_id", $obEntity->getEntityName())
                  ->where("extra_field_id", $fieldID);
                  
        return $obBuilder;
    }
    
    public function pagination(CPagination $obPagination){
        $this->pagination = $obPagination;
        
        return $this;
    }
    
    public function isExpression($value){
		return $value instanceof Expr;
	}
    
    public function isClosure($value){
		return $value instanceof Closure;
	}
    
    public function parameter($value){
		return $this->isExpression($value) ? $value->getValue() : "?";
	}
    
    protected function prepareColumn($column){
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
    
    protected function getWhereSql(){
        $arWheres = array();

        foreach($this->arWheres AS $index => $arWhere){
            $where = NULL;
            
            switch($arWhere["type"]){
                case "raw":
                    $where = $arWhere["sql"];
                case "in":
                    if(isset($arWhere["obBuilder"])){
                        $values = $arWhere["obBuilder"]->getSelectSql();
                    }else if(count($arWhere["values"])){
                        $values = implode(", ", array_map(array($this, "parameter"), $arWhere["values"]));
                    }
                    
                    if(strlen($values)){
                        $where = $this->prepareColumn($arWhere["column"]) . " " . ($arWhere["not"] ? "NOT " : "") . "IN (" . $values . ")";
                    }
                    break;
                case "exists":
                    $where = ($arWhere["not"] ? "NOT " : "") . "EXISTS (" . $arWhere["obBuilder"]->getSelectSql() . ")";
                    break;
                case "nested":
                    $where = "(" . $arWhere["obBuilder"]->getWhereSql() . ")";
                    break;
                case "between":
                    $where = $this->prepareColumn($arWhere["column"]) . " " . ($arWhere["not"] ? "NOT " : "") . "BETWEEN ? AND ?";
                    break;
                case "subquery":
                    $where = $this->prepareColumn($arWhere["column"]) . " " . $arWhere["operator"] . " (" . $arWhere["obBuilder"]->getSelectSql() . ")";
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
                    $where = $arWhere["logic"] . " " . $where;
                }
                
                $arWheres[] = $where;
            }
		}
        
        return implode(" ", $arWheres);
    }
    
    protected function getJoinSql(){
        $arJoins = array();
        
        $this->setParams(array(), "join");
        
        foreach($this->arJoins AS $obJoin){
            $join = strtoupper($obJoin->type) . " JOIN " . $this->connection->quoteTable($obJoin->table);
            
            $arConditions = array();
            
            foreach($obJoin->arConditions AS $index => $arCondition){
                $joinCondition = $this->connection->quoteColumn($arCondition["relation"]) . $arCondition["operator"] . ($arCondition["where"] ? "?" : $this->connection->quoteColumn($arCondition["reference"]));
                
                if($index > 0){
                    $joinCondition = $arCondition["logic"] . " " . $joinCondition;
                }
                
        		$arConditions[] = $joinCondition;
			}
            
            foreach($obJoin->arParams AS $param){
				$this->addParam($param, "join");
			}
            
            if(count($arConditions)){
                $join.= " ON (" . implode(" ", $arConditions) . ")";
            }
            
            $arJoins[] = $join;
        }
        
        return implode(" \n", $arJoins);
    }
    
    protected function getGroupSql(){
        $arGroups = array();

        foreach($this->arGroups AS $group => $true){
            $arGroups[] = $this->prepareColumn($group);
        }
        
        return implode(", ", $arGroups);
    }
    
    protected function getHavingSql(){
        $arHavings = array();
        
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
        $arOrders = array();
            
        foreach($this->arOrders AS $arOrder){
            $arOrders[] = $this->prepareColumn($arOrder["column"]) . " " . $arOrder["direction"];
        }
        
        return implode(", ", $arOrders);
    }
    
    protected function getSelectColumnsSql(){
        $fieldPk = ExtraField::getPk();
        
        $arBaseFields = array();
        
        foreach($this->obEntity->getFields() AS $obField){
            $arBaseFields[$obField->getFieldName()] = $obField;
        }
        
        $arCustomFields = array();
        
        foreach($this->obEntity->getCustomFields() AS $obCustomField){
            $arCustomFields[$obCustomField->getFieldName()] = $obCustomField;
        }
        
        $arColumns = array();
        
        $this->arSelectCustomColumns    = array();
        $this->arSelectExtraColumns     = array();
        
        $tableAlias = $this->alias ? $this->alias : $this->from;
        
        $needAllFields = false;
        
        if(!count($this->arColumns)){
            $needAllFields = true;
        }else{
            foreach($this->arColumns AS $column){
                if($this->isExpression($column)){
                    $arColumns[] = $column->getValue();
                }else{
                    //если выбираем все поля (базовые и кастомные), то не складываем в массив. Ниже добавим в массив
                    if($column == "*" || $column == $tableAlias . ".*"){
                        $needAllFields = true;
                        continue;
                    }
                    
                    $columnAlias = $column;
                    
                    if(stripos($column, " as ") !== false){ //if alias
                        list($column, $columnAlias) = preg_split("/\s+as\s+/si", $column, 2, PREG_SPLIT_NO_EMPTY);
                    }
                    
                    if($arCustomFields[$column]){ //is custom field
                        $this->arSelectCustomColumns[$columnAlias] = $arCustomFields[$column];
                    }else if(($fieldName = ExtraField::getFieldAliasByName($column))){
                        if($fieldName == "*"){
                            $this->loadExtraFields();
                            
                            foreach($this->arExtraFieldIndexedByID AS $arExtraField){
                                $fieldID = $arExtraField[$fieldPk];
                                $this->arSelectExtraColumns[ExtraField::getFieldNameById($fieldID)] = $arExtraField["type"];
                            }
                        }else if(($arExtraField = $this->getExtraField($column))){
                            $this->arSelectExtraColumns[$columnAlias] = $arExtraField["type"];
                        }
                    }else{ //if base or other fields, ex. join table
                        if(($pos = strpos($column, ".")) !== false){
                            list($tblAlias, $tmpColumn) = explode(".", $column, 2);
                            
                            if($tblAlias == $tableAlias && isset($arBaseFields[$tmpColumn])){
                                $this->arSelectBaseColumns[$columnAlias] = $arBaseFields[$tmpColumn];
                            }
                        }else if(isset($arBaseFields[$column])){
                            $this->arSelectBaseColumns[$columnAlias] = $arBaseFields[$column];
                        }
                        
                        $arColumns[] = $this->prepareColumn($column) . ($columnAlias != $column ? " AS " . $this->connection->quoteColumn($columnAlias) : "");
                    }
                }
            }
        }
        
        if($needAllFields){ //if all base fields need for load
            $this->arSelectBaseColumns = array();
            
            foreach($this->obEntity->getFields() AS $obField){
                $fieldName = $obField->getFieldName();
                $this->arSelectBaseColumns[$fieldName] = $obField;
                
                $arColumns[] = $this->prepareColumn($fieldName);
            }
            
            $this->arSelectCustomColumns = array();
            
            //we load custom fields too
            foreach($arCustomFields AS $fieldName => $obCustomField){
                $this->arSelectCustomColumns[$fieldName] = $obCustomField;
            }
        }
        /*
        foreach($arColumns AS $index => $column){
            $arColumns[$index] = $this->prepareColumn($column);
        }*/
        
        //we need pk value for extra values and custom values
        if(count($this->arSelectExtraColumns) || count($this->arSelectCustomColumns)){
            $pkColumn = $this->prepareColumn($this->obEntity->getPk());
            
            if(!in_array($pkColumn, $arColumns)){
                $arColumns[] = $pkColumn;
            }
        }
        
        return implode(", ", $arColumns);
    }
    
    public function getSelectSql(){
        //1. columns
        $sql = "SELECT" . ($this->distinct ? " DISTINCT" : "") . " " . $this->getSelectColumnsSql();
        
        //2. from
        if($this->from){
            $table = $this->connection->quoteTable($this->from . ($this->alias ? " AS " . $this->alias : ""));
            $sql.= " \nFROM " . $table;
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

        return $sql;
    }
    
    public function fetchAll(){
        
        p($this->getSelectSql());exit;
        $arItems = $this->connection
                        ->query($this->getSelectSql(), $this->getParams())
                        ->fetchAll(PDO::FETCH_ASSOC);
        
        if(count($arItems)){
            $arItems = $this->prepareFetch($arItems);
        }
        
        return $arItems;
    }
    
    public function fetch(){
        $arItem = $this->connection
                       ->query($this->limit(1)->getSelectSql(), $this->getParams())
                       ->fetch(PDO::FETCH_ASSOC);
        
        if($arItem){
            $arItems = array($arItem);
            $arItems = $this->prepareFetch($arItems);
            
            return reset($arItems);
        }
        
        return false;
    }
    
    public function fetchColumn(){
        return $this->connection
                    ->query($this->getSelectSql(), $this->getParams())
                    ->fetchColumn();
    }    
    
    public function add(array $arData){
        $obResult   = new Result\AddResult;
        $fieldPk    = ExtraField::getPk();
        $pk         = $this->obEntity->getPk();
        
        /*base fields*/
        $arBaseFields = array();
        
        foreach($this->obEntity->getFields() AS $obField){
            $fieldName = $obField->getFieldName();
            
            $arBaseFields[$fieldName] = $obField;
        }
        /*base fields*/
        
        /*custom fields*/
        $arCustomFields = array();
        
        foreach($this->obEntity->getCustomFields() AS $obCustomField){
            $fieldName                  = $obCustomField->getFieldName();
            $arCustomFields[$fieldName] = $obCustomField;
        }
        /*custom fields*/
        
        /*extra fields*/
        $arExtraFields = array();
        
        foreach($this->obEntity->getExtraFields() AS $arField){
            $fieldTypeClass = $arField["type"];
            
            if(class_exists($fieldTypeClass)){
                $fieldName                  = ExtraField::getFieldNameById($arField[$fieldPk]);
                $arExtraFields[$fieldName]  = new $fieldTypeClass($fieldName, $arField, $this->obEntity);
            }
        }
        /*extra fields*/
        
        $arAllFields = $arBaseFields + $arCustomFields + $arExtraFields;
        
        $validate = $this->validateFields($arAllFields, $arData);

        if($validate === true){
            $arData = $this->prepareSave($arAllFields, $arData);
            
            /*add main field values*/
            $arBaseFieldValues = array();
            
            foreach($arData AS $fieldName => $value){
                if(isset($arBaseFields[$fieldName])){
                    $arBaseFieldValues[$fieldName] = $arData[$fieldName];
                }
            }
            
            $arBaseFieldValues = $this->obEntity->onBeforeAdd($arBaseFieldValues);
            
            if($arBaseFieldValues !== false){
                $obBuilder = new DbBuilder($this->connection);
                $obBuilder->from($this->from);
                
                /*add main field values*/
                if(($id = $obBuilder->insert($arBaseFieldValues))){
                    $arAddData = array($pk => $id) + $arData;
                    
                    /*add extra field values*/
                    $arExtraFieldValues = array();
                    
                    foreach($arExtraFields AS $fieldName => $obField){
                        $arExtraFieldValues[ExtraField::getFieldIdByName($fieldName)] = $arAddData[$fieldName];
                    }
                    
                    if(count($arExtraFieldValues)){
                        $this->obEntity->setExtraFieldValues(array($id), $arExtraFieldValues);
                    }
                    /*add extra field values*/
                    
                    /*add custom field values*/
                    foreach($arCustomFields AS $fieldName => $obCustomField){
                        $obCustomField->add($arAddData, $pk, array($id => $arAddData));
                    }
                    /*add custom field values*/
                    
                    $obResult->setSuccess(true);
                    $obResult->setID($id);
                    $obResult->setData($arAddData);
                    
                    $this->obEntity->onAfterAdd($arAddData, $id);
                    
                    $arEvents = $this->obEntity->getEventNames();
                    
                    CEvent::trigger($arEvents["ADD"], $obResult);
                }else{
                    $obResult->setSuccess(false);
                    $obResult->setErrors(array("query error"));
                }
            }else{
                $obResult->setSuccess(false);
            }
        }else{
            $obResult->setSuccess(false);
            $obResult->setErrors($validate);
        }
        
        return $obResult;
    }
    
    public function update(array $arData){
        $obResult = new Result\UpdateResult;
        
        $pk         = $this->obEntity->getPk();
        $fieldPk    = ExtraField::getPk();
        
        /*base fields*/
        $arBaseFields       = array();
        $arDataBaseFields   = array();
        
        foreach($this->obEntity->getFields() AS $obField){
            $fieldName = $obField->getFieldName();
            
            if($fieldName == $pk){
                continue;
            }
            
            $arBaseFields[$fieldName] = $obField;
            
            if(isset($arData[$fieldName])){
                $arDataBaseFields[$fieldName] = $arBaseFields[$fieldName];
            }
        }
        /*base fields*/
        
        /*custom fields*/
        $arCustomFields     = array();
        $arDataCustomFields = array();
        
        foreach($this->obEntity->getCustomFields() AS $obCustomField){
            $fieldName                  = $obCustomField->getFieldName();
            $arCustomFields[$fieldName] = $obCustomField;
            
            if(isset($arData[$fieldName])){
                $arDataCustomFields[$fieldName] = $arCustomFields[$fieldName];
            }
        }
        /*custom fields*/
        
        /*extra fields*/
        $arExtraFields      = array();
        $arDataExtraFields  = array();
        
        foreach($this->obEntity->getExtraFields() AS $arField){
            $fieldTypeClass = $arField["type"];
            
            if(class_exists($fieldTypeClass)){
                $fieldName                  = ExtraField::getFieldNameById($arField[$fieldPk]);
                $arExtraFields[$fieldName]  = new $fieldTypeClass($fieldName, $arField, $this->obEntity);
                
                if(isset($arData[$fieldName])){
                    $arDataExtraFields[$fieldName] = $arExtraFields[$fieldName];
                }
            }
        }
        /*extra fields*/
                
        $arItems            = $this->select(array("*", "f_*"))
                                   ->fetchAll();
        $arIDs              = array();
        $hasErrors          = false;
        $numAffectedRows    = 0;
        $arDataAllFields    = $arDataBaseFields + $arDataCustomFields + $arDataExtraFields;
        $arAllFields        = $arBaseFields + $arCustomFields + $arExtraFields;
        
        $obBuilder = new DbBuilder($this->connection);
        $obBuilder->from($this->from);
        
        foreach($arItems AS $arItem){
            $arUpdateData = $arData + $arItem;
            
            $id = $arItem[$pk];
            
            $validate = $this->validateFields($arAllFields, $arUpdateData);
            
            if($validate === true){
                $arUpdateData = $this->prepareSave($arDataAllFields, $arUpdateData);

                /*update main field values*/
                $arBaseFieldValues = array();
                
                foreach($arUpdateData AS $fieldName => $value){
                    if(isset($arBaseFields[$fieldName])){
                        $arBaseFieldValues[$fieldName] = $arUpdateData[$fieldName];
                    }
                }
                /*update main field values*/
                
                $arBaseFieldValues = $this->obEntity->onBeforeUpdate($arBaseFieldValues, $id);
        
                if($arBaseFieldValues !== false){
                    /*update main values*/
                    $numAffectedRows+= $obBuilder->where($pk, $id)
                                                 ->update($arBaseFieldValues);              
                    /*update main values*/
                    
                    $arIDs[] = $id;
                }
            }else{
                $hasErrors = true;
                break;
            }
        }
        
        if(!$hasErrors){
            /*update custom and extra field values*/
            $arExtraFieldValues = array();
            
            foreach($arData AS $fieldName => $value){
                if(isset($arDataExtraFields[$fieldName])){
                    $arExtraFieldValues[ExtraField::getFieldIdByName($fieldName)] = $arData[$fieldName];
                }
                
                if(isset($arDataCustomFields[$fieldName])){
                    $obCustomField = $arDataCustomFields[$fieldName];
                    $obCustomField->update($arData, $pk, $arItems);
                }
            }
            
            if(count($arExtraFieldValues)){
                $this->obEntity->setExtraFieldValues($arIDs, $arExtraFieldValues);
            }
            /*update custom and extra field values*/
            
            $obResult->setSuccess(true);
            $obResult->setID($arIDs);
            $obResult->setNumAffectedRows($numAffectedRows);
            $obResult->setData($arData);
            
            $arEvents = $this->obEntity->getEventNames();
            
            foreach($arIDs AS $id){
                $this->obEntity->onAfterUpdate($arData, $id);
                
                CEvent::trigger($arEvents["UPDATE"], $obResult);
            }
        }else{
            $obResult->setSuccess(false);
            $obResult->setErrors($validate);
        }
        
        return $obResult;
    }
    
    public function delete(){
        $obResult           = new Result\DeleteResult;
        $arDeleteItems      = array();
        $numAffectedRows    = 0;
        $arIDs              = array();
        $obEntity           = $this->obEntity;
        
        $arItems            = $this->select("*", "f_*")
                                   ->fetchAll();
        $pk                 = $obEntity->getPk();
        
        foreach($arItems AS $arItem){
            $id = $arItem[$pk];
            
            if($obEntity->onBeforeDelete($arItem, $id) !== false){
                $arDeleteItems[$id] = $arItem;
            }
        }
        
        if(count($arDeleteItems)){
            $arIDs = array_keys($arDeleteItems);
            /*delete extra field values*/
            
            //we need delete all extra field values
            $obBuilder = new DbBuilder($this->connection);
            $obBuilder->from($obEntity::FIELD_VALUE_TABLE)
                      ->where("entity_id", $obEntity->getEntityName())
                      ->whereIn("item_id", $arIDs)
                      ->delete();
            /*delete extra field values*/
            
            /*delete custom field values*/
            foreach($obEntity->getCustomFields() AS $obCustomField){
                $obCustomField->delete($pk, $arDeleteItems);
            }
            /*delete custom field values*/
            
            $obBuilder = new DbBuilder($this->connection);
            
            $numAffectedRows = $obBuilder->from($this->from)
                                         ->whereIn($pk, $arIDs)
                                         ->delete();
        }
        
        $obResult->setID($arIDs);
        $obResult->setNumAffectedRows($numAffectedRows);
        $obResult->setData($arDeleteItems);
        $obResult->setSuccess(true);
        
        $arEvents = $obEntity->getEventNames();
        
        foreach($arDeleteItems AS $id => $arData){
            $obEntity->onAfterDelete($arData, $id);
            
            CEvent::trigger($arEvents["DELETE"], $obResult);
        }
        
        return $obResult;
    }
}
?>