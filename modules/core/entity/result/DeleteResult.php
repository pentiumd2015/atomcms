<?
namespace Entity\Result;

class DeleteResult extends BaseResult{
    protected $numAffectedRows;
    protected $arIds = [];
    
	public function setNumAffectedRows($numAffectedRows){
		$this->numAffectedRows = $numAffectedRows;
        
        return $this;
	}

	public function getNumAffectedRows(){
		return $this->numAffectedRows;
	}

    public function setID(array $arIds = []){
        $this->arIds = $arIds;
        
        return $this;
    }
    
    public function getID(){
        return $this->arIds;
    }
}
