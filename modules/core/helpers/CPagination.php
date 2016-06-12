<?
namespace Helpers;

class CPagination{
    public $perPage;
    public $page;
    public $numPage;
    public $count;
    
    public function __construct($page = NULL, $perPage = NULL){
        if($page){
            $this->setPage($page);
        }
        
        if($perPage){
            $this->setPerPage($perPage);
        }
    }
    
    public function setPage($page = 1){
        $this->page = abs((int)$page);
        
        return $this;
    }
    
    public function setPerPage($perPage = 10){
        $this->perPage = (int)$perPage;
        
        return $this;
    }
    
    public function correctPage(){
        if($this->page > $this->numPage){
            $this->page = $this->numPage;
        }else if($this->page < 1){
            $this->page = 1;
        }
    }
    
    public function initFromSQL($sql, $arStatements = array(), $DBconnection = NULL){
        if(!$DBconnection){
            $DBconnection = CDB::getInstance();
        }
        
        if($this->perPage > 0){
            $countSQL       = preg_replace('/^(\(*SELECT\s+.*?\s+FROM)/si', 'SELECT COUNT(*) C FROM', $sql, 1);
            $this->count    = $DBconnection->query($countSQL, $arStatements)->fetch()->C;
            $this->numPage  = ceil($this->count / $this->perPage);
            
            $this->correctPage();
            
            $sql.= ' LIMIT ' . $this->perPage . ' OFFSET ' . (($this->page - 1) * $this->perPage);
        }

        return $DBconnection->query($sql, $arStatements);
    }
    
    public function initFromArray(array $arData){
        if(count($arData) && $this->perPage > 0){
            $this->count    = count($arData);
            $this->numPage  = ceil($this->count / $this->perPage);
            
            $this->correctPage();
            
            $arData = array_chunk($arData, $this->perPage, true);
            $arData = $arData[$this->page - 1];
        }
        
        return $arData;
    }
    
    public function getData(){
        if($this->perPage > 0 && $this->count > $this->perPage){
            $obResult = new stdClass;
            $obResult->numPage  = $this->numPage; //кол-во страниц
            $obResult->count    = $this->count;   //кол-во записей
            $obResult->perPage  = $this->perPage; //кол-во записей на страницу
            $obResult->page     = $this->page;    //текущая страница
            
            return $obResult;
        }else{
            return false;
        }
    }
}
?>