<h1>Trámites disponibles a iniciar</h1>

<table class="table">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($procesos as $p): ?>
            <tr>
                <td><?= $p->nombre ?></td>
                <td><a href="<?=site_url('tramites/iniciar/'.$p->id)?>" class="btn">Iniciar</a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>