<?
namespace Entity;

use \DB\Builder AS DbBuilder;
use \CArrayHelper;
use \CArrayFilter;
use \CEvent;
use \CPagination;
use \Closure;

class Builder extends DbBuilder{
    protected $obEntity;
    
    protected $arSelectFieldNames = [];
    protected $arFields = false;
    
    protected $arFieldDispatchersClasses = [
        '\Entity\Field\Scalar\FieldDispatcher',
        '\Entity\Field\Custom\FieldDispatcher',
        '\Entity\Field\Additional\FieldDispatcher'
    ];
    
    protected $arFieldDispatchers = [];
    
    public function __construct(Entity $obEntity){
        parent::__construct();
        
        $this->obEntity = $obEntity;
        parent::from($obEntity->getTableName());
        
        $this->loadFields();
    }
    
    protected function addFieldDispatcher(Field\BaseFieldDispatcher $obFieldDispatcher){
        $this->arFieldDispatchers[] = $obFieldDispatcher;
    }
    
    protected function getFieldDispatchers(){
        return $this->arFieldDispatchers;
    }
    
    public function getFields(){
        return $this->arFields;
    }
    
    public function getEntity(){
        return $this->obEntity;
    }
    
    public function from($table){
        return $this;
    }
    
    public function operation($method, array $arArgs = []){
        $method = "parent::" . $method;
        
        if(is_callable($method)){
            return call_user_func_array($method, $arArgs);
        }
        
        return false;
    }
    
    public function mergeWithBuilder(DbBuilder $obBuilder){
        $arParams = $obBuilder->getRawParams(); //мерджим параметры where
        
		$this->mergeParams($arParams["where"]);
        
        if($this->obEntity->getTableName() == $obBuilder->from){ //если таблица не изменилась, то это поля сущности
            foreach($obBuilder->getJoins() AS $obJoin){
                $this->join($obJoin);
            }
            
            $this->orderBy($obBuilder->getOrders())
                 ->groupBy($obBuilder->getGroups());
            
            foreach($obBuilder->getHavings() AS $arHaving){
                $this->having($arHaving["column"], $arHaving["operator"], $arHaving["value"], $arHaving["logic"]);
            }
        }else{ //иначе просто смерджим как есть
            foreach($obBuilder->getJoins() AS $obJoin){
                $this->operation("join", [$obJoin]);
            }
            
            $this->operation("orderBy", [$obBuilder->getOrders()])
                 ->operation("groupBy", [$obBuilder->getGroups()]);
            
            foreach($obBuilder->getHavings() AS $arHaving){
                $this->operation("having", $arHaving);
            }
        }
        
        return $this;
    }
    
    protected function getFieldByColumn($column){
        if(!is_scalar($column) || $this->obEntity->getTableName() != $this->from){ //если таблица изменилась, то это уже не поле сущности
            return false;
        }

        list($table, $columnName, $columnAlias) = $this->getSelectColumnData($column);
        
        if($table){
            $entityTable = $this->alias ? $this->alias : $this->from;
            
            if($table != $entityTable){
                return false;
            }
        }

        return isset($this->arFields[$column]) ? $this->arFields[$column] : false ;
    }
    
