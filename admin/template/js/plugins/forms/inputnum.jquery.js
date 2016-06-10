(function($){
    var o = {
        max         : Infinity,
        min         : -Infinity,
        onOver      : $.noop,
        onChange    : $.noop,
    };
    
    $.fn.inputNum = function(options){
        var opts = $.extend({}, o, options);
        
        $(document).off("keypress", this.selector);
        
        $(document).on("keypress", this.selector, function(e){
            var key = e.keyCode || e.which;
            
            if(e.ctrlKey || e.altKey || e.metaKey || key < 32){//Ignore
				return false;
			}
            
            var currentChar = String.fromCharCode(key).replace(RegExp('\,', "gi"), ".");
            
            var cursorPos   = this.selectionStart;
            var value       = this.value.substring(0, cursorPos) + currentChar + this.value.substring(this.selectionEnd);            
            var data        = $(this).data();
            
            opts.max = typeof data.max != "undefined" && data.max !== false ? data.max : opts.max;
            opts.min = typeof data.min != "undefined" && data.min !== false ? data.min : opts.min;

            var expr = "^\\d+(\\.\\d*)?$";
            
            if(opts.min < 0){
                expr = "^\-?\\d";
                expr+= value.substring(0, 1) == "-" ? "*" : "+" ;
                expr+= "(\\.\\d*)?$"
            }
            
            var numExpr = new RegExp(expr);

            if(!numExpr.test(value)){ //если число, даже отрицательное ... пропускаем дальше
                return false;
            }
            
            if(value > opts.max || value < opts.min){ //если число за пределами диапазона
                this.value = value > opts.max ? opts.max : opts.min ;
                opts.onOver.call(this, value, opts);
            }else{
                this.value = value;
                opts.onChange.call(this, value, opts);
            }
            
            return false;
        });      
    }
})(jQuery);