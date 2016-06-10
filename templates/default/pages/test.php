<?
ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_NOTICE);
set_time_limit(0);
/*$obConnection = $this->app("db");

$arColumns = array(
    "phrase",
    "frequency"
);

$filePath = ROOT_PATH . "/создание сайтов кей.xlsx";

$obZip = new ZipArchive();
$obZip->open($filePath);

$obSheet        = simplexml_load_string($obZip->getFromName('xl/worksheets/sheet1.xml'));
$obSharedData   = simplexml_load_string($obZip->getFromName('xl/sharedStrings.xml'));

$obZip->close();

$arSharedData = array();

foreach($obSharedData->si AS $s){
    $arSharedData[] = str_replace("+", "", trim((string)$s->t));
}

unset($obSharedData);

$arData = array();

foreach($obSheet->sheetData->row AS $row){
    $arRow = array();
    
    $i = 0;
    
    foreach($row->c AS $c){
        $value = (string)$c->v;
        
        if((string)$c->attributes()->t == "s"){
            $value = htmlspecialchars($arSharedData[$value], ENT_COMPAT, "UTF-8", false);
        }
        
        $arRow[$arColumns[$i]] = $value;
        
        $i++;
    }
    
    $arRow["phrase"] = strtoupper($arRow["phrase"]);
    
    $arData[] = $arRow;
}

unset($obSheet, $arSharedData);

$arWords = array();

$arPhrases = array();
$m = memory_get_usage(true);
foreach($arData AS $key => $arItem){
    foreach(explode(" ", $arItem["phrase"]) AS $word){
        $arWords[$word] = 1;
    }
    
    $arPhrases[] = $arItem["phrase"];
    
    
}

unset($arData);

$arWords = array_keys($arWords);


$s = microtime(true);



$sql = '"' . implode('", "', $arWords) . '"';

$arFound = $obConnection->query("SELECT SQL_NO_CACHE word, base, pos_id
                                  FROM dict_g 
                                  WHERE word IN(" . $sql . ")")->fetchAll();


$arExcuse = $obConnection->query("SELECT id 
                                  FROM dict_poses 
                                  WHERE name IN('ПРЕДЛ', 'СОЮЗ', 'МЕЖД')")->fetchAll();

$arExcuse = \Helpers\CArrayHelper::index($arExcuse, "id");
$arFound  = \Helpers\CArrayHelper::index($arFound, "word");

$arResult = array();
        
foreach($arPhrases AS $key => $phrase){
    $arResult[$phrase]["lemmas"] = array();
    $arResult[$phrase]["excuse"] = array();
    
    foreach(explode(" ", $phrase) AS $index => $word){
        if($arFound[$word]){
            if($arExcuse[$arFound[$word]->pos_id]){
                $arResult[$phrase]["excuse"][]          = $word;
                $arResult[$phrase]["lemmas"][$index]    = $word;
            }else{
                $arResult[$phrase]["lemmas"][$index] = $arFound[$word]->base;
            }
        }else{
            $arResult[$phrase]["lemmas"][$index] = $word;
        }
    }

    $arResult[$phrase]["lemmas"] = implode(" ", $arResult[$phrase]["lemmas"]);
    
    if($arResult[$phrase]["excuse"]){
        $arResult[$phrase]["excuse"] = implode(" ", $arResult[$phrase]["excuse"]);
    }
    
    unset($arPhrases[$key]);
}

//0.4051787853241    mysql db


//1.2522759437561  dict file

//$obKeyPhrase  = new \Yandex\KeyPhrase;
//$arPhraseData       = $obKeyPhrase->getPhraseLemmas($arPhrases);

//Tools::p($arResult);
echo memory_get_usage(true) - $m;
echo "<br>";

echo microtime(true) - $s; 
*/



$obProxy = new \Proxy\Proxy;

$arProxyList = $obProxy->getList();

$proxy = $arProxyList[array_rand($arProxyList)];
echo $proxy;



class Wordstat{
    public function __construct(){
        $obProxy = new \Proxy\Proxy;
        
        $arProxyList = $obProxy->getList();
        
        $this->proxy = $arProxyList[array_rand($arProxyList)];
    }
    
    static public function isJSON($str){
        return ($str[0] == "{" && substr($str, -1) == "}");
    }
    
