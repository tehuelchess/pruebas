<script src="<?= base_url() ?>assets/js/modelador-acciones.js" type="text/javascript"></script>

<ul class="breadcrumb">
    <li>
        <a href="<?=site_url('backend/reportes')?>">Gestión</a> <span class="divider">/</span>
    </li>
    <li class="active"><?=$proceso->nombre?></li>
</ul>

<a class="btn" href="<?=site_url('backend/reportes/crear/'.$proceso->id)?>"><i class="icon-file"></i> Nuevo</a>

<table class="table">
    <thead>
        <tr>
            <th>Reporte</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($reportes as $p): ?>
        <tr>
            <td><?=$p->nombre?></td>
            <td>
                <a href="<?=site_url('backend/reportes/ver/'.$p->id)?>" class="btn"><i class="icon-eye-open"></i> Ver</a>
                <a href="<?=site_url('backend/reportes/editar/'.$p->id)?>" class="btn"><i class="icon-edit"></i> Editar</a>
                <a href="<?=site_url('backend/reportes/eliminar/'.$p->id)?>" class="btn" onclick="return confirm('¿Esta seguro que desea eliminar?')"><i class="icon-remove"></i> Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="modal hide fade" id="modal"></div>