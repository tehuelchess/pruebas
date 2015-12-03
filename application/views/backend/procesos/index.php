<script>

function eliminarProceso(procesoId) {
    $("#modal").load(site_url + "backend/procesos/ajax_auditar_eliminar_proceso/" + procesoId);
    $("#modal").modal();
    return false;
}

</script>

<ul class="breadcrumb">
    <li>
        Listado de Procesos
    </li>
</ul>

<a class="btn btn-success" href="<?=site_url('backend/procesos/crear/')?>"><i class="icon-file icon-white"></i> Nuevo</a>
<a class="btn btn-default" href="#modalImportar" data-toggle="modal" ><i class="icon-upload icon"></i> Importar</a>


<table class="table">
    <thead>
        <tr>
            <th>Proceso</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($procesos as $p): ?>
        <tr>
            <td><?=$p->nombre?></td>
            <td>
                <a class="btn btn-primary" href="<?=site_url('backend/procesos/editar/'.$p->id)?>"><i class="icon-white icon-edit"></i> Editar</a>
                <a class="btn btn-default" href="<?=site_url('backend/procesos/exportar/'.$p->id)?>"><i class="icon icon-share"></i> Exportar</a>
                <a class="btn btn-danger" href="#" onclick="return eliminarProceso(<?=$p->id?>);"><i class="icon-white icon-remove"></i> Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div id="modalImportar" class="modal hide fade">
    <form method="POST" enctype="multipart/form-data" action="<?=site_url('backend/procesos/importar')?>">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>Importar Proceso</h3>
    </div>
    <div class="modal-body">
        <p>Cargue a continuación el archivo .simple donde exportó su proceso.</p>
        <input type="file" name="archivo" />
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Cerrar</button>
        <button type="submit" class="btn btn-primary">Importar</button>
    </div>
    </form>
</div>

<div id="modal" class="modal hide fade"></div>