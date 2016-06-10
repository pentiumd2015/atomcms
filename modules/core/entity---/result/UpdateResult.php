<?
namespace Entity\Result;

class UpdateResult extends Result{
	protected $numAffectedRows;
    protected $id;
    protected $arData;

	public function setNumAffectedRows($numAffectedRows){
		$this->numAffectedRows = $numAffectedRows;
	}

	public function getNumAffectedRows(){
		return $this->numAffectedRows;
	}

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
