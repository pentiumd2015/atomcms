<?
namespace DB\Manager;

use DB\Query AS DbQuery;
use DB\Connection;
use DB\Result\AddResult;
use DB\Result\UpdateResult;
use DB\Result\DeleteResult;
use DB\Manager;
use CEvent;
use PDOException;

class Query extends DbQuery{
    protected $manager;

    public function __construct(Manager $manager, Connection $connection = null){
        $this->manager = $manager;

        parent::__construct($connection);
    }

    public function getManager(){
        return $this->manager;
    }

    protected function insertInternal(array $values){
        return parent::insert($values);
    }

    protected function updateInternal(array $values){
        return parent::update($values);
    }

    protected function deleteInternal(){
        return parent::delete();
    }

    public function add(array $data, $validate = true){
        $result = new AddResult;
        $result->setData($data);

        if($this->manager->onBeforeAdd($result) === true){
            $this->manager->onBeforeValidate($result);

            if(!$validate || (($validateResult = $this->validate($result)) === true)){
                $this->manager->onAfterValidate($result);

                if($id = $this->insertInternal($result->getData())){
                    $result->setSuccess(true)
                           ->setId($id);

                    $this->manager->onAfterAdd($result, $id);

                    $events = $this->manager->getEventNames();

                    CEvent::trigger($events["ADD"], [$result, $id]);
                }else{
                    $result->setSuccess(false)
                           ->setErrors(["query error"]);
                }
            }else{
                $result->setSuccess(false)
                       ->setErrors($validateResult);
            }
        }

        return $result;
    }

    protected function updateOne($id, $data, $item, $validate = true){
        $result = new UpdateResult;
        $result->setData($data)
               ->setId($id)
               ->setItemData($item);

        if($this->manager->onBeforeUpdate($result, $id) === true){
            $this->manager->onBeforeValidate($result);

            if(!$validate || (($validateResult = $this->validate($result, array_keys($data))) === true)){
                $this->manager->onAfterValidate($result);

                $this->updateInternal($result->getData());

                $result->setSuccess(true);
            }else{
                $result->setSuccess(false)
                       ->setErrors($validateResult);
            }
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

    protected function deleteOne($id, $item){
        $result = new DeleteResult;
        $result->setData($item)
               ->setId($id);

        if($this->manager->onBeforeDelete($result, $id) === true){
            $result->setSuccess(true);
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

            if(!$hasErrors){
                $this->deleteInternal();

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

    public function validate($result, array $columnNames = []){
        $validateErrors = [];
        $validators     = $this->manager->validators();

        if(!count($columnNames)){
            $columnNames = array_keys($validators);
        }

        foreach($columnNames AS $column){
            if(!isset($validators[$column])){
                continue;
            }

            $columnValidators = is_array($validators[$column]) ? $validators[$column] : [$validators[$column]] ;

            foreach($columnValidators AS $key => $validator){
                $value = $result->getValue($column, null);

                if($validator instanceof Manager\Validate\IValidate){
                    if(($validate = $validator->validate($value, $column, $result, $this->manager)) !== true){
                        $validateErrors[$column] = $validate;
                        break;
                    }
                }else if(is_callable($validator) && (($validate = $validator($value, $column, $result, $this->manager)) !== true)){
                    if($validate instanceof Error){
                        $validateErrors[$column] = $validate;
                    }else{
                        $validateErrors[$column] = new Error($column, (is_string($validate) ? $validate : "Неверное значение поля"), is_numeric($key) ? Error::ERROR_INVALID : $key) ;
                    }

                    break;
                }
            }
        }

        return count($validateErrors) ? $validateErrors : true ;
    }
}