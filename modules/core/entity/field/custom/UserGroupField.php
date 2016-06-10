<?
namespace Entity\Field\Custom;

use \CUser;
use \CUserGroup;
use \CArrayHelper;
use \DB\Builder AS DbBuilder;
use \Entity\Result\AddResult;
use \Entity\Result\UpdateResult;
use \Entity\Result\DeleteResult;
use \Entity\Result\SelectResult;
use \Entity\Field\Renderer\ListRenderer;

class UserGroupField extends Field{
    protected $arInfo = array(
        "title" => "Группа пользователей"
    );
    
    public $values = [];
    
    public function getRenderer(){
        return new ListRenderer($this);
    }
    
    protected function getColumnName(){
        return "user_group_id";
    }
    
    public function condition($method, array $args = []){
        $obBuilder  = $this->getDispatcher()->getBuilder();
        $obEntity   = $obBuilder->getEntity();
        
        $gv = CUser::GROUP_VALUE_TABLE;

        $obBuilder->leftJoin($gv, $gv . ".user_id", "{{table}}." . $obEntity->getPk())
                  ->groupBy($obEntity->getPk());
        
        $args["column"] = $gv . "." . $this->getColumnName();
        
        if(method_exists($obBuilder, $method)){
            call_user_func_array([$obBuilder, $method], $args);
        }
    }
    
    public function orderBy($by){
        $obBuilder  = $this->getDispatcher()->getBuilder();
        $obEntity   = $obBuilder->getEntity();
        
        $gv = CUser::GROUP_VALUE_TABLE;
        
        $obBuilder->leftJoin($gv, $gv . ".user_id", "{{table}}." . $obEntity->getPk())
                  ->orderBy($gv . "." . $this->getColumnName(), $by)
                  ->groupBy($obEntity->getPk());
    }
    
    public function groupBy(){
        $obBuilder  = $this->getDispatcher()->getBuilder();
        $obEntity   = $obBuilder->getEntity();
        
        $gv = CUser::GROUP_VALUE_TABLE;
        
        $obBuilder->leftJoin($gv, $gv . ".user_id", "{{table}}." . $obEntity->getPk())
                  ->groupBy($gv . "." . $this->getColumnName());
    }
    
    public function onFetch(SelectResult $obResult){ //fetch by multi items
        $arItems = $obResult->getData();

        if(count($arItems)){
            $obEntity   = $this->getDispatcher()->getBuilder()->getEntity();
            $pk         = $obEntity->getPk();
            
            $arItems = CArrayHelper::index($arItems, $pk);
            
            $fieldAlias = $this->alias ? $this->alias : $this->name ;

            $arUserGroupValues = (new DbBuilder)->from(CUser::GROUP_VALUE_TABLE)
                                                ->whereIn("user_id", array_keys($arItems))
                                                ->fetchAll();

            foreach($arUserGroupValues AS $arUserGroupValue){
                $arItems[$arUserGroupValue["user_id"]][$fieldAlias][$arUserGroupValue["id"]] = $arUserGroupValue["user_group_id"];
            }

            $obResult->setData(array_values($arItems));
        }
    }
    
    public function add(AddResult $obResult){
        $arData = $obResult->getData();
        $pk     = $this->getDispatcher()->getBuilder()->getEntity()->getPk();
        $this->save($arData[$pk], $arData);
    }
    
    public function update($id, UpdateResult $obResult){
        $arData = $obResult->getData();
        $this->save($id, $arData);
    }
    
    public function save($id, array $arData){
        $obEntity   = $this->getDispatcher()->getBuilder()->getEntity();
        $pk         = $obEntity->getPk();
        
        if(isset($arData[$this->name])){
            $arFieldValues = $arData[$this->name];
            
            if(is_numeric($arFieldValues)){
                $arFieldValues = [$arFieldValues];
            }
        }
        
        if(!is_array($arFieldValues)){
            $arFieldValues = [];
        }
        
        CUser::setGroups([$id], $arFieldValues);
    }
    
    public function delete($id, DeleteResult $obResult){
        CUser::setGroups([$id], []);
    }
}
?>