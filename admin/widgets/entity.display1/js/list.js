$(function(){
    $(".available_fields_list,.chosen_fields_list").sortable({
        connectWith : ".connected_sortable",
        placeholder : "field_placeholder",
        start       : function(event, ui){
            ui.placeholder.height(ui.helper.height());
        }
    }).disableSelection();
    
    $(".view_form").on("submit", function(){
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