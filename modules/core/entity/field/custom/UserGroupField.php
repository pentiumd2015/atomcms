<?
namespace Entity\Field\Custom;

use CUser;
use Helpers\CArrayHelper;
use Helpers\CArrayFilter;
use DB\Query AS DbQuery;
use Entity\Field\Renderer\ListRenderer;

class UserGroupField extends Field{
    protected $info = array(
        "title" => "Группа пользователей"
    );
    
    public $values;
    
    public function __construct($name, $params = []){
        parent::__construct($name, $params);
        
        $safeParams = [ //присваиваем только разрешенные параметры
            "values"
        ];
        
        foreach($safeParams AS $param){
            if(isset($params[$param])){
                $this->{$param} = $params[$param];
            }
        }
    }
    
    public function getRenderer(){
        return new ListRenderer($this);
    }
    
    protected function getColumnName(){
        return "user_group_id";
    }
    
    public function condition($method, array $args = []){
        $query  = $this->getDispatcher()->getQuery();
        $entity   = $query->getManager();
        
        $gv = CUser::GROUP_VALUE_TABLE;

        $query->leftJoin($gv, $gv . ".user_id", "{{table}}." . $entity->getPk())
                  ->groupBy($entity->getPk());
        
        $args["column"] = $gv . "." . $this->getColumnName();
        
        if(method_exists($query, $method)){
            call_user_func_array([$query, $method], $args);
        }
    }
    
    public function orderBy($by){
        $query  = $this->getDispatcher()->getQuery();
        $entity   = $query->getManager();
        
        $gv = CUser::GROUP_VALUE_TABLE;
        
        $query->leftJoin($gv, $gv . ".user_id", "{{table}}." . $entity->getPk())
                  ->orderBy($gv . "." . $this->getColumnName(), $by)
                  ->groupBy($entity->getPk());
    }
    
    public function groupBy(){
        $query  = $this->getDispatcher()->getQuery();
        $entity   = $query->getManager();
        
        $gv = CUser::GROUP_VALUE_TABLE;
        
        $query->leftJoin($gv, $gv . ".user_id", "{{table}}." . $entity->getPk())
                  ->groupBy($gv . "." . $this->getColumnName());
    }
    
    public function onFetch($result){ //fetch by multi items
        $items = $result->getData();

        if(count($items)){
            $entity   = $this->getDispatcher()->getQuery()->getManager();
            $pk         = $entity->getPk();
            
            $items = CArrayHelper::index($items, $pk);
            
            $fieldAlias = $this->alias ? $this->alias : $this->name ;

            $userGroupValues = (new DbQuery)->from(CUser::GROUP_VALUE_TABLE)
                                                ->whereIn("user_id", array_keys($items))
                                                ->fetchAll();

            foreach($userGroupValues AS $userGroupValue){
                $items[$userGroupValue["user_id"]][$fieldAlias][$userGroupValue["id"]] = $userGroupValue["user_group_id"];
            }

            $result->setData(array_values($items));
        }
    }
    
    public function add($result){
        $data = $result->getData();
        $pk     = $this->getDispatcher()->getQuery()->getManager()->getPk();
        $this->save($data[$pk], $data);
    }
    
    public function update($id, $result){
        $data = $result->getData();
        $this->save($id, $data);
    }
    
    public function save($id, array $data){
        $fieldValues = [];
        
        if(!empty($data[$this->name])){
            $fieldValues = $data[$this->name];
            
            if(is_numeric($fieldValues)){
                $fieldValues = [$fieldValues];
            }
        }

        CUser::setGroups([$id], array_filter($fieldValues));
    }
    
    public function delete($id, $result){
        CUser::setGroups([$id], []);
    }
}