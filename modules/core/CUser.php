<?
use DB\Query AS DbQuery;

use Entity\Field\Scalar\IntegerField;
use Entity\Field\Scalar\StringField;
use Entity\Field\Scalar\BooleanField;
use Entity\Field\Scalar\DateTimeField;
use Entity\Field\Scalar\PasswordField;
use Entity\Field\Scalar\ListField;

use DB\Manager\Validate\Unique as ValidateUnique;
use DB\Manager\Validate\Length as ValidateLength;
use DB\Manager\Validate\Email as ValidateEmail;

use Entity\Field\Custom\UserGroupField;

use DB\Expr;
use Helpers\CArrayHelper;

class CUser extends Entity\Manager{
    protected static $tableName = "user";
    
    protected static $info = [
        "title" => "Пользователи"
    ];
    
    protected static $events = [
        "ADD"       => "USER.ADD",
        "UPDATE"    => "USER.UPDATE",
        "DELETE"    => "USER.DELETE",
    ];
    
    protected $data = [];
    
    const GROUP_VALUE_TABLE = "user_group_value";

    public static function groupValueQuery(){
        return (new DbQuery)->from(self::GROUP_VALUE_TABLE);
    }

    public function getFields(){
        return [
            new IntegerField("id", [
                "title"         => "ID",
                "primary"       => true,
                "disabled"      => true,
                "description"   => "Идентификатор пользователя"
            ]),
            new StringField("login", [
                "title"         => "Логин",
                "required"      => true,
                "description"   => "Логин пользователя используется при авторизации на сайте",
                "validate"      => function(){
                    return [
                        new ValidateUnique(),
                        new ValidateLength(6, 40)
                    ];
                }
            ]),
            new PasswordField("password", [
                "title"     => "Пароль",
                "required"  => true,
                "algorithm" => [$this, "getHash"],
                "validate"  => function(){
                    return [
                        new ValidateLength(6, 40)
                    ];
                }
            ]),
            new StringField("fio", [
                "title" => "ФИО"
            ]),
            new StringField("email", [
                "title"     => "E-mail",
                "validate"  => function(){
                    return [
                        new ValidateEmail()
                    ];
                }
            ]),
            new ListField("gender", [
                "title"     => "Пол",
                "values"    => [
                    "0" => "Не выбран",
                    "M" => "Мужской",
                    "W" => "Женский"
                ]
            ]),
            new BooleanField("active", [
                "title" => "Активность"
            ]),
            new DateTimeField("date_add", [
                "title"     => "Дата добавления",
                "disabled"  => true
            ]),
            new DateTimeField("date_update", [
                "title"     => "Дата обновления",
                "disabled"  => true
            ]),
            new UserGroupField("group", [
                "title" => "Группа пользователей",
                "required" => true,
                "values"=> function($field){
                    $groups = CUserGroup::query()->select(["id", "title"])->fetchAll();
                    return CArrayHelper::map($groups, "id", "title");
                }
            ])
        ];
    }
    
    public function onBeforeAdd($result){
        $result->setDataValues([
            "date_add"      => new Expr("NOW()"),
            "date_update"   => new Expr("NOW()")
        ]);
        
        return true;
    }
    
    public function onBeforeUpdate($result){
        $result->setDataValues([
            "date_update" => new Expr("NOW()")
        ]);

        return true;
    }
    
    public function auth($login, $password, $rememberMe = false){
        $user = static::query()->where("login", $login)
                               ->where("active", 1)
                               ->limit(1)
                               ->fetch();
        
        if($user && $user["password"] == self::getHash($password)){
            return $this->_auth($user, $rememberMe);
        }
        
        return false;
    }
    
    public function authById($userID, $rememberMe = false){
        $user = static::query()->where(static::getPk(), $userID)
                               ->where("active", 1)
                               ->fetch();
        
        return $this->_auth($user, $rememberMe);
    }
    
    public function authByHash($hash){
        $permanentHash = (new DbQuery)->select("user_id", "last_auth")
                                     ->from("user_permanent_auth")
                                     ->where("hash", $hash)
                                     ->limit(1)
                                     ->fetch();
        
        if($permanentHash){ //TO DO check date expire
            $user = static::query()->where(static::getPk(), $permanentHash["user_id"])
                                       ->where("active", 1)
                                       ->fetch();
        }

        if($user){ //check on expire
            return $this->_auth($user, true);
        }else{
            return $this->destroyPermanentSession();
        }
    }
    
    protected function destroyPermanentSession(){
        $hash = CCookie::get("uidHash");
        
        if($hash){
            (new DbQuery)->from("user_permanent_auth")
                         ->where("hash", $hash)
                         ->delete();
            
            CCookie::set("uidHash", "", time() - 1);
        }
        
        $this->_auth(); //re auth
    }
    
