<?
function p(){
    foreach(func_get_args() AS $arg){
        echo "<pre>" . print_r($arg, true) . "</pre>";
    }
}

CEvent::on('CORE.DB.CONNECT.AFTER', function($obConnection){
    $timezone = "Asia/Yekaterinburg";
    
    date_default_timezone_set($timezone);
    
    $obConnection->query("SET lc_time_names=?", array("ru_RU"));
    $obConnection->query("SET time_zone=?", array($timezone));
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
    'CORE.SHUTDOWN'         => function($obApp, $errorType){
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
      //  CUtils::p($exception->getTrace());
    }
)); 
?>