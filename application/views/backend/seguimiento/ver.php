<script type="text/javascript" src="<?= base_url() ?>assets/js/diagrama-procesos.js"></script>
<script type="text/javascript" src="<?= base_url() ?>assets/js/seguimiento.js"></script>

<script type="text/javascript">
    $(document).ready(function(){
        tramiteId=<?= $tramite->id ?>;
        drawFromModel(<?= $tramite->Proceso->getJSONFromModel() ?>);
        drawSeguimiento(<?= json_encode($tramite->getTareasActuales()->toArray()) ?>,<?= json_encode($tramite->getTareasCompletadas()->toArray()) ?>);
    });
</script>

<style>
    #areaDibujo{
        width: <?= $tramite->Proceso->width ?>;
        height: <?= $tramite->Proceso->height ?>;
    }
</style>

<ul class="breadcrumb">
    <li><a href="<?= site_url('backend/seguimiento/index') ?>">Seguimiento de Procesos</a></li> <span class="divider">/</span>
    <li><a href="<?= site_url('backend/seguimiento/index_proceso/'.$tramite->Proceso->id) ?>"><?=$tramite->Proceso->nombre?></a></li> <span class="divider">/</span>
    <li class="active">Trámite # <?= $tramite->id ?></li>
</ul>

<div class="well" style="position:fixed; top: 200px; right: 20px; width: 300px; height: 500px; z-index: 1000; overflow-y: scroll">
    <h3>Registro de eventos</h3>
    <hr />
    <ul>
        <?php foreach ($etapas as $e): ?>
            <li>
                <h4><?= $e->Tarea->nombre ?></h4>
                <p>Estado: <?= $e->pendiente == 0 ? 'Completado' : 'Pendiente' ?></p>
                <p><?= $e->created_at ? 'Inicio: ' . strftime('%c', mysql_to_unix($e->created_at)) : '' ?></p>
                <p><?= $e->ended_at ? 'Término: ' . strftime('%c', mysql_to_unix($e->ended_at)) : '' ?></p>
                <p>Asignado a: <?= !$e->usuario_id ? 'Ninguno' : !$e->Usuario->registrado ? 'No registrado' : '<abbr class="tt" title="'.$e->Usuario->displayInfo().'">'.$e->Usuario->displayUsername().'</abbr>' ?></p>
                <p><a href="<?= site_url('backend/seguimiento/ver_etapa/' . $e->id) ?>">Revisar detalle</a></p> 
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<div id="areaDibujo">
    <h1><?= $tramite->Proceso->nombre ?></h1>
</div>  
</div>


