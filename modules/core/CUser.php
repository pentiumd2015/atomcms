<?
use \DB\Connection;
use \DB\Builder AS DbBuilder;

use \Entity\Field\Scalar\IntegerField;
use \Entity\Field\Scalar\StringField;
use \Entity\Field\Scalar\BooleanField;
use \Entity\Field\Scalar\DateTimeField;
use \Entity\Field\Scalar\PasswordField;
use \Entity\Field\Scalar\ListField;

use \Entity\Field\Validate\Unique as ValidateUnique;
use \Entity\Field\Validate\Length as ValidateLength;
use \Entity\Field\Validate\Email as ValidateEmail;

use \Entity\Field\Custom\UserGroupField;
use \Entity\Field\Custom\RelationField;

use \DB\Expr;

class CUser extends \Entity\Entity{
    static protected $_table    = "user";
    static protected $_pk       = "id";
    
    static protected $arInfo = array(
        "title" => "Пользователи"
    );
    
    static protected $arEvents = array(
        "ADD"       => "USER.ADD",
        "UPDATE"    => "USER.UPDATE",
        "DELETE"    => "USER.DELETE",
    );
    
    protected $arData = array();
    
    const GROUP_VALUE_TABLE = "user_group_value";
    
    public function getFields(){
        return array(
            new IntegerField("id", array(
                "title"         => "ID",
                "required"      => true,
                "primary"       => true,
                "disabled"      => true,
                "description"   => "Идентификатор пользователя"
            )),
            new StringField("login", array(
                "title"         => "Логин",
                "required"      => true,
                "description"   => "Логин пользователя используется при авторизации на сайте",
                "validate"      => function(){
                    return array(
                        new ValidateUnique(),
                        new ValidateLength(6, 40)
                    );
                }
            )),
            new PasswordField("password", array(
                "title"     => "Пароль",
                "required"  => true,
                "algorithm" => array($this, "getHash"),
                "validate"  => function(){
                    return array(
                        new ValidateLength(6, 40)
                    );
                }
            )),
            new StringField("fio", array(
                "title" => "ФИО"
            )),
            new StringField("email", array(
                "title"     => "E-mail",
                "validate"  => function(){
                    return array(
                        new ValidateEmail()
                    );
                }
            )),
            new ListField("gender", array(
                "title"     => "Пол",
                "values"    => array(
                    "0" => "Не выбран",
                    "M" => "Мужской",
                    "W" => "Женский"
                )
            )),
            new BooleanField("active", array(
                "title" => "Активность"
            )),
            new DateTimeField("date_add", array(
                "title"     => "Дата добавления",
                "disabled"  => true
            )),
            new DateTimeField("date_update", array(
                "title"     => "Дата обновления",
                "disabled"  => true
            )),
            new UserGroupField("group", array(
                "title" => "Группа пользователей",
                "values"=> function($obField){
                    $arGroups = CUserGroup::builder()->select(["id", "title"])->fetchAll();
                    return CArrayHelper::getKeyValue($arGroups, "id", "title");
                }
            ))
        );
    }
    
    public function onBeforeAdd($obResult){
        $obResult->setDataValues([
            "date_add"      => new Expr("NOW()"),
            "date_update"   => new Expr("NOW()")
        ]);
        
        return true;
    }
    
    public function onBeforeUpdate($obResult, $id){
        $obResult->setDataValues([
            "date_update" => new Expr("NOW()")
        ]);

        return true;
    }
    
    public function auth($login, $password, $rememberMe = false){
        $arUser = static::builder()->where("login", $login)
                                   ->where("active", 1)
                                   ->limit(1)
                                   ->fetch();
        
        if($arUser && $arUser["password"] == self::getHash($password)){
            return $this->_auth($arUser, $rememberMe);
        }
        
        return false;
    }
    
    public function authByID($userID, $rememberMe = false){
        $arUser = static::builder()->where(static::getPk(), $userID)
                                   ->where("active", 1)
                                   ->fetch();
        
        return $this->_auth($arUser, $rememberMe);
    }
    
    public function authByHash($hash){
        $obBuilder = new DbBuilder;
        
        $arPermanentHash = $obBuilder->select("user_id", "last_auth")
                                     ->from("user_permanent_auth")
                                     ->where("hash", $hash)
                                     ->limit(1)
                                     ->fetch();
        
        if($arPermanentHash){ //TO DO check date expire
            $arUser = static::builder()->where(static::getPk(), $arPermanentHash["user_id"])
                                       ->where("active", 1)
                                       ->fetch();
        }

        if($arUser){ //check on expire
            return $this->_auth($arUser, true);
        }else{
            return $this->destroyPermanentSession();
        }
    }
    
    protected function destroyPermanentSession(){
        $hash = CCookie::get("uidHash");
        
        if($hash){
            $obBuilder = new DbBuilder;
            $obBuilder->from("user_permanent_auth")
                      ->where("hash", $hash)
                      ->delete();
            
            CCookie::set("uidHash", "", time() - 1);
        }
        
        $this->_auth(); //re auth
    }
    
    static public function getPermanentHash(){
        return md5(uniqid("t_", true));
    }
    
