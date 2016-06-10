<?
namespace Models;

use \Helpers\CArrayHelper;
use \Helpers\CArrayFilter;

class UserGroupAccessValue extends \DB\Manager{
    static protected $_table    = "user_group_access_value";
    static protected $_pk       = "id";
    
    static public function setValues($userGroupID, $arUserGroupAccessIDs = array()){        
        $deleteSQL = "user_group_id=?";
        
        if(is_array($arUserGroupAccessIDs)){
            $arUserGroupAccessIDs = CArrayHelper::map($arUserGroupAccessIDs, "intval");
            $arUserGroupAccessIDs = CArrayFilter::filter($arUserGroupAccessIDs);
            
            if(count($arUserGroupAccessIDs)){
                $deleteSQL.= " AND user_group_access_id NOT IN(" . implode(", ", $arUserGroupAccessIDs) . ")";
            }
        }
        
        static::deleteByParams($deleteSQL, array($userGroupID));
        
        $arUserAccessValues = static::findAll("user_group_id=?", array($userGroupID));
        $arUserAccessValues = CArrayHelper::index($arUserAccessValues, "user_group_access_id");
        
        if(is_array($arUserGroupAccessIDs)){
            foreach($arUserGroupAccessIDs AS $userGroupAccessID){
                if(!isset($arUserAccessValues[$userGroupAccessID])){
                    static::add(array(
                        "user_group_id"         => $userGroupID,
                        "user_group_access_id"  => $userGroupAccessID
                    ));
                }
            }
        }
    }
}
?>