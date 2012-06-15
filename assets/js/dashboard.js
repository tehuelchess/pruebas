$(document).ready(function(){
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