<?
namespace Entity;


use DB\Query AS DbQuery;
use DB\Connection;
use DB\Manager\Query AS ManagerQuery;
use DB\Manager\Result\AddResult;
use DB\Manager\Result\UpdateResult;
use DB\Manager\Result\DeleteResult;
use Entity\Result\SelectResult AS SelectResult;
use PDOException;
use Helpers\CArrayHelper;
use CEvent;
use Closure;

class Query extends ManagerQuery{
    protected $selectFieldNames = [];
    protected $fields = [];
    
    protected $fieldDispatchersClasses = [
        "Entity\Field\Scalar\FieldDispatcher",
        "Entity\Field\Custom\FieldDispatcher",
        "Entity\Field\Additional\FieldDispatcher"
    ];
    
    protected $fieldDispatchers = [];
    
    public function __construct(Manager $manager, Connection $connection = null){
        parent::__construct($manager, $connection);
        
        $this->loadFields();
    }
    
    protected function addFieldDispatcher(Field\BaseFieldDispatcher $fieldDispatcher){
        $this->fieldDispatchers[] = $fieldDispatcher;
    }
    
    protected function getFieldDispatchers(){
        return $this->fieldDispatchers;
    }
    
    public function getFields(){
        return $this->fields;
    }

    public function internal($method, array $args = []){
        $method = "parent::" . $method;

        if(is_callable($method)){
            return call_user_func_array($method, $args);
        }

        return false;
    }
    
    protected function tableChanged(){
        return $this->manager->getTableName() != $this->from;
    }
    
    protected function getFieldByColumn($column){
        if($this->tableChanged() || !is_scalar($column)){ //если таблица изменилась, то это уже не поле сущности
            return false;
        }

        list($tableName) = $this->getSelectColumnData($column);
        
        if($tableName){
            $managerTable = $this->alias ? $this->alias : $this->from;
            
            if($tableName != $managerTable){
                return false;
            }
        }

        return isset($this->fields[$column]) ? $this->fields[$column] : false ;
    }
    
    protected function getSelectColumnsSql(){
        $columns = []; //колонки для выборки в SELECT ... скалярные значения
        $this->selectFieldNames = []; //все поля, даже не скалярные, чтобы подгрузить потом при событии onFetch
        
        $needSelectPk  = false;
        $needAllFields = false;
        
        $managerTable = $this->alias ? $this->alias : $this->from;
        
        if(!count($this->columns)){
            $needAllFields = true;
        }else{
            foreach(["*", $managerTable . ".*"] AS $allColumn){
                if(($key = array_search($allColumn, $this->columns)) !== false){
                    unset($this->columns[$key]);
                    $needAllFields = true;
                }
            }
        }
        
        if($needAllFields){ //if all fields need for load
            foreach($this->fields AS $fieldName => $field){
                if(!in_array($fieldName, $this->columns)){
                    $this->columns[] = $fieldName;
                }
            }
        }

        foreach($this->columns AS $column){
            if($this->isExpression($column)){
                $columns[] = $column->getValue();
                
                continue;
            }
            
            list($columnTable, $columnName, $columnAlias) = $this->getSelectColumnData($column);

            if((!$columnTable || $columnTable == $managerTable) && isset($this->fields[$columnName])){
                $field = $this->fields[$columnName];
                
                if($columnAlias){
                    $field->setAlias($columnAlias);
                }
                
                if(($column = $field->onSelect()) !== false){
                    if($this->isExpression($column)){
                        $columns[] = $column->getValue() . " AS " . $this->connection->quoteColumn($columnAlias ? $columnAlias : $columnName); //здесь нельзя применять prepareColumn, поскольку он добавит таблицу
                    }else{
                        $columns[] = $this->prepareColumn($column . ($columnAlias ? " AS " . $columnAlias : ""));
                    }
                    
                    $needSelectPk = true;
                }
                
                $this->selectFieldNames[] = $columnName; //кладем все поля сущности
            }else{ //кладем остальные поля не сущности, это могут быть поля из джоинов и прочие, любые
                $columns[] = $this->connection->quoteColumn($column);
            }
        }
        
        //если нужен pk для диспетчеров кроме скалярного
        if($needSelectPk){
            $pk = $this->manager->getPk();
            
            if(!in_array($pk, $this->selectFieldNames)){
                $columns[] = $this->prepareColumn($pk);
                $this->selectFieldNames[] = $pk;
            }
        }
        
        return count($columns) ? implode(", ", $columns) : "*" ;
    }
    
