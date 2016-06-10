var AdminTools = {
    timeout: null,
    delay: function(callback, duration){
        if(typeof callback == "function"){
            if(!duration){
                duration = 500;
            }
            
            clearTimeout(this.timeout);
            
            this.timeout = setTimeout(callback, duration);
        }
    },
    parseStr: function(url){
        var arQueryParams = {};
        
        if(url.indexOf("?") != -1){
            var uri = url.substr(1);
            
            if(uri.length){
                arQueryParams = this.parseQuery(uri);
            }
        }
        
        return arQueryParams;
    },
    parseQuery: function(uri){
        var arQueryParams = {};
        
        var a = decodeURIComponent(uri).split("&");
        
        for(var i=0;i<a.length;i++){
            var b = a[i].split("=");
            
            if(arQueryParams[b[0]]){
                if(typeof arQueryParams[b[0]] == "string"){
                    arQueryParams[b[0]] = [arQueryParams[b[0]]];
                }
                
                arQueryParams[b[0]].push(b[1]);
            }else{
                arQueryParams[b[0]] = b[1];
            }
        }
        
        return arQueryParams;
    },
    getTemplate: function(templateSelector, arParams){
        var html = $(templateSelector).html();
        
        for(var item in arParams){
            html = html.replace(RegExp('\#' + item + '\#', "gi"), arParams[item]);
        }
        
        return html;
    },
    ajaxRefresh: function(arRefreshContainers, params){
        var oldURL = location.toString();
        
        var options = $.extend({
            type    : "POST",
            url     : oldURL,
            data    : {},
            dataType: 'html',
            success : $.noop
        }, (params || {}));
        
        var success = options.success;
        
        options.success = function(r){
            var $html = $("<div>" + r + "</div>");
            
            if(arRefreshContainers && typeof arRefreshContainers == "object"){
                for(var item in arRefreshContainers){
                    var container   = arRefreshContainers[item];
                    var $obj        = $(container);
                    
                    $html.find(container).each(function(i){
                        var html = $(this).html();
                        
                        $obj.eq(i).html(html);
                    });
                }
            }
            
            if(oldURL != options.url || (options.type.toUpperCase() == "GET" && options.data)){
                var newURL  = options.url;
                var query   = options.data;
    
                if(typeof query !== "string"){
                    query = $.param(options.data);
                }
                
                if(query.length){
                    newURL+= newURL.indexOf("?") == -1 ? "?" : "&" ;
                    newURL+= decodeURIComponent(query);
                }
                
                history.pushState(
                    {url: oldURL}, 
                    document.title, 
                    newURL
                );
            }
            
            if(typeof success == "function"){
                success(arRefreshContainers, r);
            }
        }
        
        $.ajax(options);
    },
    clearForm: function(form){
        $(form).find("select,textarea,input").each(function(){
            switch(this.tagName.toLowerCase()){
                case "input":
                    switch(this.type){
                        case "checkbox":
                            this.checked = false;
                            
                            break;
                        case "radio":
                            if(this.value.length){
                                this.checked = false;
                            }else{
                                this.checked = true;
                            }
                            
                            break;
                        default:
                            this.value = "";
                    }
                    
                    break;
                case "textarea":
                    this.value = "";
                    
                    break;
                case "select":
                    this.selectedIndex = 0;
                    break;
            }
        });
    }
}

