<h1>Bandeja de Entrada</h1>

<table class="table">
    <thead>
        <tr>
            <th>Id</th>
            <th>Nombre</th>
            <th>Etapa pendiente</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tramites as $t): ?>
            <tr>
                <td><?=$t->id?></td>
                <td><?= $t->Proceso->nombre ?></td>
                <td><?=$t->getEtapaActual()->Tarea->nombre ?></td>
                <td><a href="<?=site_url('etapas/asignar/'.$t->getEtapaActual()->id)?>" class="btn">Asignarmelo</a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>