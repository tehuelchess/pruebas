<ul class="breadcrumb">
    <li>
        Seguimiento de Procesos
    </li>
</ul>



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
                <a class="btn btn-primary" href="<?=site_url('backend/seguimiento/index_proceso/'.$p->id)?>"><i class="icon-eye-open icon-white"></i> Ver seguimiento</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>