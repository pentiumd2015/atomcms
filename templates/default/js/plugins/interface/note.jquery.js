(function($){
    var timeout;
    
    var html = '<div id="simple_note" class="simple_note">\
                    <div class="simple_note_container" style="display:none;">\
                        <div class="simple_note_close">×</div>\
                        <div class="simple_note_header"></div>\
                        <div class="simple_note_message"></div>\
                    </div>\
                </div>';
    
    $.note = function(opts){
        this.defaults = {
            header          : "",
            text            : "",
            showClose       : true,
            onOpen          : $.noop,
            onClose         : $.noop,
            duration        : false,
            openDuration    : "normal",
            closeDuration   : "normal",
            theme           : "",
            easing          : "swing",
            themes          : ["success", "error", "warning", "info"],
            animateOpen     : {
                opacity: "show"
            },
            animateClose    : {
                opacity: "hide"
            }
        };
        
        if(!$("#simple_note").data("instance")){
            $('body').append(html);
            $("#simple_note").data("instance", this);
        }
        
        var $note       = $("#simple_note");
        var $close      = $note.find(".simple_note_close");
        var $message    = $note.find(".simple_note_message");
        var $header     = $note.find(".simple_note_header");
        var $container  = $note.find(".simple_note_container");
        
        var instance    = $note.data("instance");
        
        var options     = $.extend({}, instance.defaults, opts);
        
        this.close = function(){
            $container.stop(true, true).animate(options.animateClose, options.closeDuration, options.easing, function(){
                options.onClose(options, $note);
            });
        };
        
        $close.off("click").on("click", this.close);
        
        this.open = function(){
            $container.stop(true, true).animate(options.animateClose, options.closeDuration, options.easing, this._open);
        }
        
        this._open = function(){
            for(var i in options.themes){
                $container.removeClass("simple_note_" + options.themes[i]);
            }
            
            if(options.theme.length && $.inArray(options.theme, options.themes) != -1){
                $container.addClass("simple_note_" + options.theme);
            }
            
            $message.empty();
            $header.empty();
            
            $close[(options.showClose ? "show" : "close")];
            
            if(options.title.length){
                $message.html(options.title);
            }
            
            if(options.header.length){
                $header.html(options.header).show();
            }else{
                $header.hide();
            }
            
            $container.animate(options.animateOpen, options.openDuration, options.easing, function(){
                options.onOpen(options, $note);
            });
        }
        
        this.open();
        
        clearTimeout(timeout);
        
        if(options.duration){
            timeout = setTimeout(this.close, options.duration);
        }
    };
})(jQuery);
/*
<sup style="color: #D65C4F;">*</sup>
<span class="mandatory">*</span>
*/