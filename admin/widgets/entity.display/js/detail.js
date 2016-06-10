(function($){
    var detailSettings = function(){
        this.addTabModal;
        this.editTabModal;
        this.addFieldModal;
        
        this.setFields = function(arFields){
            this.arFields = arFields;
        }
        
        this.getFields = function(){
            return this.arFields;
        }
        
        this.getAvaliableFields = function(){
            var arAvailable = [];
            
            var arChosenFields = {};
            
            $("#detail_settings_container").find("input.data_item_field").each(function(){
                arChosenFields[$(this).val()] = 1;
            });
            
            var arFields = this.getFields();
            
            for(var fieldName in arFields){
                if(!arChosenFields[fieldName]){
                    arAvailable.push({
                        value: fieldName,
                        title: arFields[fieldName]
                    });
                }
            }
            
            return arAvailable;
        }
        
        this.showNewTabPopup = function(){
            this.addTabModal = new CModal({
                title: "Новая вкладка",
                width: 250,
                height: 70,
                body: AdminTools.html.text("", "", {
                    "class": "form-control",
                    "id"   : "detail_new_tab_input"
                }),
                buttons: [
                    AdminTools.html.button("Добавить", {
                        "class"     : "btn btn-info",
                        "onclick"   : "$.detailSettings.applyNewTab()"
                    }),
                    AdminTools.html.button("Отмена", {
                        "class"     : "btn btn-primary",
                        "data-mode" : "close"
                    })
                ]
            }).show();
        };
        
        this.showEditTabPopup = function(el){
            var $wrapper    = $(el).closest('a[data-toggle="tab"]');
            var index       = $wrapper.data("index");
            
            var title = $("#detail_settings_container").find('.tab-pane[data-index="' + index + '"]')
                                            .find(".data_tab_title")
                                            .val();
            
            this.editTabModal = new CModal({
                title: "Редактирование вкладки",
                width: 250,
                height: 70,
                body: AdminTools.html.text("", title, {
                    "class": "form-control",
                    "id"   : "detail_tab_input"
                }) + AdminTools.html.hidden("", index, {
                    "id" : "detail_tab_index_input"
                }),
                buttons: [
                    AdminTools.html.button("Применить", {
                        "class"     : "btn btn-info",
                        "onclick"   : "$.detailSettings.applyEditTab()"
                    }),
                    AdminTools.html.button("Отмена", {
                        "class"     : "btn btn-primary",
                        "data-mode" : "close"
                    })
                ]
            }).show();
        };
        
        this.applyNewTab = function(){
            var val = $("#detail_new_tab_input").val().trim();
    
            if(val.length){
                var tabIndex = 0;
                
                var $li = $("#detail_settings_container").find(".nav-tabs").find("li").has('a[data-index]');
                
                $li.each(function(){
                    var tmpIndex = $(this).find('a[data-index]').data("index");
                    
                    if(tmpIndex > tabIndex){
                        tabIndex = tmpIndex;
                    }
                });
                
                var nextIndex   = tabIndex + 1;
                var title       = AdminTools.html.chars(val);
                
                var liHtml = AdminTools.getTemplate("#detail_settings_tab_template", {
                    index: nextIndex,
                    title: title
                });
                
                var contentHtml = AdminTools.getTemplate("#detail_settings_tab_content_template", {
                    index: nextIndex,
                    title: title
                });
                
                $lastLi = $li.last();
                
                var container = $("#detail_settings_container");
                
                if($lastLi.length){
                    $lastLi.after(liHtml);
                    container.find('.tab-pane').last().after(contentHtml);
                }else{
                    container.find(".nav-tabs").prepend(liHtml);
                    container.find('.tab-content').prepend(contentHtml);
                }
                
                this.addTabModal.close();
            }
        };
        
        this.applyEditTab = function(){
            var val = $("#detail_tab_input").val().trim();
    
            if(val.length){
                var index       = $("#detail_tab_index_input").val();
                var container   = $("#detail_settings_container");
                var $wrapper    = container.find(".nav-tabs")
                                        .find('a[data-index="' + index + '"]');
                                                   
                $wrapper.find(".tab_title").html(val);
                
                container.find('.tab-pane[data-index="' + index + '"]')
                         .find(".data_tab_title")
                         .val(val);
                
                this.editTabModal.close();
            }
        }
        
        this.deleteTab = function(el){
            var $wrapper = $(el).closest('a[data-toggle="tab"]');
            
            var index = $wrapper.data("index");
            
            var container = $("#detail_settings_container");
            
            container.find('.tab-pane[data-index="' + index + '"]').remove();
    
            $wrapper.closest("li").remove();
            
            /*делаем последнюю вкладку активной*/
            container.find(".nav-tabs")
                     .find("li")
                     .has('a[data-index]')
                     .removeClass("active")
                     .last()
                     .addClass("active");
            
            container.find('.tab-pane[data-index]')
                     .removeClass("active")
                     .last()
                     .addClass("in active");
            /*делаем последнюю вкладку активной*/
        }
        
        this.showNewFieldPopup = function(el){
            var index       = $(el).closest(".tab-pane").data("index");
            var arAvailable = this.getAvaliableFields();

            this.addFieldModal = new CModal({
                title: "Добавление поля",
                width: 350,
                height: 350,
                body: AdminTools.html.multiselect("", arAvailable, {}, {
                    "class" : "form-control",
                    "id"    : "detail_new_field_select"
                }) + AdminTools.html.hidden("", index, {
                    "id" : "detail_field_index_input"
                }),
                buttons: [
                    AdminTools.html.button("Применить", {
                        "class"     : "btn btn-info",
                        "onclick"   : "$.detailSettings.applyAddField(this)"
                    }),
                    AdminTools.html.button("Отмена", {
                        "class"     : "btn btn-primary",
                        "data-mode" : "close"
                    })
                ]
            }).show();
        }
        
        this.applyAddField = function(el){
                var arValues        = $("#detail_new_field_select").val() || [];
                var tabIndex        = $("#detail_field_index_input").val();
                
                var $displayList    = $("#detail_settings_container").find('.tab-pane[data-index="' + tabIndex + '"]')
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
                    
                    var fieldHtml = AdminTools.getTemplate("#detail_settings_field_template", {
                        tabIndex    : tabIndex,
                        hiddenName  : "data[" + tabIndex + "][fields][" + fieldIndex + "]",
                        dragHandle  : "drag_handle_1",
                        index       : fieldIndex,
                        field       : field,
                        title       : AdminTools.html.chars(this.arFields[field])
                    });

                    $displayList.append(fieldHtml);
                    
                    fieldIndex++;
                }
                
                this.refreshDraggable();
                
                this.addFieldModal.close();
        }
        
        this.deleteField = function(el){
            $(el).closest("li").remove();
        }
        
        this.init = function(){
            this.refreshDraggable();
            
            var $tabs       = $("#detail_settings_container").find(".nav-tabs");
            var tabHeight   = $tabs.find("li").height();
            
            $tabs.sortable({
                handle      : ".tab_drag_handle",
                items       : "li:not(:last)",
                placeholder : "tab_placeholder",
                start       : function(event, ui){
                    var w = ui.item.width();
                    ui.item.width(ui.item.width() + 1);
                    ui.placeholder.css({height: tabHeight, width: w});
                },
                stop: function(event, ui){
                    var tabIndex = ui.item.find('a[data-toggle="tab"]').data("index");
                    
                    var container = $("#detail_settings_container");
                    
                    $tabContent = container.find('.tab-pane[data-index="' + tabIndex + '"]');
                    
                    var $prev = ui.item.prev();
                    
                    if($prev.length){
                        var tmpIndex = $prev.find('a[data-toggle="tab"]').data("index");
                        container.find('.tab-pane[data-index="' + tmpIndex + '"]').after($tabContent);
                    }else{
                        var $next = ui.item.next();
                        
                        var tmpIndex = $next.find('a[data-toggle="tab"]').data("index");
                        container.find('.tab-pane[data-index="' + tmpIndex + '"]').before($tabContent);
                    }
                }
            }).disableSelection();
        }
        
        this.refreshDraggable = function(){
            $("#detail_settings_container").find(".display_list").sortable({
                handle      : ".drag_handle_1",
                placeholder : "display_placeholder",
                start       : function(event, ui){
                    var h = ui.item.outerHeight(true);
                    ui.placeholder.css({height: h});
                },
            }).disableSelection();
            
            $("#detail_settings_container").find(".display_list_group").sortable({
                handle      : ".drag_handle_2",
                placeholder : "display_placeholder",
                start       : function(event, ui){
                    var h = ui.item.outerHeight(true);
                    ui.placeholder.css({height: h});
                },
            }).disableSelection();
        }
        
        this.init();
    };
    
    $.detailSettings = new detailSettings();
})(jQuery);


function saveDetailSettings(){
    var $form = $("#detail_settings_container .display_form");
    
    $.ajax({
        type    : $form.attr("method"),
        url     : $form.attr("action"),
        data    : decodeURIComponent($form.serialize()),
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
}