
$(document).ready(function(){
    diagram = new go.Diagram("draw");
    diagram.allowVerticalScroll = false;
   diagram.allowHorizontalScroll = false;
   diagram.fixedBounds=new go.Rect(0,0,10000,10000);
    $$ = go.GraphObject.make;
    
});



function drawFromModel(model){
    //console.log(model);
    
    


      
      
      diagram.nodeTemplate =
    $$(go.Node, go.Panel.Spot,
new go.Binding("location", "loc"),
{ fromSpot: go.Spot.Bottom,  // coming out from middle-right
        toSpot: go.Spot.Top },
      // the main content:
      $$(go.Panel, go.Panel.Auto,
      
      
        $$(go.Shape,
        { figure: "RoundedRectangle", fill: "#006699" }),
        $$(go.TextBlock,{stroke: "white", margin: 5},new go.Binding("text", "name"))),
      // decorations:
      $$(go.Shape, "Circle",
        { alignment: go.Spot.BottomCenter,
          fill: "red", width: 14, height: 14,
          visible: false },
        new go.Binding("visible", "stop")),
      $$(go.Shape, "Circle",
        { alignment: go.Spot.TopCenter,
          fill: "#C7EFA2", width: 14, height: 14,
          visible: false },
        new go.Binding("visible", "start"))
    );
      
      
      diagram.linkTemplate = $$(go.Link,
      { routing: go.Link.AvoidsNodes, corner: 10, curve: go.Link.JumpOver },  // link route should avoid nodes
      $$(go.Shape, {strokeWidth: 2}),
      $$(go.Picture, { segmentIndex: 0, segmentOffset: new go.Point(12, 0)  },new go.Binding("source", "type", function(v){return v!="union"?base_url+"assets/img/"+v+".gif":""})),
      $$(go.Picture, { segmentIndex: -1, segmentOffset: new go.Point(-12, 0)  },new go.Binding("source", "type", function(v){return v=="union"?base_url+"assets/img/"+v+".gif":""})),
      $$(go.Shape, { toArrow: "Standard" }));
  
  var nodeDataArray=new Array();
  for(var i in model.elements){
      nodeDataArray.push({
          key: model.elements[i].id,
          name: model.elements[i].name,
          loc: new go.Point(parseInt(model.elements[i].left),parseInt(model.elements[i].top)),
          start: model.elements[i].start==1?true:false,
          stop: false
      });
  }
  //console.log(nodeDataArray);
  var linkDataArray = new Array();
  for(var i in model.connections){
      if(model.connections[i].target==null){
          for(var j in nodeDataArray){
              if(nodeDataArray[j].key==model.connections[i].source)
                  nodeDataArray[j].stop=true;
          }
      }else{
      linkDataArray.push({
          from: model.connections[i].source,
          to: model.connections[i].target,
          type: model.connections[i].tipo
      });
      }
  }
  
  
  
  diagram.model = new go.GraphLinksModel(nodeDataArray, linkDataArray);
  
  
      
}

