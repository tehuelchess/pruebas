$(document).ready(function(){
    
});

function drawSeguimiento(actuales, completadas){  
    $(completadas).each(function(i,el){
        $("#areaDibujo #"+el.identificador).addClass("completado");
    });
    $(actuales).each(function(i,el){
        $("#areaDibujo #"+el.identificador).removeClass("completado");
        $("#areaDibujo #"+el.identificador).addClass("actual");
    });
    
    $('#areaDibujo .box.actual,#areaDibujo .box.completado').each(
        function(){
            var el=this;
            $.get(site_url+"backend/seguimiento/ajax_ver_etapas/"+tramiteId+"/"+el.id,function(d){
                $(el).unbind('hover').popover({
                    title: "Etapas ejecutadas",
                    content: d
                });
            });
        });

}