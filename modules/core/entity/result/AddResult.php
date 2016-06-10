<?
namespace Entity\Result;

class AddResult extends BaseResult{
	protected $id;

	public function setID($id){
		$this->id = $id;
        
        return $this;
	}
    
	public function getID(){
		return $this->id;
	}
    
    public function setDataValues(array $arValues = []){
        return $this->setData(array_merge($this->getData(), $arValues));
    }
}
