<script type="text/javascript" src="assets/js/diagrama-procesos.js"></script>
<script type="text/javascript" src="assets/js/seguimiento.js"></script>

<script type="text/javascript">
    $(document).ready(function(){
        tramiteId=<?=$tramite->id?>;
        drawFromModel(<?=$tramite->Proceso->getJSONFromModel()?>);
        drawSeguimiento(<?=json_encode($tramite->getTareasActuales()->toArray())?>,<?=json_encode($tramite->getTareasCompletadas()->toArray())?>);
    });
</script>

<div id="areaDibujo">
    <h1><?= $proceso->nombre ?></h1>
</div>