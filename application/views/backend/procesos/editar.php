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
    <div class="botonera btn-group">
        <button class="btn createBox" title="Crear tarea">Tarea</button>
        <button class="btn createConnection" data-tipo="secuencial" title="Crear conexión" onclick="createConnection(this,'secuencial')">Conector Secuencial</button>
        <button class="btn createConnection" data-tipo="evaluacion" title="Crear conexión" >Conector Evaluación</button>
    </div>
</div>
<div class="modal hide fade" id="modal">

</div>