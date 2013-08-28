<ul class="breadcrumb">
    <li>
        Listado de Procesos
    </li>
</ul>

<a class="btn" href="<?=site_url('backend/procesos/crear/')?>"><i class="icon-file"></i> Nuevo</a>


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
                <a class="btn btn-danger" href="<?=site_url('backend/procesos/eliminar/'.$p->id)?>" onclick="return confirm('¿Esta seguro que desea eliminar?')"><i class="icon-white icon-remove"></i> Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>