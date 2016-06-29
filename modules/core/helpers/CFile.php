<?
namespace Helpers;

use CAtom;

class CFile{
    public static function copy($src, $dst){
        return copy($src, $dst);
    }
    
    public static function normalizePath($path, $removeComma = false, $ds = DIRECTORY_SEPARATOR){
        $path = rtrim(str_replace("/\\", $ds, $path), $ds);

        if($removeComma){
            $result = [];
            
            foreach(explode($ds, $path) AS $part){
                if($part === ".."){
                    array_pop($parts);
                }else if($part === "." || $part === ""){
                    continue;
                }else{
                    $result[] = $part;
                }
            }
            
            $path = implode($ds, $result);
        }
        
        return $path === '' ? "." : $path;
    }

    public static function deleteDirectory($dir){
        if(!is_dir($dir)){
            return;
        }
        
        if(!is_link($dir)){
            if(!($handle = opendir($dir))){
                return;
            }
            
            while(($file = readdir($handle)) !== false){
                if($file === "." || $file === ".."){
                    continue;
                }
                
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                
                if(is_dir($path)){
                    static::deleteDirectory($path);
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
    
    public static function getExtension($fileName){
        return substr(strrchr($fileName, "."), 1);
    }
    
    public static function createDirectory($dirPath, $mask = 0755, $recursive = true){
        $dirPath = static::normalizePath($dirPath);
        
        if(!is_dir($dirPath)){
            mkdir($dirPath, $mask, $recursive);
        }else{
            return true;
        }
    }
    
    public static function download($filePath, $fileName){
        if(is_file($filePath)){
            ob_end_clean();
            set_time_limit(0);
            ignore_user_abort(false);
            
            CHttpResponse::setHeaders([
                "Content-Description"   => "File Transfer",
                "Content-Type"          => "application/octet-stream",
                "Content-Disposition"   => "attachment; filename='" . $fileName . "'",
                "Expires"               => 0,
                "Cache-Control"         => "must-revalidate",
                "Pragma"                => "public",
                "Content-Length"        => filesize($filePath)
            ]);
            
            readfile($filePath);

            CAtom::$app->end();
        }
    }
}