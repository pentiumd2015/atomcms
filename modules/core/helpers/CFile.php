<?
namespace Helpers;


class CFile{
    static public function getArrayFromFile($filePath){
        if(is_file($filePath) && ($array = include($filePath)) && is_array($array)){
            return $array;
        }
        
        return array();
    }
    
    static public function copy($src, $dst){
        return copy($src, $dst);
    }
    
    static public function normalizePath($path, $removeComma = false, $ds = DIRECTORY_SEPARATOR){
        $path = preg_replace("#[\/\\\]+#u", $ds, $path);
        
        if($removeComma){
            $arPath = explode($ds, $path);
            
            $arResult = array();
            
            foreach($arPath AS $part){
                switch($part){
                    case '.':
                        continue;
                    case '..':
                        array_pop($arResult);
                        continue;
                    default:
                        $arResult[] = $part;
                }     
            }
            
            $path = implode($ds, $arResult);
        }
        
        return $path;
    }
    
    static public function scanDirectory($dirPath){
        return new \DirectoryIterator($dirPath);
    }
    /*
    public static function deleteDir($dir, $removeCurrentDir = true){//убрать рекурсию и с помощью DirectoryIterator удалять директории и файлы
        if(is_dir($dir)){
            $obDirectoryIterator = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);

            $obIterator = new RecursiveIteratorIterator($obDirectoryIterator, RecursiveIteratorIterator::CHILD_FIRST);
            //$obIterator->setMaxDepth(30);
            
            foreach($obIterator AS $entity => $fileInfo){
                if($fileInfo->isDir()){
                    rmdir($entity);
                }else{
                    unlink($entity);
                }
            }
            
            if($removeCurrentDir){
               rmdir($dir);
            }
            
            return true;
        }else{
            return true;
        }
    }
    */
    public static function deleteDir($dir){
        if(!is_dir($dir)){
            return;
        }
        
        if(!is_link($dir)){
            if(!($handle = opendir($dir))){
                return;
            }
            
            while(($file = readdir($handle)) !== false){
                if($file === '.' || $file === '..'){
                    continue;
                }
                
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                
                if(is_dir($path)){
                    static::deleteDir($path);
                }else{
                    static::deleteFile($path);
                }
            }
            
            closedir($handle);
        }
        
        if(is_link($dir)){
            unlink($dir);
        }else{
            rmdir($dir);
        }
    }
    
    public static function deleteFile($file){
        if(is_file($file)){
            unlink($file);
            
            return true;
        }
        
        return false;
    }
    
    static public function getExtension($fileName){
        return substr(strrchr($fileName, "."), 1);
    }
    
    static public function mkdir($dirPath, $mask = 0755, $recursive = true){
        $dirPath = static::normalizePath($dirPath);
        
        if(!is_dir($dirPath)){
            mkdir($dirPath, $mask, $recursive);
        }
    }
    
    static public function download($filePath, $fileName){
        if(is_file($filePath)){
            ob_end_clean();
            set_time_limit(0);
            ignore_user_abort(false);
            
            CHttpResponse::setHeaders(array(
                "Content-Description"   => "File Transfer",
                "Content-Type"          => "application/octet-stream",
                "Content-Disposition"   => "attachment; filename='" . $fileName . "'",
                "Expires"               => 0,
                "Cache-Control"         => "must-revalidate",
                "Pragma"                => "public",
                "Content-Length"        => filesize($filePath)
            ));
            
            readfile($filePath);
            
            app()->end();
        }
    }
}
?>