    protected function _auth(array $arUser = array(), $rememberMe = false){
        $this->arData   = $arUser;
        $obGroupBuilder = CUserGroup::builder();
        
        $userID = isset($arUser[static::getPk()]) ? $arUser[static::getPk()] : null ;

        if($userID && count($arUser["groups"])){
            $arGroups = $obGroupBuilder->whereIn(CUserGroup::getPk(), $arUser["groups"])
                                       ->fetchAll();

            $this->arData["groups"] = CArrayHelper::index($arGroups, "alias");
            
            if($rememberMe){
                $obBuilder  = new DbBuilder;
                
                $arPermanentAuth = $obBuilder->select("last_auth", "hash")
                                             ->from("user_permanent_auth")
                                             ->where("user_id", $userID)
                                             ->limit(1)
                                             ->fetch();
                
                $obBuilder  = new DbBuilder;
                $obBuilder->from("user_permanent_auth");
                                            
                if($arPermanentAuth){
                    $permanentHash = $arPermanentAuth["hash"];
                    
                    $obBuilder->where("user_id", $userID)
                              ->update(array(
                                "last_auth" => new Expr("NOW()")
                              ));
                }else{
                    $permanentHash = static::getPermanentHash();
                    
                    $obBuilder->insert(array(
                        "user_id"   => $userID,
                        "hash"      => $permanentHash,
                        "last_auth" => new Expr("NOW()")
                    ));
                }
                
                $expire = time() + 60 * 60 * 24 * 365; //1 year
                          
                CCookie::set("uidHash", $permanentHash, $expire);
            }
        }else{
            $arGroup = $obGroupBuilder->where("alias", CUserGroup::ALIAS_UNAUTHORISED)
                                      ->limit(1)
                                      ->fetch();

            if($arGroup){
                $this->arData["groups"] = array($arGroup["alias"] => $arGroup);
            }
        }
        
        CAtom::$app->session->set("user_session", $this->arData);

        return true;
    }
    
    public function getData(){
        return $this->arData;
    }

    public function identify(){
        $data = CAtom::$app->session->get("user_session");

        if(!$data){ //if we are have not any data for user
            $hash = CCookie::get("uidHash");
            
            if(!$hash){ //if cookie hash is empty
                $this->_auth(); //auth as unauthorised
            }else{
                $this->authByHash($hash); //else auth by hash
            }
        }else{
            $this->arData = $data;
        }

        return $this;
    }
    
    static public function getHash($password){
        return sha1($password);
    }
    
    public function logout(){
        $this->destroyPermanentSession();
        
        return true;
    }
    
    public function hasGroup($groupAlias){
        if(!is_array($groupAlias)){
            $groupAlias = array($groupAlias);
        }
        
        foreach($groupAlias AS $alias){
            if(isset($this->arData["groups"][$alias])){
                return true;
            }
        }
        
        return false;
    }
    
    public function isAdmin(){
        return $this->is(CUserGroup::ALIAS_ADMIN);
    }
    
    public function is($groupAlias){
        return $this->hasGroup($groupAlias);
    }
    
    public function isAuth(){
        return $this->getID() ? true : false ;
    }
    
    public function getID(){
        $pk = static::getPk();
        return isset($this->arData[$pk]) ? $this->arData[$pk] : false ;
    }
    
    public function can($accessRule){
        if($this->isAdmin()){
            return true;
        }
        
        if(!isset($this->arData["access"]) && is_array($this->arData["groups"])){
            $arUserGroupIDs = CArrayHelper::getColumn($this->arData["groups"], CUserGroup::getPk());
            
            $arAccess = array();
            
            if(count($arUserGroupIDs)){
                $arGroupAccess = CUserGroupAccess::getGroupAccess($arUserGroupIDs);
                
                foreach($arGroupAccess AS $userGroupID => $arGroupAccessItem){
                    foreach($arGroupAccessItem AS $accessAlias => $arAccessItem){
                        $arAccess[$accessAlias] = $arAccessItem;
                    }
                }
            }
            
            $this->arData["access"] = $arAccess;
        }
        
        return isset($this->arData["access"][$accessRule]);
    }
    
    static public function setGroups($userID, array $arGroupIDs = array()){
        $obBuilder  = new DbBuilder;
        
        if(!is_array($userID)){
            $arUserIDs = array($userID);
        }else{
            $arUserIDs = $userID;
        }
        
        $obBuilder->from(self::GROUP_VALUE_TABLE)
                  ->whereIn("user_id", $arUserIDs);
        
        if(count($arGroupIDs)){
            $obBuilder->whereNotIn("user_group_id", $arGroupIDs);
        }
        
        $obBuilder->delete();

        if(count($arGroupIDs)){
            $obBuilder = new DbBuilder;
            
            $arTmpUserGroupValues = $obBuilder->from(self::GROUP_VALUE_TABLE)
                                              ->whereIn("user_id", $arUserIDs)
                                              ->fetchAll();
            
            $arUserGroupValues = array();
            
            foreach($arTmpUserGroupValues AS $arUserGroupValue){
                $hash                       = $arUserGroupValue["user_id"] . ":" . $arUserGroupValue["user_group_id"];
                $arUserGroupValues[$hash]   = $arUserGroupValue;
            }
            
            $obBuilder = new DbBuilder;
            $obBuilder->from(self::GROUP_VALUE_TABLE);
            
            foreach($arGroupIDs AS $userGroupID){
                foreach($arUserIDs AS $userID){
                    $hash = $userID . ":" . $userGroupID;
                    
                    if(!isset($arUserGroupValues[$hash])){
                        $obBuilder->insert(array(
                            "user_id"       => $userID,
                            "user_group_id" => $userGroupID
                        ));
                    }
                }
            }
        }
    }
}
?>