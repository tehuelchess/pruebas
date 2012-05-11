<ul class="breadcrumb">
    <li>
        <a href="<?=site_url('backend/procesos')?>">Listado de Procesos</a> <span class="divider">/</span>
    </li>
    <li class="active"><?=$proceso->nombre?></li>
</ul>

<ul class="nav nav-pills">
    <li><a href="<?=site_url('backend/procesos/editar/'.$proceso->id)?>">Diseñador</a></li>
    <li class="active"><a href="<?=site_url('backend/formularios/listar/'.$proceso->id)?>">Formularios</a></li>
</ul>

<a class="btn" href="<?=site_url('backend/formularios/crear/'.$proceso->id)?>"><i class="icon-file"></i> Nuevo</a>

<table class="table">
    <thead>
        <tr>
            <th>Formulario</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($formularios as $p): ?>
        <tr>
            <td><?=$p->nombre?></td>
            <td>
                <a href="<?=site_url('backend/formularios/editar/'.$p->id)?>" class="btn"><i class="icon-edit"></i> Editar</a>
                <a href="<?=site_url('backend/formularios/eliminar/'.$p->id)?>" class="btn" onclick="return confirm('¿Esta seguro que desea eliminar?')"><i class="icon-remove"></i> Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>