<?
class CView extends CConstruct{
    public $_arData = array();
    
    public function process($__file, $__extractDataKeys = true){
        $__obResult = new stdClass;
        $__obResult->content = NULL;
        $__obResult->result  = NULL;
        
        if(is_file($__file)){
            if($__extractDataKeys && is_array(($__arData = $this->getData()))){
                extract($__arData, EXTR_PREFIX_SAME, "data");
            }
            
            ob_start();
            ob_implicit_flush(false);
            $__obResult->result     = require($__file);
            $__obResult->content    = ob_get_clean();
        }
        
        return $__obResult;
    }
    
    /**
     * получает в переменную исполненный код файла
     */
    public function getContent($file){
        return $this->process($file)->content;
    }
    
    public function getResult($file){
        return $this->process($file)->result;
    }
    
    public function clearData(){
        $this->_arData = array();
        return $this;
    }
    
    /**
     * устанавливает данные для файла вида
     */
    public function setData($arData = array()){
        $this->_arData = $arData;
        return $this;
    }
    
    /**
     * добавляет данные для файла вида к уже имеющемуся массиву
     */
    public function addData($arData = array()){
        $this->_arData = array_merge_recursive($this->_arData, $arData);
        return $this;
    }
    
    /**
     * получает данные для файла вида
     */
    public function getData($data = NULL){
        if(!$data){
            return $this->_arData;
        }else{
            return isset($this->_arData[$data]) ? $this->_arData[$data] : false ;
        }
    }
}
?>