    public function where($column, $operator = "=", $value = null, $logic = "AND"){
        if(is_object($column)){//if nested query
            $isNested = false;
            
            if($column instanceof Closure){
                $isNested   = true;
                $query      = new self($this->manager, $this->connection);
                $query->from($this->manager->getTableName());
                
        		$column($query);
    		}else if($column instanceof DbQuery){
                $isNested   = true;
                $query      = $column;
    		}
            
            if($isNested){
                return $this->whereNested($query, $logic);
            }
        }
        
        if(func_num_args() == 2 || $value == null || !isset($this->operators[strtoupper($operator)])){
			$value      = $operator;
            $operator   = "=";
		}
        
        /*if subquery*/
        if(is_object($value)){//if subquery
            $isSub = false;
            
            if($value instanceof Closure){ 
                $isSub  = true;
                $query  = new self($this->manager, $this->connection);
                
        		$value($query);
    		}else if($value instanceof DbQuery){
                $isSub  = true;
                $query  = $value;
    		}
            
            if($isSub){
                return $this->whereSub($column, $operator, $query, $logic);
            }
        }
        /*if subquery*/

        if($field = $this->getFieldByColumn($column)){ 
            $field->condition(__FUNCTION__, compact("column", "operator", "value", "logic"));
        }else{
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
        }

		return $this;
	}

