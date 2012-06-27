<ul class="breadcrumb">
    <li class="active">Listado de Trámites</li>
</ul>

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
                <td><?= $t->pendiente?'En curso':'Completado' ?></td>
                <td>
                    <?php
                    $etapas_array = array();
                    foreach ($t->getEtapasActuales() as $e)
                        $etapas_array[] = $e->Tarea->nombre;
                    echo implode(', ', $etapas_array);
                    ?>
                </td>
                <td><?= strftime('%c', mysql_to_unix($t->updated_at)) ?></td>
                <td><a class="btn" href="<?= site_url('backend/seguimiento/ver/' . $t->id) ?>">Seguimiento</a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>