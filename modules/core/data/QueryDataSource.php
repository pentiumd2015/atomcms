<?
namespace Data;

use Entity\Query;
use CException;
use Helpers\CPagination;

class QueryDataSource extends BaseDataSource{
    protected $data         = null;
    protected $params       = [];
    protected $pagination   = null;
    protected $query        = null;
    
    public function __construct(array $params = []){
        $this->params = $params;
        
        if(isset($this->params["pagination"])){
            $pagination = $this->params["pagination"];
            
            if(is_array($pagination)){
                $pagination = new CPagination($pagination["page"], $pagination["perPage"]);
            }else if(!$pagination instanceof CPagination){
                throw new CException("Pagination object must extends \Helpers\CPagination");
            }
            
            $this->pagination = $pagination;
        }
        
        if(!isset($this->params["query"])){
            throw new CException("Query object is null");
        }
        
        if(!$this->params["query"] instanceof Query){
            throw new CException("Query object must extends \Entity\Query");
        }
        
        $this->query = $this->params["query"];
    }
    
    public function getData(){ 
        if($this->data === null){
            if($this->pagination){
                $this->query->pagination($this->pagination);
            }
            
            $this->data = $this->query->fetchAll();
        }
        
        return $this->data;
    }
    
    public function getPagination(){
        return $this->pagination;
    }
    
    public function getQuery(){
        return $this->query;
    }
}