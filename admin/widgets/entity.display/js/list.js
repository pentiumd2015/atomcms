$(function(){
    $(".list_settings_container .available_fields_list,.list_settings_container .chosen_fields_list").sortable({
        connectWith : ".list_settings_container .connected_sortable",
        placeholder : "field_placeholder",
        start       : function(event, ui){
            ui.placeholder.height(ui.helper.height());
        }
    }).disableSelection();
    
    $(".list_settings_container .list_settings_form").on("submit", function(){
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
});

function saveListSettings(){
    var $form = $(".list_settings_container .list_settings_form");
    
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