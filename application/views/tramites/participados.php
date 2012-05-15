<h1>Trámites en que ha participado</h1>

<table class="table">
    <thead>
        <tr>
            <th>Id</th>
            <th>Nombre</th>
            <th>Etapa</th>
            <th>Fecha Modificación</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tramites as $t): ?>
            <tr>
                <td><?=$t->id?></td>
                <td><?= $t->Proceso->nombre ?></td>
                <td><?=$t->getEtapaActual()?$t->getEtapaActual()->Tarea->nombre:'Ninguna' ?></td>
                <td><?= strftime('%c',mysql_to_unix($t->updated_at))?></td>
                <td><?=$t->pendiente?'Pendiente':'Completado'?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>