    public function whereIn($column, $values, $logic = "AND", $not = false){
        if(is_object($values)){ //if subquery
            $isSub = false;
            
            if($values instanceof Closure){
                $isSub  = true;
                $query  = new self($this->manager, $this->connection);
                
                $values($query);
    		}else if($values instanceof DbQuery){
                $isSub  = true;
                $query  = $values;
    		}
            
            if($isSub){
                return $this->whereInSub($column, $query, $logic, $not);
            }
        }

        if($field = $this->getFieldByColumn($column)){
            $field->condition(__FUNCTION__, compact("column", "values", "logic", "not"));
        }else{
            $this->wheres[] = [
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
        if($field = $this->getFieldByColumn($column)){
            $field->condition(__FUNCTION__, compact("column", "logic", "not"));
        }else{
            parent::whereNull($column, $logic, $not);
        }
        
        return $this;
	}

    public function whereBetween($column, array $values, $logic = "AND", $not = false){
        if($field = $this->getFieldByColumn($column)){
            $field->condition(__FUNCTION__, compact("column", "values", "logic", "not"));
        }else{
            parent::whereBetween($column, $values, $logic, $not);
        }
        
        return $this;
	}
    
    public function whereExists($callback, $logic = "AND", $not = false){
        $query = null;

        if($callback instanceof Closure){
            $query = new self($this->manager, $this->connection);
         
        	$callback($query);
        }else if($callback instanceof DbQuery){
            $query = $callback;
        }
        
		if($query){
            $this->wheres[] = [
                "type"  => "exists", 
                "query" => $query, 
                "logic" => $logic, 
                "not"   => $not
            ];
            
    		$this->mergeParams($query);
        }
        
		return $this;
	}
    
    public function groupBy($groups){
        $groups = is_array($groups) ? $groups : func_get_args() ;
        
        foreach($groups AS $column){
            if($field = $this->getFieldByColumn($column)){
                $field->groupBy();
            }else{
                $this->groups[] = $column;
            }
        }
        
		return $this;
	}
    
    public function orderBy($column, $direction = "ASC"){
        $orders = !is_array($column) ? [$column => $direction] : $column ;

        foreach($orders AS $column => $direction){
            if($field = $this->getFieldByColumn($column)){
                $field->orderBy($direction);
            }else{
                $this->orders[$column] = strtoupper($direction) == "ASC" ? "ASC" : "DESC";
            }
        }
        
		return $this;
	}

    protected function loadFields(){
        $fields = $this->manager->getFields();

        $this->fields = [];
        
        foreach($this->fieldDispatchersClasses AS $fieldDispatcherClass){
            $fieldDispatcher = new $fieldDispatcherClass($this);
            
            foreach($fields AS $field){
                $fieldDispatcher->addField($field);
            }
            
            $this->addFieldDispatcher($fieldDispatcher);
            
            $this->fields+= $fieldDispatcher->getFields();
        }

        $this->selectFieldNames = array_keys($this->fields);
    }
    
    public function fetchAll(){
        $items = parent::fetchAll();
        
        if($this->tableChanged()){
            return $items;
        }
        
        $result = new SelectResult;
        $result->setData($items);
        $result->setSelectFieldNames($this->selectFieldNames);
        
        foreach($this->getFieldDispatchers() AS $fieldDispatcher){
            $fieldDispatcher->fetch($result);
        }

        $items = $result->getData();

        if(count($items) && $this->indexBy && ($item = reset($items)) && array_key_exists($this->indexBy, $item)){
            return CArrayHelper::index($items, $this->indexBy);
        }

        return $items;
    }
    
    public function fetch(){
        $item = parent::fetch();
        
        if($this->tableChanged()){
            return $item;
        }
        
        $result = new SelectResult;
        $result->setData($item ? [$item] : []);
        $result->setSelectFieldNames($this->selectFieldNames);
        
        foreach($this->getFieldDispatchers() AS $fieldDispatcher){
            $fieldDispatcher->fetch($result);
        }

        $items = $result->getData();
        
        if(count($items)){
            return reset($items);
        }
        
        return false;
    }
    
    public function fetchColumn($columnNumber = 0){
        if($this->tableChanged()){
            return parent::fetchColumn($columnNumber);
        }

        if($item = $this->limit(1)->fetch()){
            $keys = array_keys($item);

            return $item[$keys[$columnNumber]];
        }
        
        return false;
    }

    public function validate($result, array $fieldNames = []){
        $validateErrors = [];

        foreach($fieldNames AS $fieldName){
            if(!isset($this->fields[$fieldName])){
                continue;
            }

            $field = $this->fields[$fieldName];
            $value = $result->getValue($fieldName, null);

            if($value === null && $field->defaultValue !== null){
                $value = $field->defaultValue;

                $result->setDataValues([
                    $field->getName() => $value
                ]);
            }
            
            if(($validateResult = $field->validate($value, $result)) !== true){
                $validateErrors[$fieldName] = $validateResult;
            }
        }

        return count($validateErrors) ? $validateErrors : true ;
    }

    protected function updateOne($id, $data, $item, $validate = true){
        $result = new UpdateResult;
        $result->setData($data)
               ->setId($id)
               ->setItemData($item);

        $this->manager->onBeforeValidate($result);

        if(!$validate || (($validateResult = $this->validate($result, array_keys($data))) === true)){
            $this->manager->onAfterValidate($result);

            $hasErrors = false;

            foreach($this->getFieldDispatchers() AS $fieldDispatcher){
                if($fieldDispatcher->update($id, $result) !== true){
                    $hasErrors = true;
                    break;
                }
            }

            if(!$hasErrors){
                $result->setSuccess(true);
            }else{
                $result->setSuccess(false)
                       ->setErrors(["query error"]);
            }
        }else{
            $result->setSuccess(false)
                   ->setErrors($validateResult);
        }

        return $result;
    }

    protected function deleteOne($id, $item){
        $result = new DeleteResult;
        $result->setData($item)
               ->setId($id);

        $hasErrors = false;

        foreach($this->getFieldDispatchers() AS $fieldDispatcher){
            if($fieldDispatcher->delete($id, $result) !== true){
                $hasErrors = true;
                break;
            }
        }

        if(!$hasErrors){
            $result->setSuccess(true);
        }else{
            $result->setSuccess(false)
                   ->setErrors(["query error"]);
        }

        return $result;
    }
    
    public function add(array $data, $validate = true){
        $result = new AddResult;
        $result->setData($data);
        
        if($this->manager->onBeforeAdd($result) === true){
            $this->manager->onBeforeValidate($result);

            if(!$validate || (($validateResult = $this->validate($result)) === true)){
                $this->manager->onAfterValidate($result);

                try{
                    $this->connection->beginTransaction();
                    
                    $hasErrors = false;
                    
                    foreach($this->getFieldDispatchers() AS $fieldDispatcher){
                        if($fieldDispatcher->add($result) !== true){
                            $hasErrors = true;
                            break;
                        }
                    }
                    
                    if(!$hasErrors){
                        $this->connection->commit();
                        
                        $data = $result->getData();
                        
                        $id = $data[$this->manager->getPk()];
                        
                        $result->setSuccess(true)
                               ->setId($id);

                        $this->manager->onAfterAdd($result);
    
                        $events = $this->manager->getEventNames();
                        
                        CEvent::trigger($events["ADD"], $result, $id);
                    }else{
                        $this->connection->rollBack();

                        $result->setSuccess(false)
                               ->setErrors(["query error"]);
                    }
                }catch(PDOException $e){
                    $this->connection->rollBack();

                    $result->setSuccess(false)
                           ->setErrors([$e]);
                }
            }else{
                $result->setSuccess(false)
                       ->setErrors($validateResult);
            }
        }else{
            $result->setSuccess(false)
                   ->setErrors(["before canceled"]);
        }

        return $result;
    }
    
    public function update(array $data, $validate = true){
        $result = new UpdateResult;
        $result->setData($data);
        
        $pk                 = $this->manager->getPk();
        $Ids                = [];
        $numAffectedRows    = 0;

        try {
            $items = $this->select("*")->fetchAll();

            $result->setItemData($items);

            $this->connection->beginTransaction();

            $hasErrors = false;

            if($this->manager->onBeforeUpdate($result) === true) {
                foreach($items AS $item){
                    $id = $item[$pk];

                    $resultItem = $this->updateOne($id, $data, $item, $validate);

                    if(!$resultItem->isSuccess()){
                        $hasErrors = true;

                        $result->setSuccess(false)
                               ->setId($id)
                               ->setErrors($resultItem->getErrors());

                        $this->connection->rollBack();

                        break;
                    }

                    $numAffectedRows++;
                    $Ids[] = $id;
                }
            }

            if(!$hasErrors){
                $this->connection->commit();

                $result->setSuccess(true)
                       ->setId($Ids)
                       ->setNumAffectedRows($numAffectedRows);

                $this->manager->onAfterUpdate($result);

                $events = $this->manager->getEventNames();

                CEvent::trigger($events["UPDATE"], $result);
            }
        }catch(PDOException $e){
            $this->connection->rollBack();

            $result->setSuccess(false)
                   ->setErrors([$e]);
        }
        
        return $result;
    }
    
    public function delete(){
        $result = new DeleteResult;

        $pk                 = $this->manager->getPk();
        $Ids                = [];
        $numAffectedRows    = 0;

        try{
            $items = $this->select("*")->fetchAll();

            $result->setData($items);

            $this->connection->beginTransaction();

            $hasErrors = false;

            if($this->manager->onBeforeDelete($result) === true){
                foreach($items AS $item){
                    $id = $item[$pk];

                    $resultItem = $this->deleteOne($id, $item);

                    if(!$resultItem->isSuccess()){
                        $hasErrors = true;

                        $result->setSuccess(false)
                               ->setErrors($resultItem->getErrors());

                        $this->connection->rollBack();

                        break;
                    }

                    $numAffectedRows++;
                    $Ids[] = $id;
                }
            }

            if(!$hasErrors){
                $this->connection->commit();

                $result->setSuccess(true)
                       ->setId($Ids)
                       ->setNumAffectedRows($numAffectedRows);

                $this->manager->onAfterDelete($result);

                $events = $this->manager->getEventNames();

                CEvent::trigger($events["DELETE"], $result);
            }
        }catch(PDOException $e) {
            $this->connection->rollBack();

            $result->setSuccess(false)
                   ->setErrors([$e]);
        }
        
        return $result;
    }
}