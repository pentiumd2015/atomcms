<?
namespace Helpers;

class CHtml{
    public static $voidTags = [
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
    ];

    public static function escape($str){
        /*return strtr($str,  array(
            "&"     => "&amp;",
            "<"     => "&lt;",
            ">"     => "&gt;",
            "\""    => "&quot;",
            "'"     => "&#039;"
        ));*/


        return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8", false);
    }
    
    public static function getAttributeString(array $attributes = []){
        $str = "";

        foreach($attributes AS $name => $value){
            $str.= " " . $name . '="' . static::escape($value) . '"';
        }
        
        return $str;
    }
    
    public static function tag($tagName, $value = "", $attributes = []){
        return "<" . $tagName . static::getAttributeString($attributes) . ">" . (isset(static::$voidTags[strtolower($tagName)]) ? "" : $value . "</" . $tagName . ">") . "\n";
    }
        
    public static function multiselect($fieldName, $data = [], $selectedValues = [], $attributes = []){
        if(isset($fieldName) && substr($fieldName, -2) != "[]"){
            $fieldName.= "[]";
        }

        $attributes["multiple"] = "multiple";

        return static::select($fieldName, $data, $selectedValues, $attributes);
    }
    
    public static function select($fieldName, $data = [], $selectedValue = null, $attributes = []){
        $optionsAttributes = isset($attributes["options"]) && is_array($attributes["options"]) ? $attributes["options"] : [] ;
        
        unset($attributes["options"]);

        if(isset($fieldName)){
            $attributes["name"] = $fieldName;
        }
        
        return static::tag("select", static::getOptionsList($data, $selectedValue, $optionsAttributes), $attributes);
    }
    
    public static function radio($fieldName, $checked = false, $attributes = []){
        if(isset($fieldName)){
            $attributes["name"] = $fieldName;
        }
        
        if($checked){
            $attributes["checked"] = "checked";
        }

        $attributes["type"] = "radio";

        return static::tag("input", false, $attributes);
    }
    
    public static function boolean($fieldName, $values = [], $checked = false, $attributes = []){
        $falseValue             = isset($values[0]) ? $values[0] : 0 ;
        $attributes["value"]    = isset($values[1]) ? $values[1] : 1 ;
        $checked                = (bool)$checked;

        $return = static::hidden($fieldName, $falseValue);
        $return.= static::checkbox($fieldName, $checked, $attributes);
        
        return $return;
    }
    
    public static function checkbox($fieldName, $checked = false, $attributes = []){
        if(isset($fieldName)){
            $attributes["name"] = $fieldName;
        }
        
        if($checked){
            $attributes["checked"] = "checked";
        }

        $attributes["type"] = "checkbox";

        return static::tag("input", false, $attributes);
    }
    
    public static function hidden($fieldName, $value = "", $attributes = []){
        if(isset($fieldName)){
            $attributes["name"] = $fieldName;
        }

        $attributes["value"]= $value;
        $attributes["type"] = "hidden";

        return static::tag("input", false, $attributes);
    }
    
    public static function file($fieldName, $attributes = []){
        if(isset($fieldName)){
            $attributes["name"] = $fieldName;
        }

        $attributes["type"] = "file";
        
        return static::tag("input", false, $attributes);
    }
    
    public static function button($label = "button", $attributes = []){
        if(!isset($attributes["type"])){
            $attributes["type"] = "button";
        }
        
        return static::tag("button", $label, $attributes);
    }
    
    public static function submit($value = "submit", $attributes = []){
        $attributes["type"] = "submit";
        $attributes["value"]= $value;
        
        return static::tag("input", false, $attributes);
    }
    
    public static function text($fieldName, $value = "", $attributes = []){
        if(isset($fieldName)){
            $attributes["name"] = $fieldName;
        }

        $attributes["type"] = "text";
        $attributes["value"]= $value;
        
        return static::tag("input", false, $attributes);
    }
    
    public static function textarea($fieldName, $value = "", $attributes = []){
        if(isset($fieldName)){
            $attributes["name"] = $fieldName;
        }

        $attributes["value"]= $value;
        
        return static::tag("textarea", $value, $attributes);
    }
    
    public static function password($fieldName, $value = "", $attributes = []){
        if(isset($fieldName)){
            $attributes["name"] = $fieldName;
        }

        $attributes["type"] = "password";
        $attributes["value"]= $value;
        
        return static::tag("input", false, $attributes);
    }
    
    public static function a($label = "", $url = null, $attributes = []){
        if($url){
            $attributes["href"] = $url;
        }
        
        return static::tag("a", $label, $attributes);
    }
    
    public static function img($src, $attributes = []){
        $attributes["src"] = $src;
        
        return static::tag("img", false, $attributes);
    }
    
    public static function getOptionsList(array $data = [], $selected = null){
        if(!is_array($data)){
            return "";
        }
        
        $str = "";
        
        if(!is_array($selected)){
            if($selected !== null){
                $selected = [$selected => 1];
            }
        }else{
            $tmpSelected    = $selected;
            $selected       = [];
            
            foreach($tmpSelected AS $selectedValue){
                $selected[$selectedValue] = 1;
            }
        }
        
        foreach($data AS $value => $title){
            $attributesString = "";

            if(isset($selected[$value])){
                $attributes[$value]["selected"] = "selected";
            }
            
            if(isset($attributes[$value]) && is_array($attributes[$value])){
                $attributesString = static::getAttributeString($attributes[$value]);
            }
            
            $str.= '<option' . $attributesString . ' value="' . static::escape($value) . '">' . static::escape($title) . '</option>\n';
        }
        
        return $str;
    }
}