    static public function getPermanentHash(){
        return md5(uniqid("t_", true));
    }
    
    protected function _auth(array $user = [], $rememberMe = false){
        $this->data = $user;
        $userID     = isset($user[static::getPk()]) ? $user[static::getPk()] : null ;

        if($userID && count($user["groups"])){
            $groups = CUserGroup::query()->whereIn(CUserGroup::getPk(), $user["groups"])
                                           ->fetchAll();

            $this->data["groups"] = CArrayHelper::index($groups, "code");
            
            if($rememberMe){
                $permanentAuth = (new DbQuery)->select("last_auth", "hash")
                                             ->from("user_permanent_auth")
                                             ->where("user_id", $userID)
                                             ->limit(1)
                                             ->fetch();
                
                $query = new DbQuery;
                $query->from("user_permanent_auth");
                                            
                if($permanentAuth){
                    $permanentHash = $permanentAuth["hash"];
                    
                    $query->where("user_id", $userID)
                          ->update([
                            "last_auth" => new Expr("NOW()")
                          ]);
                }else{
                    $permanentHash = static::getPermanentHash();
                    
                    $query->insert([
                        "user_id"   => $userID,
                        "hash"      => $permanentHash,
                        "last_auth" => new Expr("NOW()")
                    ]);
                }
                
                $expire = time() + 60 * 60 * 24 * 365; //1 year
                          
                CCookie::set("uidHash", $permanentHash, $expire);
            }
        }else{
            $group = CUserGroup::query()->where("code", CUserGroup::CODE_UNAUTHORISED)
                                        ->limit(1)
                                        ->fetch();

            if($group){
                $this->data["groups"] = [$group["code"] => $group];
            }
        }
        
        CAtom::$app->session->set("user_session", $this->data);

        return true;
    }
    
    public function getData(){
        return $this->data;
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
            $this->data = $data;
        }

        return $this;
    }
    
    public static function getHash($password){
        return sha1($password);
    }
    
    public function logout(){
        $this->destroyPermanentSession();
        
        return true;
    }
    
    public function hasGroup($groupCode){
        if(!is_array($groupCode)){
            $groupCode = [$groupCode];
        }
        
        foreach($groupCode AS $code){
            if(isset($this->data["groups"][$code])){
                return true;
            }
        }
        
        return false;
    }
    
    public function isAdmin(){
        return $this->is(CUserGroup::CODE_ADMIN);
    }
    
    public function is($groupCode){
        return $this->hasGroup($groupCode);
    }
    
    public function isAuth(){
        return $this->getId() ? true : false ;
    }
    
    public function getId(){
        $pk = static::getPk();
        return isset($this->data[$pk]) ? $this->data[$pk] : false ;
    }
    
    public function can($accessCode){
        if($this->isAdmin()){
            return true;
        }
        
        if(!isset($this->data["access"])){
            $userGroupIds = CArrayHelper::getColumn($this->data["groups"], "id");

            $access = [];

            if(count($userGroupIds)){
                $userGroupAccesses = CModuleAccess::valueQuery()->select("av.user_group_id", "a.*")
                                                                ->alias("av")
                                                                ->join(CModuleAccess::getTableName() . " AS a", "a.id", "av.module_access_id")
                                                                ->whereIn("user_group_id", $userGroupIds)->fetchAll();

                foreach($userGroupAccesses AS $userGroupAccess){
                    $access[$userGroupAccess["code"]] = $userGroupAccess;
                }
            }

            $this->data["access"] = $access;
        }

        return isset($this->data["access"][$accessCode]);
    }
    
    static public function setGroups($userID, array $groupIDs = []){ 
        $userIDs = !is_array($userID) ? [$userID] : $userID ;

        $query = static::groupValueQuery()->whereIn("user_id", $userIDs);
        
        if(count($groupIDs)){
            $query->whereNotIn("user_group_id", $groupIDs);
        }
       
        $query->delete();

        if(count($groupIDs)){
            $tmpUserGroupValues = static::groupValueQuery()->whereIn("user_id", $userIDs)->fetchAll();
            
            $userGroupValues = [];
            
            foreach($tmpUserGroupValues AS $userGroupValue){
                $hash                   = $userGroupValue["user_id"] . ":" . $userGroupValue["user_group_id"];
                $userGroupValues[$hash] = $userGroupValue;
            }
            
            $query = new DbQuery;
            $query->from(self::GROUP_VALUE_TABLE);
            
            foreach($groupIDs AS $userGroupID){
                foreach($userIDs AS $userID){
                    $hash = $userID . ":" . $userGroupID;
                    
                    if(!isset($userGroupValues[$hash])){
                        $query->insert(array(
                            "user_id"       => $userID,
                            "user_group_id" => $userGroupID
                        ));
                    }
                }
            }
        }
    }
}