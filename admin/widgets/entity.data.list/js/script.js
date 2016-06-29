var EntityDataList = function(options){
    var $this = this;
    $this.options = options;
    
    $("#" + this.options.listId + "").colResizable({
        liveDrag    : true,
        minWidth    : 50,
        headerOnly  : true
    });
    
    $("." + this.options.listId + "_pagination").on("click", "li a", function(e){
        e.preventDefault();
        
        if(!$(this).closest("li").hasClass("active")){
            var url = $(this).attr("href");
            
            if(url && url.length && url != "#"){
                $this.ajax(["#" + $this.options.listId + "_tbody", "." + $this.options.listId + "_pagination"], {
                    url: url
                });
            }
        }
    });
    
    this.selectItem = function(checkbox){
        var $items      = $("#" + this.options.listId).find("." + $this.options.listId + "_checkbox_item").not(":disabled");
        var checkedSize = $items.filter(":checked").size();
        
        $("#" + this.options.listId).find("#" + $this.options.listId + "_checkbox_all").prop("checked", ($items.size() == checkedSize));
        
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
        var $items = $("#" + this.options.listId).find("." + $this.options.listId + "_checkbox_item").not(":disabled");
        
        this.enableGroupOperations($items.prop("checked", bool));
    }
    
    this.applyGroupOperation = function(){
        var $items      = $("#" + this.options.listId).find("." + $this.options.listId + "_checkbox_item").not(":disabled");
        
        var data      = $items.filter(":checked");
        var groupData = $("#" + this.options.listId + "_wrapper").find("." + this.options.listId + "_group_actions *");

        data = $.merge(data, groupData);
        
        if(data.length){
            $this.ajax(["#" + this.options.listId + "_tbody", "." + this.options.listId + "_pagination"], {
                type: "GET",
                data: data
            });
        }
    }
    
    this.enableGroupOperations = function(enable){
        var $groupSelect = $("#" + this.options.listId + "_group_choice_select");
        
        if(!enable){
            $groupSelect.prop("disabled", true).val("");
        }else{
            $groupSelect.prop("disabled", false);
        }
        
        $groupSelect.trigger("change");
    }
    
    this.getSortData = function(){
        return $("#" + this.options.listId + " > thead").find(".sortable_field input");
    }
    
    this.getPageData = function(){
        return $("." + this.options.listId + "_pagination").find('input[name="page"]');
    }
    
    this.getPerPageData = function(){
        return $("#" + this.options.listId + "_per_page_select");
    }
    
    this.getData = function(){
        var data      = $.merge(this.getSortData(), this.getPageData());
        data          = $.merge(data, this.getPerPageData());
        
        var urlParams = AdminTools.parseStr(location.search.toString());
        
        //берем все значения элементов, чтобы удалить из массива текущих параметров
        var tmpData = data.filter(function(){
                             return !this.disabled && this.name.length > 0;
                         })
                         .map(function(){
                             return {
                                name    : this.name, 
                                value   : this.value
                             }
                         })
                         .get();
        
        for(var i in tmpData){
            if(urlParams[tmpData[i].name]){
                delete urlParams[tmpData[i].name];
            }
        }
        
        //затем отбираем только те параметры, у которых есть значение и они выделены
             
        var data = data.serializeArray().filter(function(item){
            return item.value.length > 0; 
        });

        for(var i=0;i<data.length;i++){
            if(urlParams[data[i].name]){
                if(typeof urlParams[data[i].name] == "string"){
                    urlParams[data[i].name] = [urlParams[data[i].name]];
                }
                
                urlParams[data[i].name].push(data[i].value);
            }else{
                urlParams[data[i].name] = data[i].value;
            }
        }
        
        return urlParams;
    }
    
    this.refresh = function(userData){
        var data  = (typeof userData == "object") ? userData : this.getData() ;

        this.ajax(["#" + this.options.listId + "_tbody", "." + this.options.listId + "_pagination"], {
            data    : data,
            type    : "GET",
            url     : this.options.baseURL
        });
    }
    
    this.ajax = function(refreshContainers, params){
        var $spinner = $("#" + this.options.listId + "_wrapper").find(".admin_list_spinner");

        params.beforeSend   = function(){
            $spinner.show();
        };
        
        params.complete     = function(){
            $spinner.hide();
        };
        
        params.success      = function(r){
            $("#" + $this.options.listId + "_checkbox_all").prop("checked", false);
            
            $this.enableGroupOperations(false);
            
            if(typeof window[$this.options.onApplyAfter] == "function"){
                window[$this.options.onApplyAfter].call($this, params, refreshContainers, r);
            }
        };
        
        if(typeof window[$this.options.onApplyBefore] == "function"){
            window[$this.options.onApplyBefore].call(this, params);
        }

        AdminTools.ajaxRefresh(refreshContainers, params);
    }
}