    protected function getSelectColumnsSql(){
        $arColumns = $this->arSelectFieldNames = [];
        
        $needSelectPk  = false;
        $needAllFields = false;
        
        $entityTable = $this->alias ? $this->alias : $this->from;
        
        if(!count($this->arColumns)){
            $needAllFields = true;
        }else{
            foreach(["*", $entityTable . ".*"] AS $allColumn){
                if(($key = array_search($allColumn, $this->arColumns)) !== false){
                    unset($this->arColumns[$key]);
                    $needAllFields = true;
                }
            }
        }
        
        foreach($this->arColumns AS $column){
            if($this->isExpression($column)){
                $arColumns[] = $column->getValue();
                
                continue;
            }
            
            list($columnTable, $columnName, $columnAlias) = $this->getSelectColumnData($column);
            
            if((!$columnTable || $columnTable == $entityTable) && isset($this->arFields[$columnName])){
                $obField = $this->arFields[$columnName];
                
                if($columnAlias){
                    $obField->setAlias($columnAlias);
                }
                
                if($obField instanceof Field\Scalar\Field){
                    $column = $obField->onSelect();
                    
                    if($this->isExpression($column)){
                        $arColumns[] = $column->getValue() . " AS " . $this->prepareColumn($columnAlias ? $columnAlias : $columnName);
                    }else if(!strlen($column)){
                        $arColumns[] = $this->prepareColumn($columnName) . ($columnAlias ? " AS " . $this->prepareColumn($columnAlias) : "");
                    }else{
                        $arColumns[] = $this->prepareColumn($column) . " AS " . $this->prepareColumn($columnAlias ? $columnAlias : $columnName);
                    }
                }else{
                    $needSelectPk = true; //если выбираем любое не скалярное поле, то надо подгрузить $pk, на случай, если его не задали в select
                }
                
                $this->arSelectFieldNames[] = $columnName; //кладем все поля сущности
            }else{ //кладем остальные поля не сущности, это могут быть поля из джоинов и прочие, любые
                $column = $columnName;
                
                if($columnTable){
                    $column = $columnTable . "." . $column;
                }
                
                $column = $this->prepareColumn($column);
                
                if($columnAlias){
                    $column.= " AS " . $this->prepareColumn($columnAlias);
                }
                
                $arColumns[] = $column;
            }
        }

        if($needAllFields){ //if all fields need for load
            $this->arSelectFieldNames = array_keys($this->arFields);
            
            foreach($this->arFields AS $fieldName => $obField){
                if($obField instanceof Field\Scalar\Field){
                    $arColumns[] = $this->prepareColumn($fieldName);
                }
            }
        }
        
        //если нужен pk для диспетчеров кроме скалярного
        if($needSelectPk){
            $pk = $this->obEntity->getPk();
            
            if(!in_array($pk, $this->arSelectFieldNames)){
                $arColumns[] = $this->prepareColumn($pk);
                $this->arSelectFieldNames[] = $pk;
            }
        }

        return count($arColumns) ? implode(", ", $arColumns) : "*";
    }
    
