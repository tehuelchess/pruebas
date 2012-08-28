<link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/js/clippy/clippy.css" media="all">
<script src="<?=base_url()?>assets/js/clippy/clippy.min.js"></script>
<script type="text/javascript">
    clippy.load('Clippy', function(agent) {
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
            "¿Quieres que me oculte? Esa funcionalidad no se ha implementado."
        ];
        
        var animaciones=agent.animations();
        
        // Do anything with the loaded agent
        agent.show();
        
        setInterval(function(){
            agent.animate();
            var randomTextId=Math.floor((Math.random()*textos.length));
            agent.speak(textos[randomTextId]);
        },5000);
        
    });
</script>


<script type="text/javascript" src="<?= base_url() ?>assets/js/diagrama-procesos.js"></script>
<script type="text/javascript" src="<?= base_url() ?>assets/js/modelador-procesos.js"></script>

<script type="text/javascript">
    $(document).ready(function(){
        procesoId=<?= $proceso->id ?>;
        drawFromModel(<?= $proceso->getJSONFromModel() ?>);
    });
    
</script>

<ul class="breadcrumb">
    <li>
        <a href="<?=site_url('backend/procesos')?>">Listado de Procesos</a> <span class="divider">/</span>
    </li>
    <li class="active"><?=$proceso->nombre?></li>
</ul>

<ul class="nav nav-tabs">
    <li class="active"><a href="<?= site_url('backend/procesos/editar/' . $proceso->id) ?>">Diseñador</a></li>
    <li><a href="<?= site_url('backend/formularios/listar/' . $proceso->id) ?>">Formularios</a></li>
    <li><a href="<?= site_url('backend/documentos/listar/' . $proceso->id) ?>">Documentos</a></li>
    <li><a href="<?= site_url('backend/acciones/listar/' . $proceso->id) ?>">Acciones</a></li>
    <li><a href="<?= site_url('backend/reportes/listar/' . $proceso->id) ?>">Reportes</a></li>
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