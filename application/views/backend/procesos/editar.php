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

<ul class="nav nav-pills">
    <li class="active"><a href="<?= site_url('backend/procesos/editar/' . $proceso->id) ?>">Diseñador</a></li>
    <li><a href="<?= site_url('backend/formularios/listar/' . $proceso->id) ?>">Formularios</a></li>
    <li><a href="<?= site_url('backend/acciones/listar/' . $proceso->id) ?>">Acciones</a></li>
</ul>


<div id="areaDibujo">
    <h1><?= $proceso->nombre ?></h1>
    <div class="botonera btn-toolbar">
        <div class="btn-group">
            <button class="btn createBox" title="Crear tarea"><img src="assets/img/tarea.png" /></button>
        </div>
        <div class="btn-group">
            <button class="btn createConnection" data-tipo="secuencial" title="Crear conexión secuencial" ><img src="assets/img/secuencial.gif" /></button>
            <button class="btn createConnection" data-tipo="evaluacion" title="Crear conexión por evaluación" ><img src="assets/img/evaluacion.gif" /></button>
            <button class="btn createConnection" data-tipo="paralelo" title="Crear conexión paralela" ><img src="assets/img/paralelo.gif" /></button>
            <button class="btn createConnection" data-tipo="paralelo_evaluacion" title="Crear conexión paralela con evaluación" ><img src="assets/img/paralelo_evaluacion.gif" /></button>
        </div>
    </div>
</div>
<div class="modal hide fade" id="modal">

</div>