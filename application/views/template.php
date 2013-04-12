<!DOCTYPE html>
<html lang="en">
    <head>
        <?php $this->load->view('head') ?>
    </head>

    <body>

        <header>
            <div class="container">
                <div class="row">
                    <div class="span2">
                        <h1 id="logo"><a href="<?= site_url() ?>"><img src="<?= Cuenta::cuentaSegunDominio() ? Cuenta::cuentaSegunDominio()->logoADesplegar : base_url('assets/img/logo.png') ?>" alt="<?= Cuenta::cuentaSegunDominio() ? Cuenta::cuentaSegunDominio()->nombre_largo : 'Simple' ?>" /></a></h1>
                    </div>
                    <div class="span3">
                        <h1><?= Cuenta::cuentaSegunDominio() ? Cuenta::cuentaSegunDominio()->nombre_largo : '' ?></h1>
                        <p><?= Cuenta::cuentaSegunDominio() ? Cuenta::cuentaSegunDominio()->mensaje : '' ?></p>
                    </div>
                    <div class="offset4 span3">
                        <ul id="userMenu" class="nav nav-pills pull-right">
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
                                                    <p style="font-size: 11px;"><a href="<?= site_url('autenticacion/olvido') ?>">¿Olvidaste tu contraseña?</a> - <a href="<?= site_url('autenticacion/registrar') ?>">¿No estas registrado?</a></p>
                                                    <p>O utilice <a href="<?= site_url('autenticacion/login_openid?redirect=' . current_url()) ?>"><img src="<?= base_url() ?>assets/img/openid_clave_unica.png" alt="OpenID"/></a></p>
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
                                        <?php if (!UsuarioSesion::usuario()->open_id): ?><li><a href="<?= site_url('cuentas/editar_password') ?>"><i class="icon-lock"></i> Cambiar contraseña</a></li><?php endif; ?>
                                        <li><a href="<?= site_url('autenticacion/logout') ?>"><i class="icon-off"></i> Cerrar sesión</a></li>
                                    </ul>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>

                </div>
            </div>
        </header>

        <div id="main">
            <div class="container">
                <div class="row">
                    <div class="span3">
                        <ul id="sideMenu" class="nav nav-list">    
                            <li class="iniciar <?= isset($sidebar) && $sidebar == 'disponibles' ? 'active' : '' ?>"><a href="<?= site_url('tramites/disponibles') ?>">Iniciar trámite</a></li>
                            <?php if (UsuarioSesion::usuario()->registrado): ?>
                                <li class="<?= isset($sidebar) && $sidebar == 'inbox' ? 'active' : '' ?>"><a href="<?= site_url('etapas/inbox') ?>">Bandeja de Entrada (<?= Doctrine::getTable('Etapa')->findPendientes(UsuarioSesion::usuario()->id, Cuenta::cuentaSegunDominio())->count() ?>)</a></li>
                                <li class="<?= isset($sidebar) && $sidebar == 'sinasignar' ? 'active' : '' ?>"><a href="<?= site_url('etapas/sinasignar') ?>">Sin asignar  (<?= Doctrine::getTable('Etapa')->findSinAsignar(UsuarioSesion::usuario()->id, Cuenta::cuentaSegunDominio())->count() ?>)</a></li>
                                <li class="<?= isset($sidebar) && $sidebar == 'participados' ? 'active' : '' ?>"><a href="<?= site_url('tramites/participados') ?>">Participados  (<?= Doctrine::getTable('Tramite')->findParticipados(UsuarioSesion::usuario()->id, Cuenta::cuentaSegunDominio())->count() ?>)</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="offset1 span8">
                        <?php if($this->session->flashdata('message')):?>
                        <div class="alert alert-success">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <?=$this->session->flashdata('message')?>
                        </div>
                        <?php endif ?>
                        <?php $this->load->view($content) ?>
                    </div>
                </div>
            </div>
        </div>

        <footer>
            <div class="area1">
                <div class="container">
                    <div class="row">
                        <div class="span2">
                            <div class="col">
                                <h3>Otros proyectos</h3>
                            </div>
                        </div>
                        <div class="offset6 span4">

                        </div>
                    </div>
                    <div class="row">
                        <div class="span4">
                            <ul>
                                <li><a href="http://www.chileatiende.cl" target="_blank">ChileAtiende</a></li>
                            </ul>
                        </div>
                        <div class="span4">
                            <ul>
                                <li><a href="http://www.chilesinpapeleo.cl" target="_blank">Chile sin papeleo</a></li>
                            </ul>
                        </div>
                        <div class="span4">
                            <ul>
                                <li><a href="http://www.gobiernoabierto.cl" target="_blank">Gobierno Abierto</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="area2">
                <div class="container">
                    <div class="row">
                        <div class="span5">
                            <div class="col">
                                <div class="media">
                                    <div class="pull-left">
                                        <img class="media-object" src="<?= base_url() ?>assets/img/ico_cc.png" alt="CC" />
                                    </div>
                                    <div class="media-body">
                                        <p class="modernizacion"><a href="http://www.modernizacion.gob.cl" target="_blank">Iniciativa de la Unidad de Modernización y Gobierno Digital</a><br/>
                                            <a class="ministerio" href="http://www.minsegpres.gob.cl" target="_blank">Ministerio Secretaría General de la Presidencia</a></p>
                                        <br />
                                        <p><a href="http://instituciones.chilesinpapeleo.cl/page/view/simple" target="_blank">Powered by SIMPLE</a></p>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="span3">
                            <div class="col"></div>
                        </div>
                        <div class="span4">
                            &nbsp;
                        </div>
                    </div>
                    <img class="footerGob" src="<?= base_url() ?>assets/img/gobierno_chile.png" />
                </div>
            </div>

        </footer>




    </body>
</html>
