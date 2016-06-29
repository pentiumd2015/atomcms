<?
namespace Helpers;

use DB\Connection;
use CAtom;

class CPagination{
    public $perPage     = 10;
    public $page        = 1;
    protected $numPage;
    public $count;
    
    public function __construct($page = null, $perPage = null){
        if($page !== null){
            $this->setPage($page);
        }
        
        if($perPage !== null){
            $this->setPerPage($perPage);
        }
    }
    
    public function setPage($page = 1){
        $this->page = abs((int)$page);
        
        return $this;
    }

    public function getNumPage(){
        return $this->numPage;
    }
    
    public function setPerPage($perPage = 10){
        $this->perPage = (int)$perPage;
        
        return $this;
    }
    
    public function correctPage(){
        $this->numPage = ceil($this->count / $this->perPage);

        if($this->page > $this->numPage){
            $this->page = $this->numPage;
        }else if($this->page < 1){
            $this->page = 1;
        }
    }
    
    public function initFromSql($sql, $statements = [], Connection $connection = null){
        if($connection === null){
            $connection = CAtom::$app->db;
        }
        
        if($this->perPage > 0){
            $countSQL       = preg_replace("/^(\(*SELECT\s+.*?\s+FROM)/si", "SELECT COUNT(*) FROM", $sql, 1);
            $this->count    = $connection->query($countSQL, $statements)->fetchColumn();
            $this->correctPage();
            
            $sql.= " LIMIT " . $this->perPage . " OFFSET " . (($this->page - 1) * $this->perPage);
        }

        return $connection->query($sql, $statements);
    }
    
    public function initFromArray(array $data = []){
        if(count($data) && $this->perPage > 0){
            $this->count = count($data);
            $this->correctPage();

            $data = array_chunk($data, $this->perPage, true);
            $data = $data[$this->page - 1];
        }
        
        return $data;
    }
    
    public function getData(){
        if($this->perPage > 0 && $this->count > $this->perPage){
            return [
                "numPage"  => $this->numPage,
                "count"    => $this->count,
                "perPage"  => $this->perPage,
                "page"     => $this->page
            ];
        }else{
            return null;
        }
    }
}