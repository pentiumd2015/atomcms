<?
namespace DB;


class Manager{
    protected static $tableName;
    protected static $primaryKey = "id";

    protected static $events = [
        "ADD"       => "MANAGER.ADD",
        "UPDATE"    => "MANAGER.UPDATE",
        "DELETE"    => "MANAGER.DELETE",
    ];

    public static function getClass(){
        return get_called_class();
    }

    public static function query(){
        return (new Manager\Query(new static))->from(static::getTableName());
    }

    public static function getTableName(){
        return static::$tableName;
    }

    public static function setTableName($tableName){
        static::$tableName = $tableName;
    }

    public function getEventNames(){
        return static::$events;
    }

    public function setEventNames(array $events){
        static::$events = $events;
    }

    public static function getPk(){
        return static::$primaryKey;
    }

    public function onBeforeAdd($result){
        return true;
    }

    public function onBeforeValidate($result){
        return true;
    }

    public function onBeforeUpdate($result){
        return true;
    }

    public function onBeforeDelete($result){
        return true;
    }

    public function onAfterAdd($result){}
    public function onAfterValidate($result){}
    public function onAfterUpdate($result){}
    public function onAfterDelete($result){}

    public static function getById($id){
        return static::query()->where(static::getPk(), $id)->fetch();
    }

    public static function getAllById($Ids){
        return static::query()->whereIn(static::getPk(), $Ids)->fetchAll();
    }

    public function validators(){
        return [];
    }

    public static function add(array $data, $validate = true){
        return static::query()->add($data, $validate);
    }

    public static function update($id, array $data, $validate = true){
        return static::query()->where(static::getPk(), $id)->update($data, $validate);
    }

    public static function updateAll(array $Ids, array $data, $validate = true){
        return static::query()->whereIn(static::getPk(), $Ids)->update($data, $validate);
    }

    public static function delete($id){
        return static::query()->where(static::getPk(), $id)->delete();
    }

    public static function deleteAll(array $Ids){
        return static::query()->whereIn(static::getPk(), $Ids)->delete();
    }

    public static function search(array $params = []){
        $query = static::query();

        $sort = [];

        /*Apply Request Sort*/
        if(isset($params["sort"]) && is_array($params["sort"])){
            $order  = key($params["sort"]);
            $by     = reset($params["sort"]);

            if($order && is_string($order) && $by && is_string($by)){
                $sort = [$order => $by];
            }
        }

        $query->orderBy($sort);
        /*Apply Request Sort*/

        /*Apply Request Filter*/
        if(isset($params["filter"]) && is_array($params["filter"])){
            foreach($params["filter"] AS $fieldName => $value){
                $query->where($fieldName, $value);
            }
        }
        /*Apply Request Filter*/

        return $query;
    }
}