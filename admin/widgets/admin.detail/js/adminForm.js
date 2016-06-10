var AdminForm = function(options){
    var $this = this;
    $this.options = options;
    
    this.$form = $("#" + this.options.formID);
    
    $(document).on("submit", "#" + this.options.formID, function(e){
        e.preventDefault();
        
        $this.submit();
        
        return false;
    });
    
    var $submitButtons = $("#" + $this.options.formID + " button[type=submit], #" + $this.options.formID + " input[type=submit]");
    
    this.hideSpinner = function(){
        $submitButtons.removeClass("btn-spin");
    }
    
    this.showSpinner = function(type){
        $submitButtons.addClass("btn-spin");
    }
    
    this.setErrors = function(arErrors){
        var $formItems = this.$form.find(".form-group");
        
        $formItems.removeClass("has-error")
                  .find(".control-content")
                  .find("label.error")
                  .remove();
        
        for(var field in arErrors){
            var $formItem           = $formItems.find('[name^="' + options.requestArrayName + '[' + field + ']"]');
            var $formItemWrapper    = $formItem.closest(".form-group");
            
            $formItemWrapper.addClass("has-error")
                            .find(".control-content")
                            .append("<label class=\"error\">" + arErrors[field].message + "</label>");
        }
    }
    
    this.submit = function(){
        if(typeof window[this.options.onApplyBefore] == "function"){
            window[this.options.onApplyBefore].call(this);
        }
        
        this.showSpinner();
        
        if(typeof window[$this.options.onApplyAfter] == "function"){
            window[this.options.onApplyAfter].call(this);
        }else{
            this.send();
        }
    }
    
    this.send = function(){
        var $this = this;
        var $form = this.$form;
        
        $.note({
            title: "<i class=\"icon-spinner3 spin\"></i>&nbsp;&nbsp;Сохранение...", 
            theme: "info"
        });
        
        AdminTools.delay(function(){
            $.ajax({
                type    : $form.attr("method"),
                url     : $form.attr("action"),
                data    : $form.serialize(),
                dataType: "json",
                success : function(r){
                    if(r && r.result == 1){
                        if(!r.hasErrors){
                            if(r.redirectURL){
                                var redirectURL = r.redirectURL;
                            }else{
                                var redirectURL = $this.options.url;
                            }
                            
                            location.href = redirectURL;
                        }else{
                            $this.hideSpinner();
                            
                            $.note({
                                header  : "Ошибка сохранения!", 
                                title   : "Данные не были сохранены", 
                                theme   : "error",
                                duration: 5000
                            });
                            
                            $this.setErrors(r.errors);
                        }
                    }
                }
            });
        }, 200);
    }
}