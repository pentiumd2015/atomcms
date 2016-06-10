var AdminList = function(options){
    var $this = this;
    $this.options = options;
    
    $("#" + this.options.listID + "").colResizable({
        liveDrag    : true,
        minWidth    : 50,
        headerOnly  : true
    });
    
    $("." + this.options.listID + "_pagination").on("click", "li a", function(e){
        e.preventDefault();
        
        if(!$(this).closest("li").hasClass("active")){
            var url = $(this).attr("href");
            
            if(url && url.length && url != "#"){
                $this.ajax(["#" + $this.options.listID + "_tbody", "." + $this.options.listID + "_pagination"], {
                    url: url
                });
            }
        }
    });
    
    this.selectItem = function(checkbox){
        var $items      = $("#" + this.options.listID).find("." + $this.options.listID + "_checkbox_item").not(":disabled");
        var checkedSize = $items.filter(":checked").size();
        
        $("#" + this.options.listID).find("#" + $this.options.listID + "_checkbox_all").prop("checked", ($items.size() == checkedSize));
        
        this.enableGroupOperations((checkedSize > 0));
    }
    
    this.applySort = function(column){
        var $column = $(column);
        
        $column.find('input[name="' + this.options.sortKey + '"]').prop("checked", true);
        $column.removeClass("sorting_asc sorting_desc");
        
        if($column.find('input[name="' + this.options.sortByKey + '"][value="asc"]').is(":checked")){
            $column.find('input[name="' + this.options.sortByKey + '"][value="desc"]').prop("checked", true);
            $column.addClass("sorting_desc");
        }else{
            $column.find('input[name="' + this.options.sortByKey + '"][value="asc"]').prop("checked", true);
            $column.addClass("sorting_asc");
        }
        
        $column.siblings().removeClass("sorting_asc sorting_desc");
        
        this.refresh();
    }
    
    this.selectAll = function(bool){
        var $items = $("#" + this.options.listID).find("." + $this.options.listID + "_checkbox_item").not(":disabled");
        
        this.enableGroupOperations($items.prop("checked", bool));
    }
    
    this.applyGroupOperation = function(){
        var $items      = $("#" + this.options.listID).find("." + $this.options.listID + "_checkbox_item").not(":disabled");
        
        var arData      = $items.filter(":checked");
        var arGroupData = $("#" + this.options.listID + "_wrapper").find("." + this.options.listID + "_group_actions *");

        arData = $.merge(arData, arGroupData);
        
        if(arData.length){
            $this.ajax(["#" + this.options.listID + "_tbody", "." + this.options.listID + "_pagination"], {
                type: "POST",
                data: arData
            });
        }
    }
    
    this.enableGroupOperations = function(enable){
        var $groupSelect = $("#" + this.options.listID + "_group_choice_select");
        
        if(!enable){
            $groupSelect.prop("disabled", true).val("");
        }else{
            $groupSelect.prop("disabled", false);
        }
        
        $groupSelect.trigger("change");
    }
    
    this.getSortData = function(){
        return $("#" + this.options.listID + " > thead").find(".sortable_field input");
    }
    
    this.getPageData = function(){
        return $("." + this.options.listID + "_pagination").find('input[name="page"]');
    }
    
    this.getPerPageData = function(){
        return $("#" + this.options.listID + "_per_page_select");
    }
    
    this.getData = function(){
        var arData      = $.merge(this.getSortData(), this.getPageData());
        arData          = $.merge(arData, this.getPerPageData());
        
        var arUrlParams = AdminTools.parseStr(location.search.toString());
        
        //берем все значения элементов, чтобы удалить из массива текущих параметров
        var arTmpData = arData.filter(function(){
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
        
        //затем отбираем только те параметры, у которых есть значение и они выделены
             
        var arData = arData.serializeArray().filter(function(item){
            return item.value.length > 0; 
        });

        for(var i=0;i<arData.length;i++){
            if(arUrlParams[arData[i].name]){
                if(typeof arUrlParams[arData[i].name] == "string"){
                    arUrlParams[arData[i].name] = [arUrlParams[arData[i].name]];
                }
                
                arUrlParams[arData[i].name].push(arData[i].value);
            }else{
                arUrlParams[arData[i].name] = arData[i].value;
            }
        }
        
        return arUrlParams;
    }
    
    this.refresh = function(arUserData){
        var arData  = (typeof arUserData == "object") ? arUserData : this.getData() ;

        this.ajax(["#" + this.options.listID + "_tbody", "." + this.options.listID + "_pagination"], {
            data    : arData,
            type    : "GET",
            url     : this.options.baseURL
        });
    }
    
    this.ajax = function(arRefreshContainers, params){
        var $spinner = $("#" + this.options.listID + "_wrapper").find(".admin_list_spinner");

        params.beforeSend   = function(){
            $spinner.show();
        };
        
        params.complete     = function(){
            $spinner.hide();
        };
        
        params.success      = function(r){
            $("#" + $this.options.listID + "_checkbox_all").prop("checked", false);
            
            $this.enableGroupOperations(false);
            
            if(typeof window[$this.options.onApplyAfter] == "function"){
                window[$this.options.onApplyAfter].call($this, params, arRefreshContainers, r);
            }
        };
        
        if(typeof window[$this.options.onApplyBefore] == "function"){
            window[$this.options.onApplyBefore].call(this, params);
        }

        AdminTools.ajaxRefresh(arRefreshContainers, params);
    }
}