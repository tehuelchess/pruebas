<script type="text/javascript" src="assets/js/diagrama-procesos.js"></script>
<script type="text/javascript" src="assets/js/seguimiento.js"></script>

<script type="text/javascript">
    $(document).ready(function(){
        tramiteId=<?= $tramite->id ?>;
        drawFromModel(<?= $tramite->Proceso->getJSONFromModel() ?>);
        drawSeguimiento(<?= json_encode($tramite->getTareasActuales()->toArray()) ?>,<?= json_encode($tramite->getTareasCompletadas()->toArray()) ?>);
    });
</script>

<ul class="breadcrumb">
    <li>
        <a href="<?= site_url('backend/seguimiento') ?>">Listado de Trámites</a> <span class="divider">/</span>
    </li>
    <li class="active"><?= $tramite->Proceso->nombre ?></li>
</ul>
<div class="row-fluid">
    <div class="span9">
        <div id="areaDibujo">
            <h1><?= $tramite->Proceso->nombre ?></h1>
        </div>  
    </div>
    <div class="span3">
        <div class="well">
            <h3>Registro de eventos</h3>
            <hr />
            <ul>
                <?php foreach($etapas as $e): ?>
                <li>
                    <h4><?=$e->Tarea->nombre?></h4>
                    <p>Estado: <?=$e->pendiente==0?'Completado':'Pendiente'?></p>
                    <p><?=$e->created_at?'Inicio: '.strftime('%c',mysql_to_unix($e->created_at)):''?></p>
                    <p><?=$e->ended_at?'Término: '.strftime('%c',mysql_to_unix($e->ended_at)):''?></p>
                    <p>Asignado a: <?=!$e->usuario_id?'Ninguno':!$e->Usuario->registrado?'No registrado':$e->Usuario->usuario?></p>
                    <p><a href="<?=site_url('backend/seguimiento/ver_etapa/'.$e->id)?>">Revisar detalle</a></p> 
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
