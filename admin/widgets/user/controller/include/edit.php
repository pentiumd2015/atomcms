<?
use \Entity\Display;

$userID = $this->app("user")->getID();
$id     = (int)$this->app("route")->getVarValue("ID");
$obUser = new CUser;
$arFormData = $obUser->getByID($id);

if(!$arFormData){
    CEvent::trigger("404");
}

$formID = $obUser->getEntityName();

if($_REQUEST[$formID]){
    $obResult = $obUser->update($arFormData["id"], $_REQUEST[$formID]);

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
    $listURL    => "Список пользователей",
    $addURL     => "Редактирование пользователя"
));

$obDisplay = new Display($obUser);

$this->setData(array(
    "arDisplayFields"   => $obDisplay->getDisplayDetailFields($userID),
    "formID"            => $formID,
    "listURL"           => $listURL,
    "editURL"           => str_replace("{ID}", $arFormData["id"], $editURL),
    "arFormData"        => $arFormData
));

$this->includeView("edit");
?>