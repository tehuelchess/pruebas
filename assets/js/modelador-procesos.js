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
        $( "#areaDibujo .box" ).draggable({ disabled: true });
        $("#areaDibujo .box").css("cursor","crosshair")
       modo="createConnection";
       tipo=$(this).data("tipo");    
    });

    $("#areaDibujo").on("click",function(event){
        if(modo=="createBox"){
            var left=event.pageX - $(this).position().left;
            var top=event.pageY - $(this).position().top;
            
            //Buscamos un id para asignarle
            var i=0;
            while(true){
                i++;
                var id="box_"+i;
                if($("#"+id).size()==0){   
                    break;
                }   
            }
            
            $(this).append("<div id='"+id+"' class='box' style='top: "+top+"px; left: "+left+"px;'>Tarea</div>");
            jsPlumb.draggable($("#areaDibujo .box"));
            modo=null;
            $("#areaDibujo .botonera .createBox").removeClass("disabled");
            $.post(site_url+"backend/procesos/ajax_crear_tarea/"+procesoId+"/"+id,"nombre=Tarea&posx="+left+"&posy="+top);
        }
    });
    $("#areaDibujo").on("click",".box",function(event){
        event.stopPropagation();
        if(modo=="createConnection"){
            elements.push(this.id);
            if(elements.length==2){
                var endpoint1,endpoint2;
                if(tipo=='evaluacion')
                    endpoint1=evaluacionEndpoint;
                else if(tipo=='paralelo')
                    endpoint1=paraleloEndpoint;
                else if(tipo=='paralelo_evaluacion')
                    endpoint1=paraleloEvaluacionEndpoint;
                else if(tipo=='union')
                    endpoint2=unionEndpoint;
                
                //Buscamos un id para asignarle
                var i=0;
                while(true){
                    i++;
                    id="conn_"+i;
                    var connections=jsPlumb.getConnections();
                    var existe=false;
                    for (var j in connections){
                        if(connections[j].getParameter("id")==id){
                            existe=true;
                            break;
                        }
                    }
                    if(!existe)
                        break;
                }
                
                var conn=jsPlumb.connect({
                    source: elements[0],
                    target: elements[1],
                    anchors: ["BottomCenter", "TopCenter"],
                    endpoints: [endpoint1,endpoint2],
                    parameters: {"id": id}
                });
                
                modo=null;
                elements.length=0;
                $("#areaDibujo .botonera .createConnection").removeClass("disabled");
                $("#areaDibujo .box").removeClass("selected");
                $( "#areaDibujo .box" ).draggable({ disabled: false });
                $("#areaDibujo .box").css("cursor","move")
                $.post(site_url+"backend/procesos/ajax_crear_conexion/"+procesoId+"/"+conn.getParameter("id"),"tarea_id_origen="+conn.sourceId+"&tarea_id_destino="+conn.targetId+"&tipo="+tipo);
                
            }else{
                $(this).addClass("selected");
            }
        }
    });

    //Asigno los eventos a los boxes tareas
    $(document).on("dblclick doubletap","#areaDibujo .box",function(event){
        var id=$(event.target).attr("id");
        $('#modal').load(site_url+"backend/procesos/ajax_editar_tarea/"+procesoId+"/"+id);
        $('#modal').modal('show')
    });
    
    //Asigno los eventos a los conectores
    $(document).on("dblclick doubletap","#areaDibujo ._jsPlumb_connector",function(event){
        window.getSelection().removeAllRanges() //Previene bug de firefox que selecciona el texto de toda la pantalla.
        var conectorSeleccionado=$(event.target).closest("._jsPlumb_connector").get(0);
        var connections=jsPlumb.getConnections();
        $(connections).each(function(i,connection){
            if(connection.canvas==conectorSeleccionado){
                var id=connection.getParameter("id");
                $('#modal').load(site_url+"backend/procesos/ajax_editar_conexion/"+procesoId+"/"+id);
                $('#modal').modal('show')
            }
        });
    });
    
    //Asigno el evento para editar el proceso al hacerle click al titulo
    $(document).on("dblclick doubletap","#areaDibujo h1",function(event){
        $('#modal').load(site_url+"backend/procesos/ajax_editar/"+procesoId);
        $('#modal').modal('show')
    });

    
    $( "#areaDibujo .box" ).liveDraggable({
        stop: updateModel
    });
    
    channel.bind('updateModel', function(data) {
        drawFromModel(JSON.parse(data.modelo));
    });
    

});

function updateModel(){
    var model=new Object();
    //model.nombre=$("#areaDibujo h1").text();
    model.elements=new Array();
    //model.connections=new Array();
    
    $("#areaDibujo .box").each(function(i,e){
        var tmp=new Object();
        tmp.id=e.id;
        //tmp.name=$(e).text();
        tmp.left=$(e).position().left;
        tmp.top=$(e).position().top;
        model.elements.push(tmp);
    });
    
    /*
    var connections=jsPlumb.getConnections();
    for(var i in connections){
        var tmp=new Object();
        tmp.id=connections[i].id;
        tmp.source=connections[i].sourceId;
        tmp.target=connections[i].targetId;
        model.connections.push(tmp);
    }
    */
    
    json=JSON.stringify(model);
    
    $.post(site_url+"backend/procesos/ajax_editar_modelo/"+procesoId,"modelo="+json+"&socket_id_emisor="+socketId);
}