    protected function curl($arOptions){
        $ch = curl_init();
        
        $arOpts = array(
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FOLLOWLOCATION  => false,
            CURLOPT_HEADER          => true,
            CURLOPT_CONNECTTIMEOUT  => 3,
            CURLOPT_TIMEOUT         => 3,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_SSL_VERIFYHOST  => false,
            CURLOPT_USERAGENT       => $_SERVER['HTTP_USER_AGENT'],
            CURLOPT_VERBOSE         => true,
            /*CURLOPT_HTTPHEADER      => array(
                "X-Requested-With: XMLHttpRequest"
            ),*/
            
        );
        
        curl_setopt_array($ch, $arOptions + $arOpts);
        
        $response   = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        
        $separator  = "\r\n\r\n";
        $count      = substr_count($response, $separator);
        
        $arResult = explode($separator, $response);
        
        if($count == 2){ //use proxy response
            $headers    = $arResult[1];
            $response   = $arResult[2];
        }else{
            $headers    = $arResult[0];
            $response   = $arResult[1];
        }
        
        curl_close($ch);
        
        return array(
            "status"    => $statusCode,
            "response"  => $response,
            "proxy"     => $proxy,
            "headers"   => explode("\r\n", $headers)
        );
    }
    
    protected function getQueryString($arData, $sep = "=", $sep2 = "&"){
        $arResult = array();
        
        foreach($arData AS $key => $value){
            $arResult[] = $key . $sep . $value;
        }
        
        return implode($sep2, $arResult);
    }
    
    protected $data;
    
    protected function getCookieString($arData){
        $arResult = array();
        
        if(is_array($arData)){
            foreach($arData AS $key => $arParams){
                $arResult[$key] = $arParams["value"]; 
            }
        }
        
        return $this->getQueryString($arResult, "=", ";");
    }
    
    protected function getHeaderCookies($arHeaders){
        $arCookies = array();
        
        foreach($arHeaders AS $row){
            if(strpos($row, "Set-Cookie:") === 0){
                $cookieName = false;
                
                foreach(explode(";", trim(substr($row, 11))) AS $key => $cookie){
                    list($name, $value) = explode("=", trim($cookie), 2);
                    
                    if($key == 0){
                        $cookieName             = $name;
                        $arCookies[$cookieName] = array(
                            "value" => $value
                        );
                    }else{
                        $arCookies[$cookieName][strtolower($name)] = $value;
                    }
                }
            }
        }
        
        return $arCookies;
    }
    
    public function auth($login, $pass){
        $arResult = $this->curl(array(
            CURLOPT_URL             => "https://passport.yandex.ru/passport?mode=auth",
            CURLOPT_POSTFIELDS      => $this->getQueryString(array(
                "login"     => $login, 
                "passwd"    => $pass,
                "timestamp" => microtime(true)
            ))
        ));
        
        $arCookies = $this->getHeaderCookies($arResult["headers"]);
        
        $arReturn = array();
        
        if(isset($arCookies["Session_id"])){
            $arCookies["fuid01"] = $this->getFUID();
            
            $arReturn = array(
                "isAuth"    => 1,
                "cookies"   => $arCookies
            );
            
            //we are authorized
        }else{
            $arReturn = array(
                "isAuth" => 0
            );
            //not auth
        }
        
        return $arReturn + $arResult;
    }
    
    public function getWordStat($arParams){
        $arPostParams = array(
            "db"        => "", 
            "filter"    => "all",
            "map"       => "world",
            "page"      => $arParams["page"],
            "page_type" => "words",
            "period"    => "monthly",
            "regions"   => $arParams["region"],
            "sort"      => "cnt",
            "type"      => "list",
            "words"     => $arParams["phrase"]
        );
        
        if($arParams["captcha"] && is_array($arParams["captcha"])){
            $arPostParams["captcha_value"]  = $arParams["captcha"]["value"];
            $arPostParams["captcha_key"]    = $arParams["captcha"]["key"];
        }
        
        $arResult = $this->curl(array(
            CURLOPT_URL             => "https://wordstat.yandex.ru/stat/words",
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => $this->getQueryString($arPostParams),
            CURLOPT_COOKIE          => $this->getCookieString($arParams["cookies"]),
            CURLOPT_PROXY           => $this->proxy
        ));
/*
        if($arResult["status"] == 200 && self::isJSON($arResult["response"])){
            $arResult["response"] = json_decode($arResult["response"], true);
        }else if($statusCode == 500 || $statusCode == 302){
            $arResult["need_login"] = 1;
        }else{ //status code 100
            return $this->getWordStat($arParams);
        }
        */
        return $arResult;
    }
    
    public function getFUID(){
        $arResult = $this->curl(array(
            CURLOPT_URL     => "https://kiks.yandex.ru/su/"
        ));
        
        $arCookies = $this->getHeaderCookies($arResult["headers"]);
        
        return $arCookies["fuid01"];
    }
}



































