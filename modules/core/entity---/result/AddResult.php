<?
namespace Entity\Result;

class AddResult extends Result{
	protected $id;
    protected $arData;

	public function setID($id){
		$this->id = $id;
        
        return $this;
	}
    
	public function getID(){
		return $this->id;
	}
    
    public function setData($arData){
        $this->arData = $arData;
        
        return $this;
    }
    
    public function getData(){
        return $this->arData;
    }
}
