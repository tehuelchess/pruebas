$(document).ready(function(){
    
    /*
    $("#areaDibujo .box").each(function(i,e){
        alert(1);
        jsPlumb.draggable(e);
    });
    */
    
    jsPlumb.Defaults.PaintStyle={
        strokeStyle:"#333", 
        lineWidth:4
    };
    jsPlumb.Defaults.Endpoint="Blank";
    jsPlumb.Defaults.Anchor=["BottomCenter", "TopCenter"];
    jsPlumb.Defaults.Connector="Flowchart";
    jsPlumb.Defaults.ConnectionOverlays = [[ "Arrow", {
        location:1, 
        width:16 ,
        length:16
    } ]]; 
    

    /*
    jsPlumb.connect({
        source: $("#div1"),
        target: $("#div2"),
        overlays:[ ["Arrow", {
            location:1, 
            width:20, 
            length:20
        } ]]
    });
    */

    
    var modo=null;
    var elements=new Array();
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
        
    });

    $("#areaDibujo").on("click",function(event){
        if(modo=="createBox"){
            var left=event.pageX - $(this).position().left;
            var top=event.pageY - $(this).position().top;
            var id=0;
            while(true){
                id++;
                if($("#"+id).size()==0){   
                    break;
                }   
            }
            $(this).append("<div id='"+id+"' class='box' style='top: "+top+"px; left: "+left+"px;'>Tarea</div>");
            jsPlumb.draggable($("#areaDibujo .box"));
            modo=null;
            $("#areaDibujo .botonera .createBox").removeClass("disabled");
            updateModel();
        }
    });
    $("#areaDibujo").on("click",".box",function(event){
        event.stopPropagation();
        if(modo=="createConnection"){
            elements.push(this.id);
            if(elements.length==2){
                jsPlumb.connect({
                    source: elements[0],
                    target: elements[1]
                });
                
                modo=null;
                elements.length=0;
                $("#areaDibujo .botonera .createConnection").removeClass("disabled");
                $("#areaDibujo .box").css("cursor","move")
                updateModel();
            }
        }
    });


    $(document).on("dblclick doubletap","#areaDibujo .box",function(event){
        var id=$(event.target).attr("id");
        //$('#modalEditarTarea form input[name=id]').val(id);
        //$('#modalEditarTarea form input[name=nombre]').val("");
        $('#modal').load(site_url+"backend/procesos/ajax_editar_tarea/"+procesoId+"/"+id);
        $('#modal').modal('show')
    });
    
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

/*    
    $("#modalEditarTarea form").submit(function(){
        var id=$(this).find("input[name=id]").val();
        var nombre=$(this).find("input[name=nombre]").val();
        $("#"+id).text(nombre);
        $('#modalEditarTarea').modal('hide');
        updateModel();
        return false;
    });
    */
    

});

function updateModel(){
    var model=new Object();
    model.nombre=$("#areaDibujo h1").text();
    model.elements=new Array();
    model.connections=new Array();
    
    $("#areaDibujo .box").each(function(i,e){
        var tmp=new Object();
        tmp.id=e.id;
        tmp.name=$(e).text();
        tmp.left=$(e).position().left;
        tmp.top=$(e).position().top;
        model.elements.push(tmp);
    });
        
    var connections=jsPlumb.getConnections();
    for(var i in connections){
        var tmp=new Object();
        tmp.id=connections[i].id;
        tmp.source=connections[i].sourceId;
        tmp.target=connections[i].targetId;
        model.connections.push(tmp);
    }
    
    json=JSON.stringify(model);
    
    $.post(site_url+"backend/procesos/ajax_editar_modelo/"+procesoId,"modelo="+json+"&socket_id_emisor="+socketId);
}

function drawFromModel(model){
    //Modificamos el titulo
    $("#areaDibujo h1").text(model.nombre);
    
    //limpiamos el canvas
    jsPlumb.reset();
    $("#areaDibujo .box").remove();
    
    //Creamos los elementos
    $(model.elements).each(function(i,e){
        $("#areaDibujo").append("<div id='"+e.id+"' class='box' style='top: "+e.top+"px; left: "+e.left+"px;'>"+e.name+(e.start==1?'<div class="inicial"></div>':'')+(e.stop==1?'<div class="final"></div>':'')+"</div>");
    });
    
    //Creamos las conexiones
    $(model.connections).each(function(i,c){
        var connection=jsPlumb.connect({
            source: c.source,
            target: c.target
        });
        connection.id=c.id;
    });
    
    jsPlumb.draggable($("#areaDibujo .box"));
    
}
