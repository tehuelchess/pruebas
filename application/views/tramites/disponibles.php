<h1>Trámites disponibles a iniciar</h1>

<table class="table">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Cuenta</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($procesos as $p): ?>
            <tr>
                <td><?= $p->nombre ?></td>
                <td><?=$p->Cuenta->nombre?></td>
                <td>
                    <?php if($p->canUsuarioIniciarlo(UsuarioSesion::usuario()->id)):?>
                    <a href="<?=site_url('tramites/iniciar/'.$p->id)?>" class="btn btn-primary"><i class="icon-file icon-white"></i> Iniciar</a>
                    <?php else: ?>
                    <a href="<?=site_url('autenticacion/login')?>" class="btn btn-info"><i class="icon-white icon-off"></i> Autenticarse</a>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>