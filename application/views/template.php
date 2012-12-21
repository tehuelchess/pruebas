<!DOCTYPE html>
<html lang="en">
    <head>
        <?php $this->load->view('head')?>
    </head>

    <body>

        <div class="container">
            <header class="row">
                <div class="span12">
                <h1><a href="<?=site_url()?>"><img src="<?=Cuenta::cuentaSegunDominio()?Cuenta::cuentaSegunDominio()->logoADesplegar:base_url('assets/img/logo.png') ?>" alt="<?=Cuenta::cuentaSegunDominio()?Cuenta::cuentaSegunDominio()->nombre_largo:'Simple'?>" /></a></h1>
                <ul id="userMenu" class="nav nav-pills">
                    <?php if (!UsuarioSesion::usuario()->registrado): ?>
                        <li class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#">Iniciar sesión<b class="caret"></b></a>
                            <ul class="dropdown-menu pull-right">
                                <li style="margin: 20px;">
                                    <form method="post" class="ajaxForm" action="<?= site_url('autenticacion/login_form') ?>">        
                                        <fieldset>
                                            <div class="validacion"></div>
                                            <input type="hidden" name="redirect" value="<?= current_url() ?>" />
                                            <label>Usuario</label>
                                            <input name="usuario" type="text" class="input-xlarge">
                                            <label>Contraseña</label>
                                            <input name="password" type="password" class="input-xlarge">
                                            <p>¿No esta registrado? <a href="<?= site_url('autenticacion/registrar') ?>">Regístrese acá</a></p>
                                            <p>O utilice <a href="<?=site_url('autenticacion/login_openid?redirect='.current_url())?>"><img src="<?= base_url() ?>assets/img/openid_clave_unica.png" alt="OpenID"/></a></p>
                                            <button class="btn btn-primary pull-right" type="submit">Ingresar</button>
                                        </fieldset>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#">Bienvenido <?= UsuarioSesion::usuario()->displayName() ?><b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a href="<?= site_url('cuentas/editar') ?>"><i class="icon-user"></i> Mi cuenta</a></li>
                                <?php if(!UsuarioSesion::usuario()->open_id):?><li><a href="<?= site_url('cuentas/editar_password') ?>"><i class="icon-lock"></i> Cambiar contraseña</a></li><?php endif;?>
                                <li><a href="<?= site_url('autenticacion/logout') ?>"><i class="icon-off"></i> Cerrar sesión</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
                </div>
            </header>

            <div class="row">
                <div class="span3">
                    <ul class="nav nav-list">    
                        <li class="<?= $this->uri->segment(2) == 'disponibles' ? 'active' : '' ?>"><a href="<?= site_url('tramites/disponibles') ?>">Iniciar trámite</a></li>
                        <?php if (UsuarioSesion::usuario()->registrado): ?>
                            <li class="nav-header">En curso</li>
                            <li class="<?= $this->uri->segment(2) == 'inbox' ? 'active' : '' ?>"><a href="<?= site_url('etapas/inbox') ?>">Bandeja de Entrada (<?= Doctrine::getTable('Etapa')->findPendientes(UsuarioSesion::usuario()->id, Cuenta::cuentaSegunDominio())->count() ?>)</a></li>
                            <li class="<?= $this->uri->segment(2) == 'sinasignar' ? 'active' : '' ?>"><a href="<?= site_url('etapas/sinasignar') ?>">Sin asignar  (<?= Doctrine::getTable('Etapa')->findSinAsignar(UsuarioSesion::usuario()->id,Cuenta::cuentaSegunDominio())->count() ?>)</a></li>
                            <li class="<?= $this->uri->segment(2) == 'participados' ? 'active' : '' ?>"><a href="<?= site_url('tramites/participados') ?>">Participados  (<?= Doctrine::getTable('Tramite')->findParticipados(UsuarioSesion::usuario()->id,Cuenta::cuentaSegunDominio())->count() ?>)</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="span9">
                    <?php $this->load->view($content) ?>
                </div>
            </div>
            
            <footer class="row">
                <div class="span12">
                    <p style="text-align: center;"><a class="label label-info" href="http://instituciones.chilesinpapeleo.cl/page/view/simple">Powered by SIMPLE</a></p>
                </div>
            </footer>


        </div> <!-- /container -->

    </body>
</html>
