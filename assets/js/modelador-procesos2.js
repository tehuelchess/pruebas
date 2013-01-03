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
            console.log(diagram.model.nodeDataArray);
            
            
            var i=0;
            while(true){
                i++;
                var id="box_"+i;
                if($("#"+id).size()==0){   
                    break;
                }   
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
 
 
});