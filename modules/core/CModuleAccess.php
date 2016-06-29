<?
use DB\Manager;
use DB\Manager\Validate\Required as ValidateRequired;
use DB\Manager\Validate\RegExp as ValidateRegExp;
use DB\Manager\Validate\Unique as ValidateUnique;
use DB\Query AS DbQuery;


class CModuleAccess extends Manager{
    protected static $tableName     = "module_access";
    protected static $primaryKey    = "id";

    const VALUE_TABLE = "module_access_value";

    public function validators(){
        return [
            "module_access_group_id" => new ValidateRequired,
            "title" => new ValidateRequired,
            "code" => [
                new ValidateRequired,
                new ValidateRegExp("/^[a-zA-Z0-9-_]+$/si", "Поле код должно состоять из латинских символов и цифр"),
                new ValidateUnique,
            ],
        ];
    }

    public static function valueQuery(){
        return (new DbQuery)->from(self::VALUE_TABLE);
    }

    public static function addAccessValue($userGroupIds = [], $moduleAccessCodes = []){
        $userGroupIds       = is_array($userGroupIds) ? $userGroupIds : [$userGroupIds] ;
        $moduleAccessCodes  = is_array($moduleAccessCodes) ? $moduleAccessCodes : [$moduleAccessCodes] ;

        $userGroupIds       = CUserGroup::query()->select("id")->whereIn("id", $userGroupIds)->getColumn();
        $moduleAccessIds    = self::query()->select("id")->whereIn("code", $moduleAccessCodes)->getColumn();

        $insert = [];

        foreach($userGroupIds AS $userGroupId){
            foreach($moduleAccessIds AS $moduleAccessId){
                $valueExist = static::valueQuery()->select("id")
                                                  ->where("user_group_id", $userGroupId)
                                                  ->where("module_access_id", $moduleAccessId)
                                                  ->limit(1)
                                                  ->fetch();

                if(!$valueExist){
                    $insert[] = [
                        "user_group_id"     => $userGroupId,
                        "module_access_id"  => $moduleAccessId
                    ];
                }
            }
        }

        if(count($insert)){
            static::valueQuery()->insert($insert);
        }
    }

    public static function deleteAccessValue($userGroupIds = [], $moduleAccessCodes = []){
        $userGroupIds       = is_array($userGroupIds) ? $userGroupIds : [$userGroupIds] ;
        $moduleAccessCodes  = is_array($moduleAccessCodes) ? $moduleAccessCodes : [$moduleAccessCodes] ;

        $userGroupIds       = CUserGroup::query()->select("id")->whereIn("id", $userGroupIds)->getColumn();
        $moduleAccessIds    = self::query()->select("id")->whereIn("code", $moduleAccessCodes)->getColumn();

        foreach($userGroupIds AS $userGroupId){
            foreach($moduleAccessIds AS $moduleAccessId){
                static::valueQuery()->where("user_group_id", $userGroupId)
                                    ->where("module_access_id", $moduleAccessId)
                                    ->delete();
            }
        }
    }
}