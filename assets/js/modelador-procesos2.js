$(document).ready(function(){
    var elements=new Array();
    var modo=null;
    var tipo=null;
        
    $("#areaDibujo .botonera").on("click",function(event){
        event.stopPropagation();
    });
    

    $("#areaDibujo .botonera .createBox").on("click",function(){
        $(this).addClass("disabled");
        modo="createBox";
    });
    
    $("#areaDibujo .botonera .createConnection").on("click",function(){
        $(this).addClass("disabled");
        $("#areaDibujo .box").css("cursor","crosshair")
        modo="createConnection";
        tipo=$(this).data("tipo");    
    });
    
    $("#draw").on("click",function(event){
        if(modo=="createBox"){
            var left=event.pageX - $(this).position().left;
            var top=event.pageY - $(this).position().top;
            
            
            //Buscamos un id para asignarle
            
            
            var id=1;
            for (var i in diagram.model.nodeDataArray){
                if(diagram.model.nodeDataArray[i].key>id)
                    id=1+diagram.model.nodeDataArray[i].key;
            }
            
            
            diagram.model.addNodeData({
                key: id,
                name: "Tarea",
                loc: left+" "+top
            });
            modo=null;
            $("#areaDibujo .botonera .createBox").removeClass("disabled");
            $.post(site_url+"backend/procesos/ajax_crear_tarea/"+procesoId+"/"+id,"nombre=Tarea&posx="+left+"&posy="+top);
        }
    });
    
    
    diagram.addDiagramListener("ObjectSingleClicked", function(event) {
        if(modo=="createConnection"){
            elements.push(this.id);
            if(elements.length==2){
                var c=new Object();
                c.tipo=tipo;
                c.source=elements[0];
                c.target=elements[1];
                
                //Validaciones
                if(tipo=="secuencial" && jsPlumb.getConnections({source:c.source}).length){
                    alert("Las conexiones secuenciales no pueden ir hacia mas de una tarea");
                    return;
                }
                
                
                diagram.model.addLinkData({
                    from:c.tipo,
                    to:c.source,
                    type:c.target
                })
                
 
                
                modo=null;
                elements.length=0;
                $("#areaDibujo .botonera .createConnection").removeClass("disabled");
                $.post(site_url+"backend/procesos/ajax_crear_conexion/"+procesoId,"tarea_id_origen="+c.source+"&tarea_id_destino="+c.target+"&tipo="+c.tipo);
                
            }else{
                $(this).addClass("selected");
            }
        }
    });
 
 
});