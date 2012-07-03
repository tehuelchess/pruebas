var evaluacionEndpoint=["Image",{src: base_url+"assets/img/evaluacion.gif", cssClass: "endpoint1"}];
var paraleloEndpoint=["Image",{src: base_url+"assets/img/paralelo.gif", cssClass: "endpoint1"}];
var paraleloEvaluacionEndpoint=["Image",{src: base_url+"assets/img/paralelo_evaluacion.gif", cssClass: "endpoint1"}];
var unionEndpoint=["Image",{src: base_url+"assets/img/union.gif", cssClass: "endpoint2"}];

$(document).ready(function(){
    
    jsPlumb.Defaults.PaintStyle={
        strokeStyle:"#333", 
        lineWidth:2
    };
    jsPlumb.Defaults.Endpoint="Blank";
    jsPlumb.Defaults.Connector="Flowchart";
    jsPlumb.Defaults.ConnectionOverlays = [[ "Arrow", {
        location:1, 
        width:8 ,
        length:8
    } ]];
});

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
        var endpoint1, endpoint2;
        if(c.tipo=='evaluacion')
            endpoint1=evaluacionEndpoint;
        else if(c.tipo=='paralelo')
            endpoint1=paraleloEndpoint;
        else if(c.tipo=='paralelo_evaluacion')
            endpoint1=paraleloEvaluacionEndpoint;
        else if(c.tipo=='union')
            endpoint2=unionEndpoint;
        
        var connection=jsPlumb.connect({
            source: c.source,
            target: c.target,
            anchors: ["BottomCenter", "TopCenter"],
            endpoints:[endpoint1,endpoint2],
            parameters: {"id":c.id}
        });
    });
    
    jsPlumb.draggable($("#areaDibujo .box"));
    
}
