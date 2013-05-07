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
                <a class="btn btn-primary" href="<?=site_url('backend/seguimiento/index_proceso/'.$p->id)?>">Ver trámites</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>