<?
namespace Helpers;

class CHtml{
    static public $arVoidTags = array(
        'area'      => 1,
        'base'      => 1,
        'br'        => 1,
        'col'       => 1,
        'command'   => 1,
        'embed'     => 1,
        'hr'        => 1,
        'img'       => 1,
        'input'     => 1,
        'keygen'    => 1,
        'link'      => 1,
        'meta'      => 1,
        'param'     => 1,
        'source'    => 1,
        'track'     => 1,
        'wbr'       => 1,
    );
    
    static public function chars($str){
       return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8", false);
    }
    
    static public function getAttributeString($arAttributes = array()){
        $str = "";
        
        if(is_array($arAttributes)){
            foreach($arAttributes AS $name => $value){
                $str.= " " . $name . '="' . static::chars($value) . '"';
            }
        }
        
        return $str;
    }
    
    static public function tag($tagName, $value = "", $arAttributes = array()){
        return "<" . $tagName . static::getAttributeString($arAttributes) . ">" . (isset(static::$arVoidTags[strtolower($tagName)]) ? "" : $value . "</" . $tagName . ">") . "\n";
    }
        
    static public function multiselect($fieldName = "", $arData = array(), $arSelected = array(), $arAttributes = array()){        
        $arAttributes["multiple"] = "multiple";
        
        if($fieldName && substr($fieldName, -2) != "[]"){
            $fieldName.= "[]";
        }
        
        return static::select($fieldName, $arData, $arSelected, $arAttributes);
    }
    
    static public function select($fieldName = "", $arData = array(), $selectedValue = false, $arAttributes = array()){
        $arOptionsAttributes = isset($arAttributes["options"]) && is_array($arAttributes["options"]) ? $arAttributes["options"] : array() ;
        
        unset($arAttributes["options"]);
        
        $arAttributes["name"] = static::chars($fieldName);
        
        return static::tag("select", static::getOptionsList($arData, $selectedValue, $arOptionsAttributes), $arAttributes);
    }
    
    static public function radio($fieldName = "", $checked = false, $arAttributes = array()){       
        $arAttributes["type"] = "radio";
        $arAttributes["name"] = static::chars($fieldName);
        $arAttributes["value"]= isset($arAttributes["value"]) ? static::chars($arAttributes["value"]) : 1;
        
        if($checked){
            $arAttributes["checked"] = "checked";
        }
        
        return static::tag("input", false, $arAttributes);
    }
    
    static public function boolean($fieldName = "", $arValues = array(), $checked = false, $arAttributes = array()){
        $trueValue      = isset($arValues[0]) ? $arValues[0] : 1 ;
        $falseValue     = isset($arValues[1]) ? $arValues[1] : 0 ;
        $checked        = (bool)$checked;

        $return = static::hidden(static::chars($fieldName), ($checked ? $trueValue : $falseValue));
        
        $arAttributes["onchange"] = "$(this).prev().val(this.checked ? \"" . static::chars(CText::escape($trueValue)) . "\" : \"" . static::chars(CText::escape($falseValue)) . "\").trigger(\"change\");";
        
        $return.= static::checkbox("", $checked, $arAttributes);
        
        return $return;
    }
    
    static public function checkbox($fieldName = "", $checked = false, $arAttributes = array()){
        $arAttributes["type"] = "checkbox";
        $arAttributes["name"] = static::chars($fieldName);
        $arAttributes["value"]= isset($arAttributes["value"]) ? static::chars($arAttributes["value"]) : 1;
        
        if($checked){
            $arAttributes["checked"] = "checked";
        }
        
        return static::tag("input", false, $arAttributes);
    }
    
    static public function hidden($fieldName = "", $value = "", $arAttributes = array()){
        $arAttributes["type"] = "hidden";
        $arAttributes["name"] = static::chars($fieldName);
        $arAttributes["value"]= static::chars($value);
        
        if($checked){
            $arAttributes["checked"] = "checked";
        }
        
        return static::tag("input", false, $arAttributes);
    }
    
    static public function file($fieldName = "", $arAttributes = array()){
        $arAttributes["type"] = "file";
        $arAttributes["name"] = static::chars($fieldName);
        
        return static::tag("input", false, $arAttributes);
    }
    
    static public function button($label = "button", $arAttributes = array()){
        if(!isset($arAttributes["type"])){
            $arAttributes["type"] = "button";
        }
        
        return static::tag("button", $label, $arAttributes);
    }
    
    static public function submit($label = "submit", $arAttributes = array()){
        $arAttributes["type"] = "submit";
        $arAttributes["name"] = static::chars($fieldName);
        $arAttributes["value"]= static::chars($label);
        
        return static::tag("input", false, $arAttributes);
    }
    
    static public function text($fieldName = "", $value = "", $arAttributes = array()){
        $arAttributes["type"] = "text";
        $arAttributes["name"] = static::chars($fieldName);
        $arAttributes["value"]= static::chars($value);
        
        return static::tag("input", false, $arAttributes);
    }
    
    static public function textarea($fieldName = "", $value = "", $arAttributes = array()){
        $arAttributes["name"] = static::chars($fieldName);
        
        return static::tag("textarea", $value, $arAttributes);
    }
    
    static public function password($fieldName = "", $value = "", $arAttributes = array()){
        $arAttributes["type"] = "password";
        $arAttributes["name"] = static::chars($fieldName);
        $arAttributes["value"]= static::chars($value);
        
        return static::tag("input", false, $arAttributes);
    }
    
    static public function a($label = "", $url = "", $arAttributes = array()){
        $arAttributes["href"] = $url;
        
        return static::tag("a", $label, $arAttributes);
    }
    
    static public function img($src, $arAttributes = array()){
        $arAttributes["src"] = $src;
        
        return static::tag("img", false, $arAttributes);
    }
    
    static public function getOptionsList($arData = array(), $selected = false, $arAttributes = array()){
        if(!is_array($arData)){
            return "";
        }
        
        $str = "";
        
        if(!is_array($selected)){
            $arSelected = array($selected => 1);
        }else{
            $arSelected = array();
            
            foreach($selected AS $selectedValue){
                $arSelected[$selectedValue] = 1;
            }
        }
        
        foreach($arData AS $value => $title){
            $attributes = "";

            if(isset($arSelected[$value])){
                $arAttributes[$value]["selected"] = "selected";
            }
            
            if(isset($arAttributes[$value]) && is_array($arAttributes[$value])){
                $attributes = static::getAttributeString($arAttributes[$value]);    
            }
            
            $str.= "<option" . $attributes . " value=\"" . static::chars($value) . "\">" . static::chars($title) . "</option>\n";
        }
        
        return $str;
    }
}
?>