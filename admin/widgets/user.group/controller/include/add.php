<?
use \Entity\Display;

$userID = $this->app("user")->getID();

$formID = CUserGroup::getTableName();

if($_REQUEST[$formID]){
    $obResult = CUserGroup::add($_REQUEST[$formID]);

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

$arUserGroup = isset($_REQUEST[$formID]) ? $_REQUEST[$formID] : array();

CBreadcrumbs::add(array(
    $listURL    => "Список групп",
    $addURL     => "Новая группа"
));

$obUserGroup = new CUserGroup;

$this->setData(array(
    "arDisplayDetailFields" => Display::getDisplayDetailFields($obUserGroup, $userID),
    "formID"                => $formID,
    "arErrors"              => $arErrors,
    "listURL"               => $listURL,
    "addURL"                => $addURL,
    "arUserGroup"           => $arUserGroup
));

$this->includeView("add");
?>