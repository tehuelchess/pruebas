<script src="<?= base_url() ?>assets/js/modelador-acciones.js" type="text/javascript"></script>

<ul class="breadcrumb">
    <li>
        <a href="<?=site_url('backend/procesos')?>">Listado de Procesos</a> <span class="divider">/</span>
    </li>
    <li class="active"><?=$proceso->nombre?></li>
</ul>

<ul class="nav nav-tabs">
    <li><a href="<?=site_url('backend/procesos/editar/'.$proceso->id)?>">Diseñador</a></li>
    <li><a href="<?=site_url('backend/formularios/listar/'.$proceso->id)?>">Formularios</a></li>
    <li class="active"><a href="<?= site_url('backend/documentos/listar/' . $proceso->id) ?>">Documentos</a></li>
    <li><a href="<?=site_url('backend/acciones/listar/'.$proceso->id)?>">Acciones</a></li>
    <li><a href="<?=site_url('backend/reportes/listar/'.$proceso->id)?>">Reportes</a></li>
</ul>

<a class="btn" href="<?=site_url('backend/documentos/crear/'.$proceso->id)?>"><i class="icon-file"></i> Nuevo</a>

<table class="table">
    <thead>
        <tr>
            <th>Documento</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($documentos as $p): ?>
        <tr>
            <td><?=$p->nombre?></td>
            <td>
                <a href="<?=site_url('backend/documentos/editar/'.$p->id)?>" class="btn btn-primary"><i class="icon-white icon-edit"></i> Editar</a>
                <a href="<?=site_url('backend/documentos/previsualizar/'.$p->id)?>" class="btn btn-info"><i class="icon-white icon-zoom-in"></i> Previsualizar</a>
                <a href="<?=site_url('backend/documentos/eliminar/'.$p->id)?>" class="btn btn-danger" onclick="return confirm('¿Esta seguro que desea eliminar?')"><i class="icon-white icon-trash"></i> Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="modal hide fade" id="modal"></div>