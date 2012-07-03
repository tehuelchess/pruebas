<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Tramitador - <?= $title ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <!-- Le styles -->
        <link href="<?= base_url() ?>assets/css/bootstrap.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/css/bootstrap-responsive.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/css/common.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/js/bootstrap-datepicker/css/datepicker.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/js/jquery.chosen/chosen.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/js/file-uploader/fileuploader.css" rel="stylesheet">

        <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

        <!-- Le fav and touch icons -->
        <link rel="shortcut icon" href="<?= base_url() ?>assets/ico/favicon.ico">
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?= base_url() ?>assets/ico/apple-touch-icon-114-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?= base_url() ?>assets/ico/apple-touch-icon-72-precomposed.png">
        <link rel="apple-touch-icon-precomposed" href="<?= base_url() ?>assets/ico/apple-touch-icon-57-precomposed.png">
        
        <script src="<?= base_url() ?>assets/js/jquery-ui/js/jquery-1.7.2.min.js"></script>
        <script src="<?= base_url() ?>assets/js/bootstrap.min.js"></script>
        <script src="<?= base_url() ?>assets/js/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
        <script src="<?= base_url() ?>assets/js/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js"></script>
        <script src="<?= base_url() ?>assets/js/jquery.chosen/chosen.jquery.min.js"></script> <?php //Soporte para selects con multiple choices    ?>
        <script src="<?= base_url() ?>assets/js/file-uploader/fileuploader.js"></script> <?php //Soporte para subir archivos con ajax    ?>
        <script type="text/javascript">
            var site_url="<?= site_url() ?>";
            var base_url="<?= base_url() ?>";
        </script>
        <script src="<?= base_url() ?>assets/js/common.js"></script>
    </head>

    <body>

        <div class="container">
            <header class="row">
                <h1><a href="<?=site_url()?>"><img src="<?= base_url() ?>assets/img/logo.png" alt="Tramitador" /></a></h1>
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
                                            <p>O utilice <a href="<?=$this->lightopenid->authUrl()?>"><img src="<?= base_url() ?>assets/img/openid.png" alt="OpenID"/></a></p>
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
                                <li><a href="<?= site_url('autenticacion/logout') ?>">Cerrar sesión</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </header>

            <div class="row">
                <div class="span3">
                    <ul class="nav nav-list">    
                        <li class="<?= $this->uri->segment(2) == 'disponibles' ? 'active' : '' ?>"><a href="<?= site_url('tramites/disponibles') ?>">Iniciar trámite</a></li>
                        <?php if (UsuarioSesion::usuario()->registrado): ?>
                            <li class="nav-header">En curso</li>
                            <li class="<?= $this->uri->segment(2) == 'inbox' ? 'active' : '' ?>"><a href="<?= site_url('etapas/inbox') ?>">Bandeja de Entrada (<?= Doctrine::getTable('Etapa')->findPendientes(UsuarioSesion::usuario()->id)->count() ?>)</a></li>
                            <li class="<?= $this->uri->segment(2) == 'sinasignar' ? 'active' : '' ?>"><a href="<?= site_url('etapas/sinasignar') ?>">Sin asignar  (<?= Doctrine::getTable('Etapa')->findSinAsignar(UsuarioSesion::usuario()->id)->count() ?>)</a></li>
                            <li class="<?= $this->uri->segment(2) == 'participados' ? 'active' : '' ?>"><a href="<?= site_url('tramites/participados') ?>">Participados  (<?= Doctrine::getTable('Tramite')->findParticipados(UsuarioSesion::usuario()->id)->count() ?>)</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="span9">
                    <?php $this->load->view($content) ?>
                </div>
            </div>


        </div> <!-- /container -->

    </body>
</html>
