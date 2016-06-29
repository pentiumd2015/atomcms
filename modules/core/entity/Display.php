<?
namespace Entity;

use Helpers\CJson;
use DB\Manager AS DbManager;


class Display extends DbManager{
    protected static $tableName = "new_entity_display";
    protected $manager;

    public function __construct(Manager $manager = null){
        if($manager !== null){
            $this->manager = $manager;
        }
    }

    public function setManager(Manager $manager){
        $this->manager = $manager;

        return $this;
    }

    public function getManager(){
        return $this->manager;
    }
    
    public function getFieldNames(){
        $fields = [];
        
        foreach($this->manager->query()->getFields() AS $fieldName => $field){
            if($field->visible){
                $fields[$fieldName] = $field;
            }
        }
        
        return $fields;
    }
    
    public function getEntityDisplayData($userId, $type){
        $tmpEntityDisplay = $this->query()
                                 ->select("data", "user_id")
                                 ->where("entity_id", $this->manager->getTableName())
                                 ->whereIn("user_id", [0, $userId])
                                 ->orderBy("user_id", "DESC")
                                 ->limit(2)
                                 ->fetchAll();

        foreach($tmpEntityDisplay AS $tmpEntityDisplayItem){
            $displayData = CJson::decode($tmpEntityDisplayItem["data"], true);
            
            if(is_array($displayData[$type]) && count($displayData[$type])){
                return $displayData[$type];
            }
        }
        
        return false;
    }
    
    public function getDisplayListFields($userId = 0){
        $fields = $this->manager->query()->getFields();

        $displayFields = [];

        if($entityDisplay = $this->getEntityDisplayData($userId, "list")){
            foreach($entityDisplay AS $displayField){
                if(($fieldName = $displayField["field"]) && isset($fields[$fieldName]) && $fields[$fieldName]->visible){
                    $displayFields[] = $fieldName;
                }
            }
        }else{
            foreach($this->getFieldNames() AS $fieldName){
                $displayFields[] = $fieldName;
            }
        }

        return $displayFields;
    }
    
    public function getDisplayFilterFields($userId = 0){
        $fields = $this->manager->query()->getFields();

        $displayFields = [];

        if($entityDisplay = $this->getEntityDisplayData($userId, "filter")){
            foreach($entityDisplay AS $displayField){
                if(($fieldName = $displayField["field"]) && isset($fields[$fieldName]) && $fields[$fieldName]->visible && $fields[$fieldName]->filterable){
                    $displayFields[] = $fieldName;
                }
            }
        }else{
            foreach($this->getFieldNames() AS $fieldName){
                $displayFields[] = $fieldName;
            }
        }
        
        return $displayFields;
    }
    
    public function getDisplayDetailFields($userId = 0){
        $fields = $this->manager->query()->getFields();

        $displayFields  = [];

        if($entityDisplay = $this->getEntityDisplayData($userId, "detail")){
            foreach($entityDisplay AS $index => $tab){
                $tabFields = [];
                
                if(is_array($tab["fields"])){
                    foreach($tab["fields"] AS $displayField){
                        if(($fieldName = $displayField["field"]) && isset($fields[$fieldName]) && $fields[$fieldName]->visible){
                            $tabFields[] = $fieldName;
                        }
                    }
                }
                
                $displayFields[] = [
                    "name"  => "tab_" . ($index + 1),
                    "title" => $tab["title"],
                    "fields"=> $tabFields
                ];
            }
        }else{
            $tabFields = [];

            foreach($this->getFieldNames() AS $fieldName){
                $tabFields[] = $fieldName;
            }

            $displayFields[] = [
                "name"      => "tab_main",
                "title"     => "Основное",
                "fields"    => $tabFields
            ];
        }

        return $displayFields;
    }
    
    public function setDisplayFields(array $data = [], $type, $userId = 0){
        $tmpEntityDisplay = $this->query()
                                   ->select("data")
                                   ->where("entity_id", $this->manager->getTableName())
                                   ->where("user_id", $userId)
                                   ->limit(1)
                                   ->fetch();
        
        if($tmpEntityDisplay){
            $entityDisplay = CJson::decode($tmpEntityDisplay["data"], true);
            
            if(count($data)){
                $entityDisplay[$type] = $data;
            }else{
                unset($entityDisplay[$type]);
            }
            
            return $this->query()
                        ->where("entity_id", $this->manager->getTableName())
                        ->where("user_id", $userId)
                        ->update([
                           "data" => CJson::encode($entityDisplay)
                        ]);
        }else{
            return $this->query()
                        ->insert([
                            "entity_id" => $this->manager->getTableName(),
                            "user_id"   => $userId,
                            "data"      => CJson::encode([$type => $data])
                        ]);
        }
    }
}