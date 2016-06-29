function CModal(options){
    var opts = {
        width   : 600,
        height  : 350,
        title   : "",
        buttons : "<button class=\"btn btn-primary\">Применить</button>\
                   <button class=\"btn btn-default\" data-mode=\"close\">Отмена</button>",
        body    : "",
        overlay :  "<div class=\"modal-backdrop\"></div>",
        template: "<div class=\"modal\">\
                        <div class=\"modal-dialog\" data-mode=\"close\">\
                            <div class=\"modal-content\">\
                                <div class=\"modal-header\">\
                                    <button type=\"button\" class=\"close\" data-mode=\"close\">&times;</button>\
                                    <h4 class=\"modal-title\"></h4>\
                                </div>\
                                <div class=\"modal-body\"></div>\
                                <div class=\"modal-footer\"></div>\
                            </div>\
                        </div>\
                    </div>",
        url         : false,        
        onShow      : $.noop,
        onShown     : $.noop,
        onClose     : $.noop,
        onClosed    : $.noop
    }

    options = $.extend({}, opts, options);
    
    var $this = this;
    
    var arModals = $(document).data("data-modal");
    
    if(!arModals){
        arModals = [];
    }
    
    arModals.push(this);
    
    $(document).data("data-modal", arModals);
    
    this.init = function(){
        this.$element = $(options.template);
        
        this.setTitle(options.title);
        this.setBody(options.body);
        this.setButtons(options.buttons);
        
        this.setWidth(options.width);
        this.setHeight(options.height);
        
        this.$element.find('[data-mode="close"]').on("click", function(e){
            if($(e.target).data("mode") == "close"){
                $this.close();
            }
        });
        
        $("body").append(this.$element);
        
        return this;
    }
    
    this.setWidth = function(width){
        this.$element.find(".modal-content").width(width);
    };
    
    this.setHeight = function(height){
        this.$element.find(".modal-body").height(height);
    };
    
    this.show = function(){
        var maxIndex    = parseInt(this.$element.css("z-index"));
        var arModals    = $(document).data("data-modal");
        
        if(arModals.length > 1){
            for(var i=0;i<arModals.length;i++){
                var $testModal = arModals[i];
                
                if($testModal.$element.is(":visible") && $testModal != this){
                    var tmpIndex = parseInt(arModals[i].$element.css("z-index"));
                    
                    if(tmpIndex > maxIndex){
                        maxIndex = tmpIndex;
                    }
                }
            }
        }

        this.$element.css("z-index", maxIndex + 2);
        
        this.$overlay = $(options.overlay);
        $("body").addClass("modal-opened").append(this.$overlay).width();
        this.$overlay.css("z-index", maxIndex + 1);
        
        options.onShow.call(this);
        
        if(!options.url){
            this.$element.css("display", "table");
            
            options.onShown.call(this);
        }else{
            $.ajax({
                type: "GET",
                url: options.url,
                dataType: "json",
                success: function(r){
                    if(r && r.result == 1 && r.content){
                        if(r.content.title){
                            $this.setTitle(r.content.title);
                        }
                        
                        if(r.content.body){
                            $this.setBody(r.content.body);
                        }
                        
                        if(r.content.buttons){
                            $this.setButtons(r.content.buttons);
                        }
                        
                        $this.$element.css("display", "table");

                        options.onShown.call($this);
                    }
                }
            });
        }
        
        return this;
    }
    
    this.destroy = function(){
        this.$element.remove();
        this.$overlay.remove();
        
        var arModals = $(document).data("data-modal");
        
        if(arModals){
            for(var i=0;i<arModals.length;i++){
                if(arModals[i] === this){
                    arModals.splice(i,1);
                }
            }
            
            if(arModals.length == 0){
                $("body").removeClass("modal-opened");
            }
        }
        
        delete this;
    }
    
    this.close = function(){
        this.$element.css("z-index", "");
        
        options.onClose.call(this);
        
        this.destroy();
        
        options.onClosed();
    }
    
    this.setBody = function(body){
        this.$element.find(".modal-body").html(body);
        
        return this;
    };
    
    this.setTitle = function(title){
        this.$element.find(".modal-title").html(title);
        
        return this;
    };
    
    this.setButtons = function(buttons){
        this.$element.find(".modal-footer").html(buttons);
        
        return this;
    };
    
    this.init();
}