/*




$ch = curl_init();

$arOpts = array(
    CURLOPT_URL             => "https://kiks.yandex.ru/su",
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_FOLLOWLOCATION  => false,
    CURLOPT_HEADER          => true,
    CURLOPT_CONNECTTIMEOUT  => 10,
    CURLOPT_TIMEOUT         => 10,
    //CURLOPT_SSL_VERIFYPEER  => false,
    //CURLOPT_SSL_VERIFYHOST  => false,
   // CURLOPT_USERAGENT       => $_SERVER['HTTP_USER_AGENT'],
   // CURLOPT_VERBOSE         => true,
    CURLOPT_HTTPHEADER      => array(
        "X-Requested-With: XMLHttpRequest",
      //  "Host: wordstat.yandex.ru"
    ),
 //   CURLOPT_PROXY           => "185.64.18.65:8080",
    CURLOPT_REFERER         => "https://wordstat.yandex.ru"
);

curl_setopt_array($ch, $arOpts);

$response   = curl_exec($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);




curl_close($ch);

echo $response;










$ch = curl_init();

$arOpts = array(
    CURLOPT_URL             => "https://passport.yandex.ru/passport?mode=auth",
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_FOLLOWLOCATION  => false,
    CURLOPT_HEADER          => true,
    CURLOPT_CONNECTTIMEOUT  => 10,
    CURLOPT_TIMEOUT         => 10,
    CURLOPT_REFERER         => "https://wordstat.yandex.ru",
    CURLOPT_POSTFIELDS      => "login=YwrNtWTa7440&passwd=KleTxVBA9984&timestamp" . microtime(true)
);

curl_setopt_array($ch, $arOpts);

$response   = curl_exec($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);




curl_close($ch);

*/


//echo $response;













$obW = new Wordstat();


$r = $obW->auth("wMhCxKaL4527", "hWApxtaU6442");
Tools::p($r);
sleep(3);
$rr = $obW->getWordStat(array(
    "db"        => "", 
    "filter"    => "all",
    "map"       => "world",
    "page"      => 1,
    "page_type" => "words",
    "period"    => "monthly",
    "regions"   => 225,
    "sort"      => "cnt",
    "type"      => "list",
    "words"     => "окна купить",
    "cookies"   => $r["cookies"]
));
/**/
Tools::p($rr);
/*


$obRollingCurl->setOptions(array( //global options
    CURLOPT_REFERER         => "http://key_dev.2atom.ru/reg.php",
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_CONNECTTIMEOUT  => 5,
    CURLOPT_TIMEOUT         => 5,
    CURLOPT_USERAGENT       => $_SERVER['HTTP_USER_AGENT'],
    CURLOPT_HEADER          => true,
    //CURLOPT_PROXY           => $proxy
));

$obRequest = new \RollingCurl\Request("http://key_dev.2atom.ru/reg.php");

$obRollingCurl->add($obRequest);

$obRollingCurl->setCallback(function($obRequest, $obRollingCurl){
    $response   =  $obRequest->getResponseText();
Tools::p($response);
  
    $obRollingCurl->clearCompleted();
    $obRollingCurl->prunePendingRequestQueue();
});
  */  

/*
$obRollingCurl = new \RollingCurl\RollingCurl();

//getPostD
$arQueue = array(
    array(
        "url"       => "https://passport.yandex.ru/passport?mode=auth",
        "options"   => array(
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FOLLOWLOCATION  => false,
            CURLOPT_MAXREDIRS       => 1,
            CURLOPT_CONNECTTIMEOUT  => 5,
            CURLOPT_TIMEOUT         => 5,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_SSL_VERIFYHOST  => false,
            CURLOPT_VERBOSE         => true,
            CURLOPT_HEADER          => true,
            CURLOPT_HTTPHEADER      => array(
                "X-Requested-With: XMLHttpRequest"
            ),
            CURLOPT_POST        => true,
            CURLOPT_USERAGENT   => $_SERVER['HTTP_USER_AGENT'],
            CURLOPT_POSTFIELDS      => "login=YwrNtWTa7440&passwd=KleTxVBA9984&timestamp=" . microtime(true),
            //CURLOPT_PROXY => $proxy
        )
    )
);

foreach($arQueue AS $arParams){
    $obRequest = new \RollingCurl\Request($arParams["url"]);
    $obRequest->setOptions($arParams["options"]);
    
    $obRollingCurl->add($obRequest);
}

$obRollingCurl->setCallback(function($obRequest, $obRollingCurl){
    \Tools::p($obRequest->getResponseText());
  //  $obRequest->getResponseInfo()
    $obRollingCurl->clearCompleted();
    $obRollingCurl->prunePendingRequestQueue();
});

$obRollingCurl->setSimultaneousLimit(10);
$obRollingCurl->execute();*/
?>