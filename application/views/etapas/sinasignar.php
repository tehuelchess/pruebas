<h1>Etapas sin asignar</h1>

<table class="table">
    <thead>
        <tr>
            <th>Id</th>
            <th>Nombre</th>
            <th>Etapa</th>
            <th>Modificación</th>
            <th>Vencimiento</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($etapas as $e): ?>
            <tr>
                <td><?=$e->Tramite->id?></td>
                <td><?= $e->Tramite->Proceso->nombre ?></td>
                <td><?=$e->Tarea->nombre ?></td>
                <td><?= strftime('%c',mysql_to_unix($e->updated_at))?></td>
                <td><?=$e->getFechaVencimiento()?strftime('%c',$e->getFechaVencimiento()->getTimestamp()):'N/A'?></td>
                <td><a href="<?=site_url('etapas/asignar/'.$e->id)?>" class="btn btn-primary"><i class="icon-check icon-white"></i> Asignarmelo</a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>