$(document).ready(function(){
    
});

function drawSeguimiento(actuales, completadas){
    $(actuales).each(function(i,el){
        $("#areaDibujo #"+el.identificador).addClass("actual");
    });
    $(completadas).each(function(i,el){
        $("#areaDibujo #"+el.identificador).addClass("completado");
    });
    
    $('#areaDibujo .box.actual,#areaDibujo .box.completado').each(
        function(){
            var el=this;
            $.get(site_url+"backend/seguimiento/ajax_ver_etapas/"+tramiteId+"/"+el.id,function(d){
                $(el).unbind('hover').popover({
                    delay: {hide: 5000},
                    title: "Etapas ejecutadas",
                    content: d
                });
            });
        });

}