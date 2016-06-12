<?
namespace Entity\Field\Custom;

use \CUser;
use \CUserGroup;
use \Helpers\CArrayHelper;
use \DB\Builder AS DbBuilder;
use \DB\JoinCondition;
use \Entity\Builder;

class RelationField extends Field{
    protected $arInfo = array(
        "title" => "Поле связи"
    );
    
    public function getRenderer(){
        return new \Entity\Field\Renderer\ListRenderer($this);
    }
    
    public function condition($method, array $args = []){
        $arParams = $this->getParams();
        $obBuilder = $this->getDispatcher()->getBuilder();
        
        if(isset($arParams["conditionColumn"]) && isset($arParams["relation"]) && is_callable($arParams["relation"])){ //если есть связь
            $obJoinBuilder = new DbBuilder;
            
            call_user_func_array($arParams["relation"], [$obJoinBuilder, $this]); //выполняем всю привязку (джойны)
            
            foreach($obJoinBuilder->getJoins() AS $obJoin){
                $obBuilder->join($obJoin);
            }
            
            $args["column"] = $arParams["conditionColumn"];
            
            if(method_exists($obBuilder, $method)){
                call_user_func_array([$obBuilder, $method], $args);
            }
            
            $obBuilder->groupBy($this->getEntity()->getPk());
        }
    }
    
    public function onBeforeFetch(array $arItems = []){ //fetch by multi items
        $arParams = $this->getParams();
        
        if(isset($arParams["relation"]) && is_callable($arParams["relation"])){ //если есть связь
            $obJoinBuilder = new DbBuilder;
            
            call_user_func_array($arParams["relation"], [$obJoinBuilder, $this]); //выполняем всю привязку (джойны)
            
            $arJoins = $obJoinBuilder->getJoins();
            
            if(count($arJoins)){ //если есть джойны
                $obTable = array_shift($arJoins); //попытаемся сделать выборку из первой таблицы, а остальные доджойнить к ней
                
                $obBuilder  = new DbBuilder;
                $obBuilder->from($obTable->table);
                
                if($obTable->alias){
                    $obBuilder->alias($obTable->alias);
                }
                
                $arOns = $obTable->getOns();
                
                if(count($arOns)){
                    $relation   = $arOns[0]["relation"];
                    $reference  = $arOns[0]["reference"];
                    
                    foreach($arJoins AS $obJoin){ //доджойним остальные, но обязательно INNER JOIN
                        $obJoin->type = "INNER";
                        $obBuilder->join($obJoin);
                    }
                    
                    $obLastJoin = end($arJoins); //возьмем последний джойн, чтобы взять таблицу, из которой выберем все поля
                    
                    if(!$obLastJoin){
                        $obLastJoin = $obTable;
                    }
                    
                    $selectTableName = $obLastJoin->alias ? $obLastJoin->alias : $obLastJoin->table ;
                    $selectTableName.= '.*';
                    
                    $obBuilder->select([$selectTableName, $reference]); //обязательно выберем $reference поле, чтобы потом можно было сложить в массив по ключу
                    
                    if(strpos($relation, ".") !== false){ //очистим колонку связи relation от алиаса
                        list($alias, $relation) = explode(".", $relation, 2);
                    }
                    
                    $obBuilder->whereIn($reference, CArrayHelper::getColumn($arItems, $relation)); //делаем выборку по полю reference используя связь relation
                    
                    if(strpos($reference, ".") !== false){ //очистим колонку связи reference от алиаса
                        list($alias, $reference) = explode(".", $reference, 2);
                    }
                    
                    $arValues   = $obBuilder->fetchAll(); //получаем значения и теперь нужно разложить их в массив
                    $fieldAlias = $this->getFieldAlias() ? $this->getFieldAlias() : $this->getFieldName() ; //возьмем алиас для того, чтобы ложить по этому названию в массив
                    
                    foreach($arValues AS $arValue){
                        $arItems[$arValue[$reference]][$fieldAlias][] = $arValue; //далее кладем по полю reference (с которым связка), поле fieldAlias и его все значения
                    }
                }
            }
        }
        
        return $arItems;
    }
    
    public function add(array $arData){
        $pk = $this->getEntity()->getPk();
        $this->save($arData[$pk], $arData);
    }
    
    public function update($id, array $arData){
        $this->save($id, $arData);
    }
    
    public function save($id, array $arData){
        $obEntity   = $this->getEntity();
        $pk         = $obEntity->getPk();
        $fieldName  = $this->getFieldName();
        
        if(isset($arData[$fieldName])){
            $arFieldValues = $arData[$fieldName];
            
            if(is_numeric($arFieldValues)){
                $arFieldValues = [$arFieldValues];
            }
        }
        
        if(!is_array($arFieldValues)){
            $arFieldValues = [];
        }
        
        $this->setValues([$id], $arFieldValues);
    }
    
    public function delete($id, array $arData){
        $this->setValues([$id], []);
    }
    
    protected function setValues(array $arItemIds = [], array $arValueIDs = []){
        $arParams = $this->getParams();
        
        $obJoin = false;
        
        if(isset($arParams["relation"]) && is_callable($arParams["relation"])){ //если есть связь
            $obJoinBuilder = new DbBuilder;
            
            call_user_func_array($arParams["relation"], [$obJoinBuilder, $this]); //выполняем всю привязку (джойны)
            
            $arJoins = $obJoinBuilder->getJoins();
            
            if(count($arJoins)){ //если есть джойны
                $obJoin = reset($arJoins);
            }
        }
        /*
        (
            [type] => INNER
            [table] => user_group_value
            [alias] => ugv
            [arWheres] => Array
                (
                )
        
            [arOns] => Array
                (
                    [0] => Array
                        (
                            [relation] => user.id
                            [operator] => =
                            [reference] => ugv.user_id
                            [logic] => AND
                        )
        
                )
        
        )
        */
        if($obJoin){
            /*удаляем значения, которые не были переданы*/
            $arOns = $obJoin->getOns();
            
            if($arOns){
                $obBuilder = new DbBuilder;
                
                $obBuilder->from($obJoin->table);
                
                if($obJoin->alias){
                    $obBuilder->alias($obJoin->alias);
                }
                
                $obBuilder->whereIn($arOns[0]["reference"], $arUserIDs);
                
                if(count($arValueIDs)){
                    $obBuilder->whereNotIn("user_group_id", $arValueIDs);
                }
            }
            
            
                      
            
            
            $obBuilder->delete();
            /*удаляем значения, которые не были переданы*/
        }
        
        p($obJoin);exit;
    }
}
?>