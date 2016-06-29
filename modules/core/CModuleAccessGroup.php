<?
use DB\Manager;
use DB\Manager\Validate\Required as ValidateRequired;


class CModuleAccessGroup extends Manager{
    protected static $tableName     = "module_access_group";
    protected static $primaryKey    = "id";

    public function validators(){
        return [
            "title"     => new ValidateRequired,
            "module_id" => new ValidateRequired,
        ];
    }
}