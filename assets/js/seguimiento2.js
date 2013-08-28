$(document).ready(function(){
    
    });

function drawSeguimiento(actuales, completadas){
    $(completadas).each(function(i,el){
        var nodedata=diagram.model.findNodeDataForKey(el.identificador);
        diagram.model.setDataProperty(nodedata,"status","completed");
        
        //var node=diagram.findNodeForKey(el.identificador);
        //console.log(node.location.x);
    });
    $(actuales).each(function(i,el){
        var nodedata=diagram.model.findNodeDataForKey(el.identificador);
        diagram.model.setDataProperty(nodedata,"status","current");  
    });
    
    /*
    $('#draw .box.actual,#draw .box.completado').each(
        function(){
            var el=this;
            $.get(site_url+"backend/seguimiento/ajax_ver_etapas/"+tramiteId+"/"+el.id,function(d){
                $(el).unbind('hover').popover({
                    html: true,
                    title: "Etapas ejecutadas",
                    content: d
                });
            });
        });
        */

}