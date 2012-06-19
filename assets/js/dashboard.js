$(document).ready(function(){
    $("#dashboard .row-fluid").sortable({
        items: ".span4",
        handle: ".cabecera",
        revert: true
    });
    
    
});

function widgetConfig(button){
    var widget=$(button).closest(".widget");
    $(widget).addClass('flip');
    return false;
}

function widgetConfigOk(form){ 
    var widget=$(form).closest(".widget");
    var widgetId=$(widget).data("id");
    $(widget).removeClass('flip');
    
    //Damos tiempo para que termine la animacion
    setTimeout(function(){$(widget).load("backend/portada/widget_load/"+widgetId)},1000);
}