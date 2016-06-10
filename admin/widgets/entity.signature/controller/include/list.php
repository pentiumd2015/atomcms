<?
use \Entities\Entity;
use \Helpers\CHttpResponse;
use \Helpers\CJSON;

$obEntity = Entity::findByPk($entityID);

if(!$obEntity){
    CHttpResponse::redirect("/404/");
}

$obEntity->params = CJSON::decode($obEntity->params, true);

$arDefaultSignatures = Entity::getDefaultSignatureList();

$arErrors = array();

if($_REQUEST["entity_signature"]){
    $arData = $_REQUEST["entity_signature"];
    
    foreach($arDefaultSignatures AS $type => $arDefaultSignature){
        if(empty($arData[$type]["title"])){
            $arErrors[$type][] = "Введите название [" . $arDefaultSignature["title"] . "]";
        }
    }

    if(!count($arErrors)){
        $obEntity->params["signatures"] = $arData;
        
        Entity::updateByPk($entityID, array(
            "params" => CJSON::encode($obEntity->params)
        ));
        
        $arSaveResult["success"]    = true;
    }else{
        $arSaveResult["hasErrors"]  = true;
        $arSaveResult["success"]    = false;
        $arSaveResult["errors"]     = $arErrors;
    }
    
    if(\Helpers\CHttpRequest::isAjax()){
        $arResponse = array("result" => 1);
        
        if($arSaveResult["success"]){
            $arResponse["hasErrors"]    = 0;
            $arResponse["redirectURL"]  = str_replace("{ENTITY_ID}", $entityID, $listURL);
        }else{
            $arResponse["hasErrors"]    = 1;
            $arResponse["errors"]       = $arSaveResult["errors"];
        }
        
        CHttpResponse::setType(CHttpResponse::JSON);
        echo CJSON::encode($arResponse);
        exit;
    }else{
        if($arSaveResult["success"]){
            $redirectURL = str_replace("{ENTITY_ID}", $entityID, $listURL);
            
            CHttpResponse::redirect($redirectURL);
        }else{
            $arErrors = $arSaveResult["errors"];
        }
    }
}

\Page\Breadcrumbs::add(array(
    $entityURL  => "Редактирование сущности",
    $listURL    => "Редактирование подписей"
));

$this->setData(array(
    "obEntity"              => $obEntity,
    "arDefaultSignatures"   => $arDefaultSignatures,
    "entityURL"             => $entityURL,
    "arErrors"              => $arErrors,
    "listURL"               => $listURL,
));

$this->includeView("list");
?>