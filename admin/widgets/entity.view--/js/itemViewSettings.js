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
        
        $('.view_table').find('tr[data-type="field"]').each(function(){
            var data = $(this).data();
            
            if(data.isBase){
                arChoosenBaseFields[data.field]  = 1;
            }else{
                arChoosenExtraFields[data.field] = 1;
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
    
    this.refreshDragTable = function(){
        $('.view_table').tableDnD({
            onDragClass : "on_drag_start",
            dragHandle  : ".drag_handle_1"
        });
        
        $('.view_table_group').tableDnD({
            onDragClass : "on_drag_start",
            dragHandle  : ".drag_handle_2"
        });
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
    
    this.init = function(){
        var $this = this;
        
        this.refreshDragTable();
        
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
                var lastLi      = $(".tabs_container").find(".nav-tabs").find("li").has('a[data-index]').last();
                var lastIndex   = lastLi.find('a[data-index]').data("index");
                var nextIndex   = lastIndex + 1;
                
                var liHtml = $this.getTemplate("#tab_template", {
                    index: nextIndex,
                    title: $this.escapeHtml(val)
                });
                
                lastLi.after(liHtml);
                
                var lastContainer = $(".tabs_container").find('.tab-pane[data-index="' + lastIndex + '"]');
                
                var contentHtml = $this.getTemplate("#tab_content_template", {
                    index: nextIndex
                });
                
                lastContainer.after(contentHtml);
                
                $modal.modal("hide");
            }
        });
        /*add tab*/
        
        /*edit tab*/
        $(".tabs_container").find(".nav-tabs").on("click", ".tab_edit", function(e){
            e.preventDefault();
            
            var $modal      = $("#view_tab_edit_modal");
            var $wrapper    = $(this).closest('a[data-toggle="tab"]');
            var data        = $wrapper.data();
            
            $modal.find(".tab_edit_input").val(data.title);
            $modal.data({"index": data.index}).modal();
        });
        
        $("#view_tab_edit_modal").find(".tab_edit_apply").on("click", function(e){
            e.preventDefault();
            
            var $modal  = $("#view_tab_edit_modal");
            var index   = $modal.data("index");
            var val     = $modal.find(".tab_edit_input").val().trim();
    
            if(val.length){
                var $wrapper = $(".tabs_container").find(".nav-tabs")
                                                   .find('a[data-index="' + index + '"]');
                
                $wrapper.attr("data-title", $this.escapeHtml(val)).data("title", val);
                $wrapper.find(".tab_title").html(val);
                
                $modal.modal("hide");
            }
        });
        /*edit tab*/
        
        /*delete tab*/
        $(".tabs_container").find(".nav-tabs").on("click", ".tab_remove", function(e){
            e.preventDefault();
            
            var $wrapper = $(this).closest('a[data-toggle="tab"]');
            
            $(target).remove();
    
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
            
            var $modal      = $("#view_field_add_modal");
            var arValues    = $modal.find(".select_available_fields").val() || [];
            var tabIndex    = $modal.data("index");
            
            var $viewTable = $(".tabs_container").find('.tab-pane[data-index="' + tabIndex + '"]')
                                                 .find(".view_table > tbody");
            
            /*определяем максимальный индекс и берем следующий*/
            var fieldIndex  = 0;
             
            $viewTable.children("tr")
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
                        dragHandle  : "drag_handle_1",
                        index       : fieldIndex,
                        isBase      : 0,
                        field       : field,
                        title       : $this.escapeHtml($this.arExtraFields[field].title)
                    });
                }else if(preffix == "b_"){ //base field
                    var fieldHtml = $this.getTemplate("#field_template", {
                        tabIndex    : tabIndex,
                        dragHandle  : "drag_handle_1",
                        index       : fieldIndex,
                        isBase      : 1,
                        field       : field,
                        title       : $this.escapeHtml($this.arBaseFields[field].title)
                    });
                }
                
                $viewTable.append(fieldHtml);
            }
            
            $this.refreshDragTable();
            
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
            
            var $viewTable = $(".tabs_container").find('.tab-pane[data-index="' + tabIndex + '"]')
                                                 .find(".view_table > tbody");
            
            /*определяем максимальный индекс и берем следующий*/
            var groupIndex  = 0;
             
            $viewTable.children("tr")
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
                            dragHandle  : "drag_handle_2",
                            index       : fieldIndex,
                            isBase      : 0,
                            field       : field,
                            title       : $this.escapeHtml($this.arExtraFields[field].title)
                        });
                    }else if(preffix == "b_"){ //base field
                        fieldHtml+= $this.getTemplate("#field_template", {
                            tabIndex    : tabIndex,
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
                
                $viewTable.append(groupHtml);
                                    
                $this.refreshDragTable();
                
                $("#view_group_add_modal").modal("hide");
            }
        });
        /*add group*/
        
        /*edit group*/
        $(document).on('click', '.group_edit', function(e){
            e.preventDefault();
            
            var $modal      = $("#view_group_edit_modal");
            var index       = $(this).closest(".tab-pane").data("index");
            var $tr         = $(this).closest("tr");
            var data        = $tr.data();
            var options     = "";
    
            //Добавим поля из группы
            $tr.find(".view_table_group tr").each(function(){
                var fData = $(this).data();
                var field   = fData.field;
                
                if(fData.isBase){
                    value = "b_" + fData.field;
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
            
            $modal.find(".select_available_fields").html(options);
            $modal.find(".group_edit_value").val(data.title);
            $modal.data({index: index, group: data}).modal();
        });
        
        $(".group_edit_apply").on('click', function(e){
            e.preventDefault();
            
            var $modal      = $("#view_group_edit_modal");
            var arValues    = $modal.find(".select_available_fields").val() || [];
            var groupTitle  = $modal.find(".group_edit_value").val();
            var data        = $modal.data();
            var tabIndex    = data.index;
            var gData       = data.group;
            
            var $viewTable  = $(".tabs_container").find('.tab-pane[data-index="' + tabIndex + '"]')
                                                  .find(".view_table > tbody");
            
            var $tr = $viewTable.children('tr[data-index="' + gData.index + '"]');
            
            var fieldIndex = 0;
            
            if(groupTitle){          
                var fieldHtml = "";
                
                for(var i in arValues){
                    var field   = arValues[i];
                    var preffix = field.substr(0, 2);
                    var field   = field.substr(2);
                    
                    if(preffix == "e_"){ //extra field
                        fieldHtml+= $this.getTemplate("#field_template", {
                            tabIndex    : tabIndex,
                            dragHandle  : "drag_handle_2",
                            index       : fieldIndex,
                            isBase      : 0,
                            field       : field,
                            title       : $this.escapeHtml($this.arExtraFields[field].title)
                        });
                    }else if(preffix == "b_"){ //base field
                        fieldHtml+= $this.getTemplate("#field_template", {
                            tabIndex    : tabIndex,
                            dragHandle  : "drag_handle_2",
                            index       : fieldIndex,
                            isBase      : 1,
                            field       : field,
                            title       : $this.escapeHtml($this.arBaseFields[field].title)
                        });
                    }
                    
                    fieldIndex++;
                }
                
                $tr.attr("data-title", $this.escapeHtml(groupTitle)).data("title", groupTitle);
                $tr.find(".group_title").html(groupTitle);
                $tr.find(".view_table_group > tbody").html(fieldHtml);
                       
                $this.refreshDragTable();
                
                $modal.modal("hide");
            }
        });
        /*edit group*/
        
        /*remove group or field*/
        $(document).on("click", ".entity_view_remove", function(e){
            e.preventDefault();
            
            $(this).closest("tr").remove();
        });
        /*remove group or field*/
        
        $(".view_form").on("submit", function(){
            var data = [];
            
            var $container = $(".tabs_container");
            
            $container.find(".nav-tabs")
                      .find("li")
                      .find('a[data-index]')
                      .each(function(){
                          var d = $(this).data();
                        
                          var tab = {};
                        
                          tab.title = d.title;
                        
                          var $tabContent = $container.find('.tab-pane[data-index="' + d.index + '"]')
                                                      .find(".view_table > tbody");
                                                      
                          var $tr = $tabContent.children("tr");
                          
                          if($tr.length){
                              tab.items = [];
                              
                              $tr.each(function(){
                                  var d = $(this).data();
                                  
                                  
                                  
                                  if(d.type == "group"){
                                      var group = {
                                          title : d.title,
                                          type  : d.type
                                      };
                                      
                                      var $fieldTr = $(this).find(".view_table_group tr");
                                      
                                      if($fieldTr.length){
                                          group.fields = [];
                                          
                                          $fieldTr.each(function(){
                                              var d = $(this).data();
                                              
                                              group.fields.push({
                                                  isBase: d.isBase,
                                                  field : d.field,
                                                  type  : d.type
                                              });
                                          });
                                      }
                                      
                                      tab.items.push(group);
                                  }else{
                                      tab.items.push({
                                          isBase: d.isBase,
                                          field : d.field,
                                          type  : d.type
                                      });
                                  }
                              });
                          }
                          
                          data.push(tab);
                      });
            
            $.ajax({
                type    : "POST",
                url     : $this.params.ajaxURL,
                data    : {
                    widget      : "entity.item",
                    method      : "saveViewSettings",
                    data        : data,
                    entityItemID: $this.params.obEntityItem.entity_item_id
                },
                dataType: "json",
                success : function(r){
                    if(r && r.result == 1){
                        if(!r.hasErrors){
                         //   location.href = $this.editItemURL;
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
});