<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/js/clippy/clippy.css" media="all">
<script src="<?= base_url() ?>assets/js/clippy/clippy.js"></script>
<script type="text/javascript">
    var clip;
    var intervalId;
    var textos=[
        "Veo que estas escribiendo una carta. Necesitas ayuda?",
        "Veo que necesitas ayuda.",
        "Necesitas que te de una mano?",
        "Estas seguro que no necesitas ayuda?",
        "Yo soy tu amigo. Tu quieres ser mi amigo?",
        "A veces yo aparezco por ninguna razon en particular. Como ahora.",
        "Tu computador parece estar prendido.",
        "Veo que estas tratando de trabajar. Necesitas que te moleste?",
        "Veo que tu vida no tiene sentido. Necesitas consejo?",
        "Parece que estas conectado a internet.",
        "Veo que has estado usando el mouse.",
        "Tu productividad ha ido decreciendo con el tiempo. Espero que estes bien.",
        "He detectado un movimiento del mouse. Esto es normal.",
        "Veo que tu postura no es la adecuada. Por favor sientate bien.",
        "Tu monitor se encuentra 100% operacional.",
        "Si necesitas ayuda, por favor pidemela.",
        "Tu mouse esta sucio. Limpialo para un rendimiento optimo.",
        "¿Quieres que me oculte? Esa funcionalidad no se ha implementado.",
        "¿Quieres que me oculte?<br /><br /><button onclick='javascript:clip_hide()'>Si, por favor!</button><button>No, gracias</button>"
    ];
    clippy.load('Clippy', function(agent) {
        clip=agent;
        clip_start(false);
        
        //var animaciones=agent.animations();
        
        // Do anything with the loaded agent
        
        
        
       
       
       
        
    });
    
    function clip_start(vengativo){
        clip.show();
        
        
        if(!vengativo){
            intervalId=setInterval(function(){
                clip.animate();
                var randomTextId=Math.floor((Math.random()*textos.length));
                clip.speak(textos[randomTextId]);
            },10000);
        }else{
            clip.speak("Volviiiiii! Te echaba de menos.");
            setTimeout(function(){
                clip.speak("¡Por que me dejaste! ¿Guardaste tu proceso? jajajaj");
                
            },5000);
            setTimeout(function(){
                $(".box").hide();
                clip.speak("Upppps");
            },10000);
            setTimeout(function(){
                $(".box").show();
            },15000);
            
            setTimeout(function(){
                
                intervalId=setInterval(function(){
                
                    clip.animate();
                    var randomTextId=Math.floor((Math.random()*textos.length));
                    clip.speak(textos[randomTextId]);
                },5000);
            },15000);
            
        }
    }
    
    function clip_hide(){
        clip.stop();
        clip.hide();
        clearInterval(intervalId);
           
        setTimeout(function(){
            clip_start(true);
        },10000);
    }
</script>

<script type="text/javascript" src="<?= base_url() ?>assets/js/diagrama-procesos.js"></script>
<script type="text/javascript" src="<?= base_url() ?>assets/js/modelador-procesos.js"></script>

<script type="text/javascript">
    $(document).ready(function(){
        procesoId=<?= $proceso->id ?>;
        drawFromModel(<?= $proceso->getJSONFromModel() ?>);
    });
    
</script>
<style>
    #areaDibujo{
        width: <?=$proceso->width?>;
        height: <?=$proceso->height?>;
    }
</style>

<ul class="breadcrumb">
    <li>
        <a href="<?= site_url('backend/procesos') ?>">Listado de Procesos</a> <span class="divider">/</span>
    </li>
    <li class="active"><?= $proceso->nombre ?></li>
</ul>

<ul class="nav nav-tabs">
    <li class="active"><a href="<?= site_url('backend/procesos/editar/' . $proceso->id) ?>">Diseñador</a></li>
    <li><a href="<?= site_url('backend/formularios/listar/' . $proceso->id) ?>">Formularios</a></li>
    <li><a href="<?= site_url('backend/documentos/listar/' . $proceso->id) ?>">Documentos</a></li>
    <li><a href="<?= site_url('backend/acciones/listar/' . $proceso->id) ?>">Acciones</a></li>
</ul>


<div id="areaDibujo">
    <h1><?= $proceso->nombre ?></h1>
    <div class="botonera btn-toolbar">
        <div class="btn-group">
            <button class="btn createBox" title="Crear tarea"><img src="<?= base_url() ?>assets/img/tarea.png" /></button>
        </div>
        <div class="btn-group">
            <button class="btn createConnection" data-tipo="secuencial" title="Crear conexión secuencial" ><img src="<?= base_url() ?>assets/img/secuencial.gif" /></button>
            <button class="btn createConnection" data-tipo="evaluacion" title="Crear conexión por evaluación" ><img src="<?= base_url() ?>assets/img/evaluacion.gif" /></button>
            <button class="btn createConnection" data-tipo="paralelo" title="Crear conexión paralela" ><img src="<?= base_url() ?>assets/img/paralelo.gif" /></button>
            <button class="btn createConnection" data-tipo="paralelo_evaluacion" title="Crear conexión paralela con evaluación" ><img src="<?= base_url() ?>assets/img/paralelo_evaluacion.gif" /></button>
            <button class="btn createConnection" data-tipo="union" title="Crear conexión de unión" ><img src="<?= base_url() ?>assets/img/union.gif" /></button>
        </div>
    </div>
</div>
<div class="modal hide fade" id="modal">

</div>