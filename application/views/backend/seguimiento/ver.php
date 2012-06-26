<script type="text/javascript" src="assets/js/diagrama-procesos.js"></script>
<script type="text/javascript" src="assets/js/seguimiento.js"></script>

<script type="text/javascript">
    $(document).ready(function(){
        tramiteId=<?=$tramite->id?>;
        drawFromModel(<?=$tramite->Proceso->getJSONFromModel()?>);
        drawSeguimiento(<?=json_encode($tramite->getTareasActuales()->toArray())?>,<?=json_encode($tramite->getTareasCompletadas()->toArray())?>);
    });
</script>

<ul class="breadcrumb">
    <li>
        <a href="<?=site_url('backend/seguimiento')?>">Listado de Trámites</a> <span class="divider">/</span>
    </li>
    <li class="active"><?=$tramite->Proceso->nombre?></li>
</ul>

<div id="areaDibujo">
    <h1><?= $tramite->Proceso->nombre ?></h1>
</div>