AdminTools.html = {
    voidTags: {
        'area'      : 1,
        'base'      : 1,
        'br'        : 1,
        'col'       : 1,
        'command'   : 1,
        'embed'     : 1,
        'hr'        : 1,
        'img'       : 1,
        'input'     : 1,
        'keygen'    : 1,
        'link'      : 1,
        'meta'      : 1,
        'param'     : 1,
        'source'    : 1,
        'track'     : 1,
        'wbr'       : 1,
    },
    chars: function(str){
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        
        return str.toString().replace(/[&<>"']/g, function(m){ 
            return map[m]; 
        });
    },
    getAttributeString: function(arAttributes){
        var str = "";
        
        if(typeof arAttributes == "object"){
            for(var name in arAttributes){
                str+= " " + name + '="' + this.chars(arAttributes[name]) + '"';
            }
        }
        
        return str;
    },
    tag: function(tagName, value, arAttributes){
        if(tagName && tagName.length){
            return "<" + tagName + this.getAttributeString(arAttributes) + ">" + (this.voidTags[tagName] ? "" : (value ? value : "") + "</" + tagName + ">");
        }
        
        return "";
    },
    multiselect: function(fieldName, arData, arSelected, arAttributes){
        if(!arAttributes){
            arAttributes = {};
        }
        
        arAttributes.multiple = "multiple";
        
        if(fieldName && fieldName.substr(-2) != "[]"){
            fieldName+= "[]";
        }
        
        return this.select(fieldName, arData, arSelected, arAttributes);
    },
    select: function(fieldName, arData, selectedValue, arAttributes){
        if(!arAttributes){
            arAttributes = {};
        }
        
        var arOptionsAttributes = (arAttributes.options && typeof arAttributes.options == "object") ? arAttributes.options : [] ;
        
        if(arAttributes.options){
            delete arAttributes.options;
        }
        
        arAttributes.name = this.chars(fieldName);
        
        return this.tag("select", this.getOptionsList(arData, selectedValue, arOptionsAttributes), arAttributes);
    },
    radio: function(fieldName, checked, arAttributes){
        if(!arAttributes){
            arAttributes = {};
        }
        
        arAttributes.type   = "radio";
        arAttributes.name   = this.chars(fieldName);
        arAttributes.value  = arAttributes.value ? this.chars(arAttributes.value) : 1;
        
        if(checked){
            arAttributes.checked = "checked";
        }
        
        return this.tag("input", false, arAttributes);
    },
    checkbox: function(fieldName, checked, arAttributes){
        if(!arAttributes){
            arAttributes = {};
        }
        
        arAttributes.type   = "checkbox";
        arAttributes.name   = this.chars(fieldName);
        arAttribute.value   = arAttribute.value ? this.chars(arAttribute.value) : 1;
        
        if(checked){
            arAttributes.checked = "checked";
        }
        
        return this.tag("input", false, arAttributes);
    },
    hidden: function(fieldName, value, arAttributes){
        if(!arAttributes){
            arAttributes = {};
        }
        
        arAttributes.type   = "hidden";
        arAttributes.name   = this.chars(fieldName);
        arAttributes.value  = this.chars(value);
        
        return this.tag("input", false, arAttributes);
    },
    file: function(fieldName, arAttributes){
        if(!arAttributes){
            arAttributes = {};
        }
        
        arAttributes.type = "file";
        arAttributes.name = this.chars(fieldName);
        
        return this.tag("input", false, arAttributes);
    },
    button: function(label, arAttributes){
        if(!label){
            label = "Button";
        }
        
        if(!arAttributes){
            arAttributes = {};
        }
        
        if(!arAttributes.type){
            arAttributes.type = "button";
        }
        
        return this.tag("button", label, arAttributes);
    },
    submit: function(label, arAttributes){
        if(!label){
            label = "Submit";
        }
        
        if(!arAttributes){
            arAttributes = {};
        }
        
        arAttributes.type   = "submit";
        arAttributes.name   = this.chars(fieldName);
        arAttributes.value  = this.chars(label);
        
        return this.tag("input", false, arAttributes);
    },
    text: function(fieldName, value, arAttributes){
        if(!arAttributes){
            arAttributes = {};
        }
        
        arAttributes.type   = "text";
        arAttributes.name   = this.chars(fieldName);
        arAttributes.value  = this.chars(value);
        
        return this.tag("input", false, arAttributes);
    },
    textarea: function(fieldName, value, arAttributes){
        if(!arAttributes){
            arAttributes = {};
        }
        
        arAttributes.name = this.chars(fieldName);
        
        return this.tag("textarea", value, arAttributes);
    },
    password: function(fieldName, value, arAttributes){
        if(!arAttributes){
            arAttributes = {};
        }
        
        arAttributes.type   = "password";
        arAttributes.name   = this.chars(fieldName);
        arAttributes.value  = this.chars(value);
        
        return this.tag("input", false, arAttributes);
    },
    a: function(label, url, arAttributes){
        if(!arAttributes){
            arAttributes = {};
        }
        
        arAttributes.href = url;
        
        return this.tag("a", label, arAttributes);
    },
    img: function(src, arAttributes){
        if(!arAttributes){
            arAttributes = {};
        }
        
        arAttributes.src = src;
        
        return this.tag("img", false, arAttributes);
    },
    getOptionsList: function(arData, selected, arAttributes){
        if(!arAttributes){
            arAttributes = {};
        }
        
        if(typeof arData != "object"){
            return "";
        }
        
        var str = "";
        
        if(!typeof selected != "object"){
            var arSelected = {selected: 1};
        }else{
            var arSelected = {};
            
            for(var i in selected){
                arSelected[selected[i]] = 1;
            }
        }
        
        var value;
        
        for(var i=0;i<arData.length;i++){
            attributes = "";
            
            value = arData[i].value;
            
            if(arSelected[value]){
                arAttributes[value]["selected"] = "selected";
            }
            
            if(typeof arAttributes[value] == "object"){
                attributes = this.getAttributeString(arAttributes[value]);    
            }
            
            str+= "<option" + attributes + " value=\"" + this.chars(value) + "\">" + this.chars(arData[i].title) + "</option>\n";
        }
        
        return str;
    }
}