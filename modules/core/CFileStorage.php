<?
use \DB\Manager;
use \DB\Expr;

class CFileStorage extends Manager{
    static protected $_table = "file_storage";
    
    public function generateFilePath($name){
        $sha = sha1($name);
        
        return substr($sha, 0, 3) . "/" . substr($sha, 4, 3);
    }
    
    static public function getUploadDir(){
        $uploadDir = CParam::get("settings.upload.dir");

        if(!$uploadDir){
            $uploadDir = "/upload";
        }
        
        return $uploadDir;
    }
    
    static public function getFilePath(array $arData){
        if(!isset($arData["path"]) || !isset($arData["name"])){
            return false;
        }
        
        return static::getUploadDir() . "/" . $arData["path"] . "/" . $arData["name"] ;
    }
    
    static public function add(array $arFile){
        $obInfo = new SplFileInfo($arFile["tmp_name"]);

        if(!$obInfo->isFile()){
            return false;
        }
        
        $ext = $obInfo->getExtension();
        
        $arData = array();
        $arData["size"]             = $obInfo->getSize();
        $arData["original_name"]    = $obInfo->getBasename();
        $arData["name"]             = sha1($arData["original_name"] . microtime(true)) . ($ext ? "." . $ext : "");
        
        $uploadDir = ROOT_PATH . static::getUploadDir();
        
        while(true){
			$path = self::generateFilePath($arData["original_name"]);
			
            if(!is_file($uploadDir . "/" . $path . "/" . $arData["name"])){
				break;
			}
		}
        
        $arData["path"] = $path;
        
        CFile::mkdir($uploadDir . "/" . $path, 0755, true);
        
        if(CFile::copy($arFile["tmp_name"], $uploadDir . "/" . $path . "/" . $arData["name"])){
            CFile::deleteFile($arFile["tmp_name"]);
        }
        
        $arData["date_add"] = new Expr("NOW()");
                
        if(($fileID = parent::add($arData))){
            return $fileID;
        }else{
            CFile::deleteFile($uploadDir . "/" . $path . "/" . $arData["name"]);
            
            return false;
        }
    }
}
?>