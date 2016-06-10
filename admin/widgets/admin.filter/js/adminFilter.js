var AdminFilter = function(options){
    var $this = this;
    $this.options = options;
    
    this.clear = function(){
        AdminTools.clearForm("#" + this.options.filterID);
    }
    
    this.getValues = function(){
        var arUrlParams = AdminTools.parseStr(location.search.toString());
        var $form       = $("#" + this.options.filterID);
        var arValues    = $("#" + this.options.filterID).find("select,textarea,input")
        
        var arTmpData = arValues.filter(function(){
                                    return !this.disabled && this.name.length > 0;
                                })
                                .map(function(){
                                     return {
                                        name    : this.name, 
                                        value   : this.value
                                     }
                                })
                                .get();

        for(var i in arTmpData){
            if(arUrlParams[arTmpData[i].name]){
                delete arUrlParams[arTmpData[i].name];
            }
        }
            
        var arValues = arValues.serializeArray().filter(function(item){
            return item.value.length > 0; 
        });
        
        for(var i=0;i<arValues.length;i++){
            if(arUrlParams[arValues[i].name]){
                if(typeof arUrlParams[arValues[i].name] == "string"){
                    arUrlParams[arValues[i].name] = [arUrlParams[arValues[i].name]];
                }
                
                arUrlParams[arValues[i].name].push(arValues[i].value);
            }else{
                arUrlParams[arValues[i].name] = arValues[i].value;
            }
        }
 
        return arUrlParams;
    }
    
    this.hideSpinner = function(type){
        switch(type){
            case "submit":
            case "reset":
                $("#" + this.options.filterID).find(".form-actions button[type=" + type + "]").removeClass("btn-spin");
                break;
            default:
                $("#" + this.options.filterID).find(".form-actions button.btn-spin").removeClass("btn-spin");
        }
    }
    
    this.showSpinner = function(type){
        switch(type){
            case "submit":
            case "reset":
                $("#" + this.options.filterID).find(".form-actions button[type=" + type + "]").addClass("btn-spin");
                break;
        }
    }
    
    this.apply = function(form){
        var arValues = this.getValues();
        
        if(typeof window[this.options.onApplyBefore] == "function"){
            window[this.options.onApplyBefore].call(this, arValues);
        }
        
        if(typeof window[this.options.onApplyAfter] == "function"){
            window[this.options.onApplyAfter].call(this, arValues);
        }
    }
    
    $("#" + this.options.filterID).on("reset", function(){
        $this.clear(this);
        
        $this.showSpinner("reset");
        
        $this.apply(this);
        
        return false;
    });
    
    $("#" + this.options.filterID).on("submit", function(){
        $this.showSpinner("submit");
        
        $this.apply(this);
        
        return false;
    });
}