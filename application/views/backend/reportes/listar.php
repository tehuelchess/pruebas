<script src="<?= base_url() ?>assets/js/modelador-acciones.js" type="text/javascript"></script>

<ul class="breadcrumb">
    <li>
        <a href="<?=site_url('backend/reportes')?>">Gestión</a> <span class="divider">/</span>
    </li>
    <li class="active"><?=$proceso->nombre?></li>
</ul>

<a class="btn btn-success" href="<?=site_url('backend/reportes/crear/'.$proceso->id)?>"><i class="icon-file icon-white"></i> Nuevo</a>

<table class="table">
    <thead>
        <tr>
            <th>Reporte</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($reportes as $p): ?>
        <tr>
            <td><?=$p->nombre?></td>
            <td>
            	<a href="<?=site_url('backend/reportes/ver/'.$p->id)?>" class="btn btn-info"><i class="icon-eye-open icon-white"></i> Ver</a>
                <? if(!in_array('gestion',explode(",",$rol)) ){ ?>
                    <a href="<?=site_url('backend/reportes/editar/'.$p->id)?>" class="btn btn-primary"><i class="icon-edit icon-white"></i> Editar</a>
                    <a href="<?=site_url('backend/reportes/eliminar/'.$p->id)?>" class="btn btn-danger" onclick="return confirm('¿Esta seguro que desea eliminar?')"><i class="icon-remove icon-white"></i> Eliminar</a>
                <?}?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="modal hide fade" id="modal"></div>