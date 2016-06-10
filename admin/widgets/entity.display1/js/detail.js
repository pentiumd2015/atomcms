var ViewSettings = function(){
    this.arBaseFields   = {};
    this.arExtraFields  = {};
    
    this.getTemplate = function(templateSelector, arParams){
        var html = $(templateSelector).html();
        
        for(var item in arParams){
            html = html.replace(RegExp('\#' + item + '\#', "gi"), arParams[item]);
        }
        
        return html;
    }
    
    this.getAvaliableFields = function(){
        var arAvailable = [];
        
        var arChoosenBaseFields     = {};
        var arChoosenExtraFields    = {};
        
        $('.display_list').find('input.data_item_type[value="field"]').each(function(){
            var $siblings   = $(this).siblings();
            var isBase      = $siblings.filter("input.data_item_is_base").val();
            var field       = $siblings.filter("input.data_item_field").val();
            
            if(isBase == true){
                arChoosenBaseFields[field]  = 1;
            }else{
                arChoosenExtraFields[field] = 1;
            }
        });
        
        for(var fieldName in this.arBaseFields){
            if(!arChoosenBaseFields[fieldName]){
                arAvailable.push({
                    isBase  : 1,
                    field   : fieldName,
                    title   : this.arBaseFields[fieldName].title
                });
            }
        }
        
        for(var fieldID in this.arExtraFields){
            if(!arChoosenExtraFields[fieldID]){
                arAvailable.push({
                    isBase  : 0,
                    field   : fieldID,
                    title   : this.arExtraFields[fieldID].title
                });
            }
        }
        
        return arAvailable;
    }
    
    this.escapeHtml = function(text){
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        
        return text.replace(/[&<>"']/g, function(m){ 
            return map[m]; 
        });
    }
    
    this.refreshDraggable = function(){
        $(".display_list").sortable({
            handle      : ".drag_handle_1",
            placeholder : "display_placeholder",
            start       : function(event, ui){
                var h = ui.item.outerHeight(true);
                ui.placeholder.css({height: h});
            },
        }).disableSelection();
        
        $(".display_list_group").sortable({
            handle      : ".drag_handle_2",
            placeholder : "display_placeholder",
            start       : function(event, ui){
                var h = ui.item.outerHeight(true);
                ui.placeholder.css({height: h});
            },
        }).disableSelection();
    }
    
    this.init = function(){
        var $this = this;
        
        this.refreshDraggable();
        
        /*add tab*/
        $(".tabs_container").find(".nav-tabs").find(".tab_add").on("click", function(e){
            e.preventDefault();
            
            var $modal = $("#view_tab_add_modal");
            
            $modal.find(".tab_add_input").val("");
            $modal.modal();
        });
        
        $("#view_tab_add_modal").find(".tab_add_apply").on("click", function(e){
            e.preventDefault();
            
            var $modal  = $("#view_tab_add_modal");
            var val     = $modal.find(".tab_add_input").val().trim();
    
            if(val.length){
                var tabIndex = 0;
                
                var $li = $(".tabs_container").find(".nav-tabs").find("li").has('a[data-index]');
                
                $li.each(function(){
                    var tmpIndex = $(this).find('a[data-index]').data("index");
                    
                    if(tmpIndex > tabIndex){
                        tabIndex = tmpIndex;
                    }
                });
                
                var nextIndex   = tabIndex + 1;
                var title       = $this.escapeHtml(val);
                
                var liHtml = $this.getTemplate("#tab_template", {
                    index: nextIndex,
                    title: title
                });
                
                var contentHtml = $this.getTemplate("#tab_content_template", {
                    index: nextIndex,
                    title: title
                });
                
                $lastLi = $li.last();
                
                if($lastLi.length){
                    $lastLi.after(liHtml);
                    $(".tabs_container").find('.tab-pane').last().after(contentHtml);
                }else{
                    $(".tabs_container").find(".nav-tabs").prepend(liHtml);
                    $(".tabs_container").find('.tab-content').prepend(contentHtml);
                }
                
                $modal.modal("hide");
            }
        });
        /*add tab*/
        
        /*edit tab*/
        $(".tabs_container").find(".nav-tabs").on("click", ".tab_edit", function(e){
            e.preventDefault();
            
            var $modal      = $("#view_tab_edit_modal");
            var $wrapper    = $(this).closest('a[data-toggle="tab"]');
            var index       = $wrapper.data("index");
            
            var title = $(".tabs_container").find('.tab-pane[data-index="' + index + '"]')
                                            .find(".data_tab_title")
                                            .val();
            
            $modal.find(".tab_edit_input").val(title);
            $modal.data({"index": index}).modal();
        });
        
        $("#view_tab_edit_modal").find(".tab_edit_apply").on("click", function(e){
            e.preventDefault();
            
            var $modal  = $("#view_tab_edit_modal");
            var index   = $modal.data("index");
            var val     = $modal.find(".tab_edit_input").val().trim();
    
            if(val.length){
                var $wrapper = $(".tabs_container").find(".nav-tabs")
                                                   .find('a[data-index="' + index + '"]');
                                                   
                $wrapper.find(".tab_title").html(val);
                
                $(".tabs_container").find('.tab-pane[data-index="' + index + '"]')
                                    .find(".data_tab_title")
                                    .val(val);
                
                $modal.modal("hide");
            }
        });
        /*edit tab*/
        
        /*delete tab*/
        $(".tabs_container").find(".nav-tabs").on("click", ".tab_remove", function(e){
            e.preventDefault();
            
            var $wrapper = $(this).closest('a[data-toggle="tab"]');
            
            var index = $wrapper.data("index");
            
            $(".tabs_container").find('.tab-pane[data-index="' + index + '"]').remove();
    
            $wrapper.closest("li").remove();
            
            /*делаем последнюю вкладку активной*/
            $(".tabs_container").find(".nav-tabs")
                                .find("li")
                                .has('a[data-index]')
                                .removeClass("active")
                                .last()
                                .addClass("active");
            
            $(".tabs_container").find('.tab-pane[data-index]')
                                .removeClass("active")
                                .last()
                                .addClass("in active");
            /*делаем последнюю вкладку активной*/
        });
        /*delete tab*/
        
        /*add field*/
        $(document).on('click', '.field_add', function(e){
            e.preventDefault();
            
            var $modal      = $("#view_field_add_modal");
            var index       = $(this).closest(".tab-pane").data("index");
            var arAvailable = $this.getAvaliableFields();
            var options     = "";
            
            for(var i in arAvailable){
                var item    = arAvailable[i];
                var title   = $this.escapeHtml(item.title);
                var value   = item.field;
                
                if(item.isBase){
                    value = "b_" + value;
                    title = "(Осн. поле) " + title;
                }else{
                    value = "e_" + value;
                    title = "(Доп. поле) " + title;
                }
                
                options+= '<option value="' + value + '">' + title + '</option>';
            }
            
            $modal.find(".select_available_fields").html(options);
            $modal.data({index: index}).modal();
        });
        
        $(".field_add_apply").on('click', function(e){
            e.preventDefault();
            
            var $modal          = $("#view_field_add_modal");
            var arValues        = $modal.find(".select_available_fields").val() || [];
            var tabIndex        = $modal.data("index");
            
            var $displayList    = $(".tabs_container").find('.tab-pane[data-index="' + tabIndex + '"]')
                                                      .find(".display_list");
            
            /*определяем максимальный индекс и берем следующий*/
            var fieldIndex  = 0;
             
            $displayList.children("li")
                        .each(function(){
                            var currentIndex = $(this).data("index");
                        
                            if(currentIndex > fieldIndex){
                                fieldIndex = currentIndex;
                            }
                        });
            
            fieldIndex++;
            /*определяем максимальный индекс и берем следующий*/
            
            for(var i in arValues){
                var field = arValues[i];
                
                var preffix = field.substr(0, 2);
                var field   = field.substr(2);
                
                if(preffix == "e_"){ //extra field
                    var fieldHtml = $this.getTemplate("#field_template", {
                        tabIndex    : tabIndex,
                        hiddenName  : "data[" + tabIndex + "][items][" + fieldIndex + "]",
                        dragHandle  : "drag_handle_1",
                        index       : fieldIndex,
                        isBase      : 0,
                        field       : field,
                        title       : $this.escapeHtml($this.arExtraFields[field].title)
                    });
                }else if(preffix == "b_"){ //base field
                    var fieldHtml = $this.getTemplate("#field_template", {
                        tabIndex    : tabIndex,
                        hiddenName  : "data[" + tabIndex + "][items][" + fieldIndex + "]",
                        dragHandle  : "drag_handle_1",
                        index       : fieldIndex,
                        isBase      : 1,
                        field       : field,
                        title       : $this.escapeHtml($this.arBaseFields[field].title)
                    });
                }
                
                $displayList.append(fieldHtml);
                
                fieldIndex++;
            }
            
            $this.refreshDraggable();
            
            $modal.modal("hide");
        });
        /*add field*/
        
        /*add group*/
        $(document).on('click', '.group_add', function(e){
            e.preventDefault();
            
            var $modal      = $("#view_group_add_modal");
            var index       = $(this).closest(".tab-pane").data("index");
            var arAvailable = $this.getAvaliableFields();
            var options     = "";
            
            for(var i in arAvailable){
                var item    = arAvailable[i];
                var title   = $this.escapeHtml(item.title);
                var value   = item.field;
                
                if(item.isBase == true){
                    value = "b_" + value;
                    title = "(Осн. поле) " + title;
                }else{
                    value = "e_" + value;
                    title = "(Доп. поле) " + title;
                }
                
                options+= '<option value="' + value + '">' + title + '</option>';
            }
            
            $modal.find(".select_available_fields").html(options);
            $modal.find(".group_add_value").val("");
            
            $modal.data({index: index}).modal();
        });
        
        $(".group_add_apply").on('click', function(e){
            e.preventDefault();
            
            var $modal      = $("#view_group_add_modal");
            
            var arValues    = $modal.find(".select_available_fields").val() || [];
            var groupTitle  = $modal.find(".group_add_value").val();
            var tabIndex    = $modal.data("index");
            
            var $displayList = $(".tabs_container").find('.tab-pane[data-index="' + tabIndex + '"]')
                                                   .find(".display_list");
            
            /*определяем максимальный индекс и берем следующий*/
            var groupIndex  = 0;
             
            $displayList.children("li")
                        .each(function(){
                            var currentIndex = $(this).data("index");
                        
                            if(currentIndex > groupIndex){
                                groupIndex = currentIndex;
                            }
                        });
            
            groupIndex++;
            /*определяем максимальный индекс и берем следующий*/
            
            var fieldIndex = 0;
            
            if(groupTitle){
                var fieldHtml = "";
                
                for(var i in arValues){
                    var field = arValues[i];
                    
                    var preffix = field.substr(0, 2);
                    var field   = field.substr(2);
                    
                    if(preffix == "e_"){ //extra field
                        fieldHtml+= $this.getTemplate("#field_template", {
                            tabIndex    : tabIndex,
                            hiddenName  : "data[" + tabIndex + "][items][" + groupIndex + "][items][" + fieldIndex + "]",
                            dragHandle  : "drag_handle_2",
                            index       : fieldIndex,
                            isBase      : 0,
                            field       : field,
                            title       : $this.escapeHtml($this.arExtraFields[field].title)
                        });
                    }else if(preffix == "b_"){ //base field
                        fieldHtml+= $this.getTemplate("#field_template", {
                            tabIndex    : tabIndex,
                            hiddenName  : "data[" + tabIndex + "][items][" + groupIndex + "][items][" + fieldIndex + "]",
                            dragHandle  : "drag_handle_2",
                            index       : fieldIndex,
                            isBase      : 1,
                            field       : field,
                            title       : $this.escapeHtml($this.arBaseFields[field].title)
                        });
                    }
                    
                    fieldIndex++;
                }
                
                var groupHtml = $this.getTemplate("#group_template", {
                    tabIndex    : tabIndex,
                    index       : groupIndex,
                    title       : $this.escapeHtml(groupTitle),
                    fieldItems  : fieldHtml
                });
                
                $displayList.append(groupHtml);
                
                $this.refreshDraggable();
                
                $("#view_group_add_modal").modal("hide");
            }
        });
        /*add group*/
        
        /*edit group*/
        $(document).on('click', '.group_edit', function(e){
            e.preventDefault();
            
            var $modal      = $("#view_group_edit_modal");
            var tabIndex    = $(this).closest(".tab-pane").data("index");
            var $li         = $(this).closest("li");
            var options     = "";
    
            //Добавим поля из группы
            $li.find(".display_list_group li").each(function(){
                var isBase  = $(this).find("input.data_item_is_base").val();
                var field   = $(this).find("input.data_item_field").val();
                
                if(isBase == true){
                    value = "b_" + field;
                    var title = "(Осн. поле) " + $this.arBaseFields[field].title;
                }else{
                    value = "e_" + field;
                    var title = "(Доп. поле) " + $this.arExtraFields[field].title;
                }
                
                options+= '<option selected="selected" value="' + value + '">' + title + '</option>';
            });
            
            var arAvailable = $this.getAvaliableFields();
            
            for(var i in arAvailable){
                var item    = arAvailable[i];
                var title   = $this.escapeHtml(item.title);
                var value   = item.field;
                
                if(item.isBase == true){
                    value = "b_" + value;
                    var title = "(Осн. поле) " + title;
                }else{
                    value = "e_" + value;
                    var title = "(Доп. поле) " + title;
                }
                
                options+= '<option value="' + value + '">' + title + '</option>';
            }
            
            var title = $li.find("input.data_item_title").val();
            
            $modal.find(".select_available_fields").html(options);
            $modal.find(".group_edit_value").val(title);
            $modal.data({tabIndex: tabIndex, itemIndex: $li.data("index")}).modal();
        });
        
        $(".group_edit_apply").on('click', function(e){
            e.preventDefault();
            
            var $modal          = $("#view_group_edit_modal");
            var arValues        = $modal.find(".select_available_fields").val() || [];
            var groupTitle      = $modal.find(".group_edit_value").val();
            var data            = $modal.data();
            
            var $displayList    = $(".tabs_container").find('.tab-pane[data-index="' + data.tabIndex + '"]')
                                                      .find(".display_list");
            
            var $li = $displayList.children('li[data-index="' + data.itemIndex + '"]');
            
            var fieldIndex = 0;
            
            if(groupTitle){          
                var fieldHtml = "";
                
                for(var i in arValues){
                    var field   = arValues[i];
                    var preffix = field.substr(0, 2);
                    var field   = field.substr(2);
                    
                    if(preffix == "e_"){ //extra field
                        fieldHtml+= $this.getTemplate("#field_template", {
                            tabIndex    : data.tabIndex,
                            hiddenName  : "data[" + data.tabIndex + "][items][" + data.itemIndex + "][items][" + fieldIndex + "]",
                            dragHandle  : "drag_handle_2",
                            index       : fieldIndex,
                            isBase      : 0,
                            field       : field,
                            title       : $this.escapeHtml($this.arExtraFields[field].title)
                        });
                    }else if(preffix == "b_"){ //base field
                        fieldHtml+= $this.getTemplate("#field_template", {
                            tabIndex    : data.tabIndex,
                            hiddenName  : "data[" + data.tabIndex + "][items][" + data.itemIndex + "][items][" + fieldIndex + "]",
                            dragHandle  : "drag_handle_2",
                            index       : fieldIndex,
                            isBase      : 1,
                            field       : field,
                            title       : $this.escapeHtml($this.arBaseFields[field].title)
                        });
                    }
                    
                    fieldIndex++;
                }
                
                $li.find("input.data_item_title").val(groupTitle);
                $li.find(".group_title").html(groupTitle);
                $li.find(".display_list_group").html(fieldHtml);
                
                $this.refreshDraggable();
                
                $modal.modal("hide");
            }
        });
        /*edit group*/
        
        /*remove group or field*/
        $(document).on("click", ".entity_view_remove", function(e){
            e.preventDefault();
            
            $(this).closest("li").remove();
        });
        /*remove group or field*/
        
        $(".display_form").on("submit", function(){
            $this = $(this);
            
            $.ajax({
                type    : $this.attr("method"),
                url     : $this.attr("action"),
                data    : decodeURIComponent($this.serialize()),
                dataType: "json",
                success : function(r){
                    if(r && r.result == 1){
                        if(!r.hasErrors){
                            location.reload();
                        }else{
                            $.note({
                                header  : "Ошибка сохранения!", 
                                title: "Данные не были сохранены", 
                                theme: "error",
                                duration: 5000
                            });
                        }
                    }
                }
            });
            
            return false;
        });
    }
};

$(function(){
    obViewSettings.init();
    
    var $tabs       = $("#tabs_sortable");
    var tabHeight   = $tabs.find("li").height();
    
    $tabs.sortable({
        handle      : ".tab_drag_handle",
        items       : "li:not(:has(.tab_add))",
        placeholder : "tab_placeholder",
        start       : function(event, ui){
            var w = ui.item.width();
            ui.item.width(ui.item.width() + 1);
            ui.placeholder.css({height: tabHeight, width: w});
        },
        stop: function(event, ui){
            var tabIndex = ui.item.find('a[data-toggle="tab"]').data("index");
            
            $tabContent = $(".tabs_container").find('.tab-pane[data-index="' + tabIndex + '"]');
            
            var $prev = ui.item.prev();
            
            if($prev.length){
                var tmpIndex = $prev.find('a[data-toggle="tab"]').data("index");
                $(".tabs_container").find('.tab-pane[data-index="' + tmpIndex + '"]').after($tabContent);
            }else{
                var $next = ui.item.next();
                
                var tmpIndex = $next.find('a[data-toggle="tab"]').data("index");
                $(".tabs_container").find('.tab-pane[data-index="' + tmpIndex + '"]').before($tabContent);
            }
        }
    }).disableSelection();
});