    public function where($column, $operator = "=", $value = NULL, $logic = "AND"){
        if(is_object($column)){//if nested query
            $isNested = false;
            
            if($column instanceof Closure){ 
                $isNested   = true;
                $obBuilder  = new self($this->obEntity);
                
        		$column($obBuilder);
    		}else if($column instanceof DbBuilder){
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
        
        /*if subquery*/
        if(is_object($value)){//if subquery
            $isSub = false;
            
            if($value instanceof Closure){ 
                $isSub      = true;
                $obBuilder  = new self($this->obEntity);
                
        		$value($obBuilder);
    		}else if($value instanceof DbBuilder){
                $isSub      = true;
                $obBuilder  = $value;
    		}
            
            if($isSub){
                return $this->whereSub($column, $operator, $obBuilder, $logic);
            }
        }
        /*if subquery*/

        if($obField = $this->getFieldByColumn($column)){ 
            $obField->condition(__FUNCTION__, compact("column", "operator", "value", "logic"));
        }else{
            $this->whereSimple($column, $operator, $value, $logic);
        }

		return $this;
	}

    public function whereIn($column, $values, $logic = "AND", $not = false){
        if(is_object($values)){ //if subquery
            $isSub = false;
            
            if($values instanceof Closure){
                $isSub      = true;
                $obBuilder  = new self($this->obEntity);
                
                $values($obBuilder);
    		}else if($values instanceof DbBuilder){
                $isSub          = true;
                $obBuilder = $values;
    		}
            
            if($isSub){
                return $this->whereInSub($column, $obBuilder, $logic, $not);
            }
        }

        if($obField = $this->getFieldByColumn($column)){
            $obField->condition(__FUNCTION__, compact("column", "values", "logic", "not"));
        }else{
            $this->arWheres[] = [
                "type"  => "in",
                "column"=> $column,
                "values"=> $values,
                "logic" => $logic,
                "not"   => $not
            ];
            
            $this->addParam($values, "where");
        }

		return $this;
	}

    public function whereNull($column, $logic = "AND", $not = false){
        if($obField = $this->getFieldByColumn($column)){
            $obField->condition(__FUNCTION__, compact("column", "logic", "not"));
        }else{
            $this->operation(__FUNCTION__, func_get_args());
        }
        
        return $this;
	}

    public function whereBetween($column, array $arValues, $logic = "AND", $not = false){
        if($obField = $this->getFieldByColumn($column)){
            $obField->condition(__FUNCTION__, compact("column", "values", "logic", "not"));
        }else{
            $this->operation(__FUNCTION__, func_get_args());
        }
        
        return $this;
	}
    
    public function whereExists($callback, $logic = "AND", $not = false){
        if($callback instanceof Closure){
            $obBuilder = new self($this->obEntity);
         
        	$callback($obBuilder);
        }else if($callback instanceof DbBuilder){
            $obBuilder = $callback;
        }
        
		if($obBuilder){
            $this->arWheres[] = [
                "type"      => "exists", 
                "builder"   => $obBuilder, 
                "logic"     => $logic, 
                "not"       => $not
            ];
            
    		$this->mergeParams($obBuilder);
        }
        
		return $this;
	}
    
    public function groupBy($arGroups){
        $arGroups = is_array($arGroups) ? $arGroups : func_get_args() ;
        
        foreach($arGroups AS $column){
            if($obField = $this->getFieldByColumn($column)){
                $obField->groupBy();
            }else{
                $this->arGroups[] = $column;
            }
        }
        
		return $this;
	}
    
    public function orderBy($column, $direction = "ASC"){
        $arOrders = !is_array($column) ? [$column => $direction] : $column ;

        foreach($arOrders AS $column => $direction){
            if($obField = $this->getFieldByColumn($column)){
                $obField->orderBy($direction);
            }else{
                $this->arOrders[$column] = strtoupper($direction) == "ASC" ? "ASC" : "DESC";
            }
        }
        
		return $this;
	}
        
    protected function validateFields(Result\BaseResult $obResult){
        $arErrors = [];
        
        $arData = $obResult->getData();
        
        
        foreach($this->arFields AS $fieldName => $obField){
            $value = isset($arData[$fieldName]) ? $arData[$fieldName] : null ;
            
            if(($obValidateResult = $obField->validate($value, $obResult)) !== true && $obValidateResult instanceof Field\Error){
                $arErrors[$fieldName] = $obValidateResult;
            }
        }

        return count($arErrors) ? $arErrors : true ;
    }
    
    protected function loadFields(){
        $arFields = $this->obEntity->getFields();
        
        $this->arFields = [];
        
        foreach($this->arFieldDispatchersClasses AS $fieldDispatcherClass){
            $obFieldDispatcher = new $fieldDispatcherClass($this);
            
            foreach($arFields AS $obField){
                $obFieldDispatcher->addField($obField);
            }
            
            $this->addFieldDispatcher($obFieldDispatcher);
            
            $this->arFields+= $obFieldDispatcher->getFields();
        }
        
        $this->arSelectFieldNames = array_keys($this->arFields);
    }
    
    protected function fetchInternal($oneRow = false){
        $obResult = new Result\SelectResult;
        $obResult->setSelectFieldNames($this->arSelectFieldNames);
        
        foreach($this->getFieldDispatchers() AS $obFieldDispatcher){
            $obFieldDispatcher->fetch($obResult, $oneRow);
        }
        
        return $obResult->getData();
    }
    
    public function fetchAll(){
        return $this->fetchInternal(false);
    }
    
    public function fetch(){
        $arItems = $this->fetchInternal(true);
        
        if(count($arItems)){
            return reset($arItems);
        }
        
        return false;
    }
    
    public function add(array $arData){
        $obResult = new Result\AddResult;
        $obResult->setData($arData)
                 ->setSuccess(true);
        
        if($this->obEntity->onBeforeAdd($obResult) === true){
            if(($validate = $this->validateFields($obResult)) === true){
                $hasErrors = false;
                
                foreach($this->getFieldDispatchers() AS $obFieldDispatcher){
                    if($obFieldDispatcher->add($obResult) !== true){
                        $hasErrors = true;
                        break;
                    }
                }
                
                if(!$hasErrors){
                    $arData = $obResult->getData();
                    
                    $id = $arData[$this->obEntity->getPk()];
                    
                    $this->obEntity->onAfterAdd($obResult, $id);

                    $arEvents = $this->obEntity->getEventNames();
                    
                    CEvent::trigger($arEvents["ADD"], $obResult, $id);
                }else{
                    $obResult->setSuccess(false)
                             ->setErrors(["query error"]);
                }
            }else{
                $obResult->setSuccess(false)
                         ->setErrors($validate);
            }
        }else{
            $obResult->setSuccess(false)
                     ->setErrors(["before canceled"]);
        }

        return $obResult;
    }
    
    public function update(array $arData){
        $obResult = new Result\UpdateResult;
        $obResult->setSuccess(true)
                 ->setChangedData($arData);
        
        $pk                 = $this->obEntity->getPk();
        $arIDs              = [];
        $numAffectedRows    = 0;
        
        foreach($this->select("*")->fetchAll() AS $arItem){
            $obResultItem = new Result\UpdateResult;
            $obResultItem->setItemData($arItem)
                         ->setChangedData($arData);

            $id = $arItem[$pk];
            
            if($this->obEntity->onBeforeUpdate($obResultItem, $id) === true){
                if(($validate = $this->validateFields($obResultItem)) === true){
                    $hasErrors = false;
                
                    foreach($this->getFieldDispatchers() AS $obFieldDispatcher){
                        if($obFieldDispatcher->update($id, $obResultItem) !== true){
                            $hasErrors = true;
                            break;
                        }
                    }
                    
                    if(!$hasErrors){
                        $numAffectedRows++;
                        $arIDs[] = $id;
                    }else{
                        $obResult->setSuccess(false)
                                 ->setErrors(["query error"]);
                        break;
                    }
                }else{
                    $obResult->setSuccess(false)
                             ->setErrors($validate);
                    break;
                }
            }
        }
        
        if($obResult->isSuccess()){
            $obResult->setID($arIDs);
            $obResult->setNumAffectedRows($numAffectedRows);
            
            $arEvents = $this->obEntity->getEventNames();
            
            $this->obEntity->onAfterUpdate($obResult);
                
            CEvent::trigger($arEvents["UPDATE"], $obResult);
        }
        
        return $obResult;
    }
    
    public function delete(){
        $obResult = new Result\DeleteResult;
        $obResult->setSuccess(true)
                 ->setData($arData);
        
        $pk                 = $this->obEntity->getPk();
        $arIDs              = [];
        $numAffectedRows    = 0;
        $arDeleteData       = [];
                
        foreach($this->select("*")->fetchAll() AS $arItem){
            $obResultItem = new Result\DeleteResult;
            
            $id = $arItem[$pk];
            
            if($this->obEntity->onBeforeDelete($obResultItem, $id) === true){
                $hasErrors = false;
            
                foreach($this->getFieldDispatchers() AS $obFieldDispatcher){
                    if($obFieldDispatcher->delete($id, $obResultItem) !== true){
                        $hasErrors = true;
                        break;
                    }
                }
                
                if(!$hasErrors){
                    $numAffectedRows++;
                    $arIDs[]            = $id;
                    $arDeleteData[$id]  = $obResultItem->getData();
                }else{
                    $obResult->setSuccess(false)
                             ->setErrors(["query error"]);
                    break;
                }
            }
        }
        
        if($obResult->isSuccess()){
            $obResult->setID($arIDs)
                     ->setNumAffectedRows($numAffectedRows)
                     ->setData($arDeleteData);
            
            $arEvents = $this->obEntity->getEventNames();
            
            $this->obEntity->onAfterDelete($obResult);
                
            CEvent::trigger($arEvents["DELETE"], $obResult);
        }
        
        return $obResult;
    }
}
?>