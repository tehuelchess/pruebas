
function drawFromModel(model){
    console.log(model);
    
    var diagram = new go.Diagram("draw");
    
    var $$ = go.GraphObject.make;

/*
  diagram.layout =
    $$(go.LayeredDigraphLayout,  // this will be discussed in a later section
      { columnSpacing: 5,
        setsPortSpots: false });
        */
    
    diagram.nodeTemplate = $$(go.Node, go.Panel.Auto,
      new go.Binding("location", "loc", go.Point.parse),
      { fromSpot: go.Spot.Bottom,  // coming out from middle-right
        toSpot: go.Spot.Top },
      $$(go.Shape,
        { figure: "RoundedRectangle", fill: "#006699" }),
      $$(go.TextBlock,{stroke: "white", margin: 5},new go.Binding("text", "name"))
  );
      
      diagram.linkTemplate = $$(go.Link,
      { routing: go.Link.AvoidsNodes, corner: 10 },  // link route should avoid nodes
      $$(go.Shape),
      $$(go.Picture, { source: base_url+"assets/img/evaluacion.gif", segmentIndex: 1 },new go.Binding("source", "type", function(v){return base_url+"assets/img/"+v+".gif";})),
      $$(go.Shape, { toArrow: "Standard" }));
  
  var nodeDataArray=new Array();
  for(var i in model.elements){
      nodeDataArray.push({
          key: model.elements[i].id,
          name: model.elements[i].name,
          loc: model.elements[i].left+" "+model.elements[i].top
      });
  }
  
  var linkDataArray = new Array();
  for(var i in model.connections){
      linkDataArray.push({
          from: model.connections[i].source,
          to: model.connections[i].target,
          type: model.connections[i].tipo
      });
  }
  
  diagram.addDiagramListener("ObjectDoubleClicked", function(e) {
      var part = e.subject.part;
      var id;
      if (!(part instanceof go.Link)){
          id= part.data.key;
          $('#modal').load(site_url+"backend/procesos/ajax_editar_tarea/"+procesoId+"/"+id);
        $('#modal').modal('show')
      }else{
          id=part.data.from;
          $('#modal').load(site_url+"backend/procesos/ajax_editar_conexiones/"+procesoId+"/"+id);
          $('#modal').modal('show')
          
      }
      
      
        
      
  });
  
  diagram.model = new go.GraphLinksModel(nodeDataArray, linkDataArray);
    
}

