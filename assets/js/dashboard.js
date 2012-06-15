$(document).ready(function(){
    $("#dashboard .row-fluid").sortable({
        items: ".span4",
        handle: ".cabecera",
        revert: true
    });
    
    $("#dashboard .widget .config").click(function(){
        var widget=$(this).closest(".widget");
        $(widget).addClass('flip');
        return false;
    });
    $("#dashboard .widget .volver").click(function(){
        var widget=$(this).closest(".widget");
        $(widget).removeClass('flip');
        return false;
    });
});