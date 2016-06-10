<?
namespace Entity\Result;

class UpdateResult extends BaseResult{
	protected $numAffectedRows;
    protected $arIds = [];
    protected $arItem = [];
    protected $arChangedData = [];

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
    
    public function getData(){
        return array_merge($this->arItemData, $this->arChangedData);
    }
    
    public function setItemData($arItemData){
        $this->arItemData = $arItemData;
        
        return $this;
    }
    
    public function getItemData(){
        return $this->arItemData;
    }
    
    public function setChangedData($arChangedData){
        $this->arChangedData = $arChangedData;
        
        return $this;
    }
    
    public function getChangedData(){
        return $this->arChangedData;
    }
    
    public function setDataValues(array $arValues = []){
        return $this->setData(array_merge($this->getData(), $arValues));
    }
}
