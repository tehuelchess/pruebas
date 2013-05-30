<script>
    function editarVencimiento(etapaId){
        $("#modal").load(site_url+"backend/seguimiento/ajax_editar_vencimiento/"+etapaId);
        $("#modal").modal();
        return false;
    }
</script>

<ul class="breadcrumb">
    <li><a href="<?= site_url('backend/seguimiento/index') ?>">Seguimiento de Procesos</a></li> <span class="divider">/</span>
    <li class="active"><?= $proceso->nombre ?></li>
</ul>

<div class="row-fluid">
    <form class="form-search pull-right" method="GET" action="<?= current_url() ?>">
        <div class="input-append">
            <input name="query" value="<?= $query ?>" type="text" class="search-query" />
            <button type="submit" class="btn">Buscar</button>
        </div>
    </form>

    <div class="btn-group pull-left">
        <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
            Operaciones
            <span class="caret"></span>
        </a>
        <ul class="dropdown-menu">
            <li><a href="<?= site_url('backend/seguimiento/borrar_proceso/' . $proceso->id) ?>" onclick="if (confirm('¿Esta seguro que desea eliminar todos los tramites de este proceso?'))
                        return confirm('Atención. Esta operación no se podra deshacer y borrara todos los tramites en curso de este proceso. ¿Esta seguro que desea continuar?');
                    else
                        return false;">Borrar todo</a></li>
        </ul>
    </div>
</div>

<?= $this->pagination->create_links()?>

<table class="table">
    <thead>
        <tr>
            <th>Id</th>
            <th>Tramite</th>
            <th>Estado</th>
            <th>Etapa actual</th>
            <th>Último cambio</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tramites as $t): ?>
            <tr>
                <td><?= $t->id ?></td>
                <td><?= $t->Proceso->nombre ?></td>
                <td><?= $t->pendiente ? 'En curso' : 'Completado' ?></td>
                <td>
                    <?php
                    $etapas_array = array();
                    foreach ($t->getEtapasActuales() as $e)
                        $etapas_array[] = $e->Tarea->nombre . ($e->vencimiento_at ? ' <a href="#" onclick="return editarVencimiento('.$e->id.')" title="Cambiar fecha de vencimiento">(' . $e->getFechaVencimientoAsString() . ')</a>' : '');
                    echo implode(', ', $etapas_array);
                    ?>
                </td>
                <td><?= strftime('%c', mysql_to_unix($t->updated_at)) ?></td>
                <td>
                    <a class="btn btn-primary" href="<?= site_url('backend/seguimiento/ver/' . $t->id) ?>"><i class="icon-white icon-eye-open"></i> Seguimiento</a>
                    <a class="btn btn-danger" href="<?= site_url('backend/seguimiento/borrar_tramite/' . $t->id) ?>" onclick="return confirm('¿Esta seguro que desea borrar estre trámite?')"><i class="icon-white icon-trash"></i> Borrar</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?= $this->pagination->create_links()?>

<div id="modal" class="modal hide fade" >
    
</div>