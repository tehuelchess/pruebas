<h1>Bandeja de Entrada</h1>

<table class="table">
    <thead>
        <tr>
            <th>Id</th>
            <th>Nombre</th>
            <th>Etapa</th>
            <th>Fecha Modificación</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tramites as $t): ?>
            <tr>
                <td><?=$t->id?></td>
                <td><?= $t->Proceso->nombre ?></td>
                <td><?=$t->getEtapaActual()->Tarea->nombre ?></td>
                <td><?= strftime('%c',mysql_to_unix($t->updated_at))?></td>
                <td><a href="<?=site_url('etapas/ejecutar/'.$t->getEtapaActual()->id)?>" class="btn">Realizar</a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>