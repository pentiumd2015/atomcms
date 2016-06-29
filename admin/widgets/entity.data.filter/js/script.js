var EntityDataFilter = function(options){
    var $this = this;
    $this.options = options;
    
    this.clear = function(){
        AdminTools.clearForm("#" + this.options.filterId);
    }
    
    this.getValues = function(){
        var urlParams = AdminTools.parseStr(location.search.toString());
        var $form       = $("#" + this.options.filterId);
        var values    = $("#" + this.options.filterId).find("select,textarea,input")
        
        var arTmpData = values.filter(function(){
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
            if(urlParams[arTmpData[i].name]){
                delete urlParams[arTmpData[i].name];
            }
        }
            
        var values = values.serializeArray().filter(function(item){
            return item.value.length > 0; 
        });
        
        for(var i=0;i<values.length;i++){
            if(urlParams[values[i].name]){
                if(typeof urlParams[values[i].name] == "string"){
                    urlParams[values[i].name] = [urlParams[values[i].name]];
                }
                
                urlParams[values[i].name].push(values[i].value);
            }else{
                urlParams[values[i].name] = values[i].value;
            }
        }
 
        return urlParams;
    }
    
    this.hideSpinner = function(type){
        switch(type){
            case "submit":
            case "reset":
                $("#" + this.options.filterId).find(".form-actions button[type=" + type + "]").removeClass("btn-spin");
                break;
            default:
                $("#" + this.options.filterId).find(".form-actions button.btn-spin").removeClass("btn-spin");
        }
    }
    
    this.showSpinner = function(type){
        switch(type){
            case "submit":
            case "reset":
                $("#" + this.options.filterId).find(".form-actions button[type=" + type + "]").addClass("btn-spin");
                break;
        }
    }
    
    this.apply = function(form){
        var values = this.getValues();
        
        if(typeof window[this.options.onApplyBefore] == "function"){
            window[this.options.onApplyBefore].call(this, values);
        }
        
        if(typeof window[this.options.onApplyAfter] == "function"){
            window[this.options.onApplyAfter].call(this, values);
        }
    }
    
    $("#" + this.options.filterId).on("reset", function(){
        $this.clear(this);
        
        $this.showSpinner("reset");
        
        $this.apply(this);
        
        return false;
    });
    
    $("#" + this.options.filterId).on("submit", function(){
        $this.showSpinner("submit");
        
        $this.apply(this);
        
        return false;
    });
}