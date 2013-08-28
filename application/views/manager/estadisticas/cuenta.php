<ul class="breadcrumb">
    <li><a href="<?=site_url('manager')?>">Inicio</a> <span class="divider">/</span></li>
    <li><a href="<?=site_url('manager/estadisticas')?>">Estadisticas</a> <span class="divider">/</span></li>
    <li><a href="<?=site_url('manager/estadisticas/cuentas')?>">Cuentas</a> <span class="divider">/</span></li>
    <li class="active"><?=$title?></li>
</ul>

<table class="table table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Tramite</th>
            <th>Estado</th>
            <th>Fecha</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tramites as $t): ?>
            <tr>
                <td><?= $t->id ?></td>
                <td><?= $t->Proceso->nombre ?></td>
                <td><?= $t->pendiente ? 'Pendiente' : 'Completado' ?></td>
                <td><?= strftime('%c', strtotime($t->updated_at)) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>