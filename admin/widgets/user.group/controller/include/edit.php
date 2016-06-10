<?
use \Entity\Display;

$userID = $this->app("user")->getID();

$id = (int)$obRoute->getVarValue("ID");

$arUserGroup = CUserGroup::getByID($id, true);

if(!$arUserGroup){
    CEvent::trigger("404");
}

$formID = CUserGroup::getTableName();

if($_REQUEST[$formID]){
    $obResult = CUserGroup::update($arUserGroup["id"], $_REQUEST[$formID]);

    if(CHttpRequest::isAjax()){
        $arResponse = array("result" => 1);
        
        if($obResult->isSuccess()){
            $id = $obResult->getID();
            $arResponse["hasErrors"]    = 0;
            $arResponse["id"]           = $id;
            $arResponse["redirectURL"]  = str_replace("{ID}", $id, $editURL);
        }else{
            $arResponse["hasErrors"]    = 1;
            $arResponse["errors"]       = array();
            
            foreach($obResult->getErrors() AS $obFieldError){
                $arResponse["errors"][$obFieldError->getFieldName()] = array(
                    "code"      => $obFieldError->getCode(),
                    "message"   => $obFieldError->getMessage()
                );
            }
        }

        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        exit;
    }
}

CBreadcrumbs::add(array(
    $listURL    => "Список групп",
    $addURL     => "Редактирование группы"
));

$obUserGroup = new CUserGroup;

$this->setData(array(
    "arDisplayDetailFields" => Display::getDisplayDetailFields($obUserGroup, $userID),
    "formID"                => $formID,
    "listURL"               => $listURL,
    "editURL"               => str_replace("{ID}", $arUser["id"], $editURL),
    "arUserGroup"           => $arUserGroup
));

$this->includeView("edit");
?>