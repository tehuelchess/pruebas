<h2>Trámites disponibles a iniciar</h2>

<?php if (count($procesos) > 0): ?>

<table id="mainTable" class="table">
    <thead>
        <tr>
            <th><a href="<?=current_url().'?orderby=nombre&direction='.($direction=='asc'?'desc':'asc')?>">Nombre</a></th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($procesos as $p): ?>
            <tr>
                <td class="name">
                    <?php if($p->canUsuarioIniciarlo(UsuarioSesion::usuario()->id)):?>
                    <a class="preventDoubleRequest" href="<?=site_url('tramites/iniciar/'.$p->id)?>"><?= $p->nombre ?></a>
                    <?php else: ?>
                        <?php if($p->getTareaInicial()->acceso_modo=='claveunica'):?>
                        <a href="<?=site_url('autenticacion/login_openid')?>?redirect=<?=current_url()?>"><?= $p->nombre ?></a>
                        <?php else:?>
                        <a href="<?=site_url('autenticacion/login')?>"><?= $p->nombre ?></a>
                        <?php endif ?>
                    <?php endif ?>
                </td>
                <td class="actions">
                    <?php if($p->canUsuarioIniciarlo(UsuarioSesion::usuario()->id)):?>
                    <a href="<?=site_url('tramites/iniciar/'.$p->id)?>" class="btn btn-primary preventDoubleRequest"><i class="icon-file icon-white"></i> Iniciar</a>
                    <?php else: ?>
                        <?php if($p->getTareaInicial()->acceso_modo=='claveunica'):?>
                        <a href="<?=site_url('autenticacion/login_openid')?>?redirect=<?=current_url()?>"><img src="<?=base_url('assets/img/claveunica-medium.png')?>" alt="ClaveUnica" style="max-width: none;" /></a>
                        <?php else:?>
                        <a href="<?=site_url('autenticacion/login')?>" class="btn btn-primary"><i class="icon-white icon-off"></i> Autenticarse</a>
                        <?php endif ?>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php else: ?>
<p>No hay trámites disponibles para ser iniciados.</p>
<?php endif; ?>
