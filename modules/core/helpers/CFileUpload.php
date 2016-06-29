<?
namespace Helpers;

class FileUpload extends CFileUpload{
    const UPLOAD_DIR = "/upload";
    
    public function generateFilePath($name){
        $sha = sha1($name);
        
        return substr($sha, 0, 3) . "/" . substr($sha, 4, 3);
    }
    
    public function generateFileName($name){
        $fileName = sha1($name . microtime(true));
        
        $extension = CFile::getExtension($name);
        
        if($extension){
            $fileName.= "." . $extension;
        }
        
        return $fileName;
    }
    
    public function add($uploadDir = self::UPLOAD_DIR){
        $newPath = static::generateFilePath($this->name);
        
        $dirPath = ROOT_PATH . $uploadDir . "/" . $newPath;
        
        CFile::mkdir($dirPath, 0755, true);
        
        if(is_array($this->tmpName)){
            foreach($this->tmpName AS $key => $tmpName){
                $newName = $this->generateFileName($this->name[$key]);
                
                if($this->saveAs($dirPath . "/" . $newName)){
                    $fileID = $this->addToDB($newPath, $newName, $this->name[$key], $this->type, $this->size);
                    
                    if($fileID){
                        return $fileID;
                    }else{
                        CFile::removeFile($dirPath . "/" . $newName);
                    }
                }else{
                    return false;
                }
            }
        }else{
            $newName = $this->generateFileName($this->name);
            
            if($this->saveAs($dirPath . "/" . $newName)){
                $fileID = $this->addToDB($newPath, $newName, $this->name, $this->type, $this->size);
                
                if($fileID){
                    return $fileID;
                }else{
                    CFile::removeFile($dirPath . "/" . $newName);
                }
            }else{
                return false;
            }
        }
    }
    
    protected function addToDB($filePath, $fileName, $originalName, $type, $size){
        $obDate = new \DateTime();
        
        return CDBFile::add(array(
            "file_name"     => $fileName,
            "original_name" => $originalName,
            "file_path"     => $filePath,
            "type"          => $type,
            "size"          => $size,
            "date_add"      => $obDate->format("Y-m-d H:i:s")
        ));
    }
}