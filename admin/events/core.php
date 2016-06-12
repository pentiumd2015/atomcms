<?
function p(){
    foreach(func_get_args() AS $arg){
        echo "<pre>" . print_r($arg, true) . "</pre>";
    }
}

CEvent::on("CORE.DB.QUERY.ERROR", function($connection, $error){
    CBuffer::clear();
    p($error);
    exit;
});

CEvent::on("404", function(){
    echo 404;
    exit;
});

CEvent::on("CORE.START", function(){
    $authUrl = BASE_URL . "auth/";
    
    list($requestUri) = explode("?", $_SERVER["REQUEST_URI"], 2);

   /* if(!$this->user->can(CUserGroupAccess::ADMIN_ACCESS) && $authUrl != $requestUri){ // if user has not access
       // CHttpResponse::redirect($authUrl . "?redirectUrl=" . urlencode($_SERVER["REQUEST_URI"]));
    }*//**/
});

CEvent::on('CORE.DB.CONNECT.AFTER', function($connection){
    $timezone = "Asia/Yekaterinburg";
    
    date_default_timezone_set($timezone);
    
    $connection->query("SET lc_time_names=?", ["ru_RU"]);
    $connection->query("SET time_zone=?", [$timezone]);
});

CEvent::on(array(
    "CORE.TEMPLATE.NOT_FOUND" => function($obApp, $obRouter, $obRoute){
       // echo 'Страница шаблона [' . $obTemplate->params->info->path . '] не найдена';
        Tools::p($obRoute);
        exit;
    },
    'CORE.MODULE.CONTROLLER.NOT_FOUND' => function($obModule){
        CHttpResponse::setCode(404);
        echo 'Модуль ' . $obModule->name . ' не найден';
        CTools::p($obModule);
    },
    'CORE.DB.CONNECT.ERROR' => function(){
        echo 'Error connect to DB: ';
        print_r(func_get_args());
    },
    'CORE.ROUTE.NOT_FOUND' => function($router){
        echo 'ROUTE.NOT_FOUND';
    //    CUtils::p($router);
    },
    'CORE.ROUTE.SEF.NOT_FOUND' => function($obApp, $obRouter){
        CHttpResponse::setCode(404);
       // CTools::p($obApp->route);
        echo "Страница [" . $obApp->route->url . "] не найдена";
        $obApp->end();
        
        CHttpResponse::redirect("/customer/");
     //   CUtils::p($router);
    },
    "CORE.DB.QUERY.ERROR" => function($obConnection, $arErrors){
        echo "<pre>" . print_r($arErrors, true) , "</pre>";
    },
    'CORE.SHUTDOWN'         => function($errorType){
        $arErrors = error_get_last();
           
        if($arErrors && ($arErrors["type"] & $errorType)){
            CBuffer::clear();
            
            $arConfig = $obApp->getConfig();
            
            if($arConfig["errors"]["displayErrors"]){
                echo '<pre>';
                echo 'Error: ' . CException::$arErrorTypes[$arErrors['type']] . '<br/>';
                print_r($arErrors);
                
                $arTraces = debug_backtrace();
                
                foreach($arTraces AS &$arTrace){
                    if(is_object($arTrace["object"])){
                        $arTrace["object"] = get_class($arTrace["object"]);
                    }
                    
                    unset($arTrace["args"]);
                }
                print_r($arTraces);
                echo '</pre>';
            }
        }
    },
    'CORE.EXCEPTION' => function($exception){
        CBuffer::clear();
        
        echo 'Exception</br>';
        echo $exception->getMessage(), "</br>";
        echo $exception->getCode(), "</br>";
        echo $exception->getFile(), "</br>";
        echo $exception->getLine(), "</br>";
        
        
        
       p($exception->getTrace());
    }
)); 
?>