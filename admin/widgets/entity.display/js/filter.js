$(function(){
    $(".filter_settings_container .available_fields_list,.filter_settings_container .chosen_fields_list").sortable({
        connectWith : ".filter_settings_container .connected_sortable",
        placeholder : "field_placeholder",
        start       : function(event, ui){
            ui.placeholder.height(ui.helper.height());
        }
    }).disableSelection();
    
    $(".filter_settings_container .filter_settings_form").on("submit", function(){
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

function saveFilterSettings(){
    var $form = $(".filter_settings_container .filter_settings_form");
    
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