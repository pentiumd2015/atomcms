<?
namespace DB\Manager\Validate;

use DB\Manager\Error;
use DB\Query;

class Unique implements IValidate{
    public function validate($value, $column, $result, $manager){
        $pk = $manager->getPk();

        if($result->isNewRecord()){ //если добавление, $id еще нет
            $existId = $manager->query()
                               ->select($pk)
                               ->where($column, $value)
                               ->limit(1)
                               ->fetchColumn();

            if($existId){
                return new Error($column, "Запись с таким значением уже существует [ID: " . $existId . "]", "not_unique");
            }
        }else{
            $itemIds = $manager->query()
                               ->select($pk)
                               ->where($column, $value)
                               ->limit(2)
                               ->getColumn();

            $itemData = $result->getItemData();

            foreach($itemIds AS $itemId){
                if($itemId != $itemData[$pk]){
                    return new Error($column, "Запись с таким значением уже существует [ID: " . $itemId . "]", "not_unique");
                }
            }
        }

        return true;
    }
}