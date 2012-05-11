<h1>Todos sus trámites</h1>

<table class="table">
    <thead>
        <tr>
            <th>Id</th>
            <th>Nombre</th>
            <th>Etapa pendiente</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tramites as $t): ?>
            <tr>
                <td><?=$t->id?></td>
                <td><?= $t->Proceso->nombre ?></td>
                <td><?=$t->getEtapaActual()->Tarea->nombre ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>