<!DOCTYPE html>
<html lang="en">
    <head>
        <base href="<?= base_url() ?>" />
        <meta charset="utf-8">
        <title>Tramitador - <?= $title ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <!-- Le styles -->
        <link href="assets/css/bootstrap.css" rel="stylesheet">
        <link href="assets/css/bootstrap-responsive.css" rel="stylesheet">

        <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

        <!-- Le fav and touch icons -->
        <link rel="shortcut icon" href="assets/ico/favicon.ico">
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png">
        <link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png">
    </head>

    <body>

        <div class="container">
            <div class="row">
                <ul class="nav nav-pills pull-right">
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Bienvenido <?=UsuarioSesion::usuario()->nombre?><b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="<?=site_url('autenticacion/logout')?>">Cerrar sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>

            <div class="row">
                <div class="span3">
                    <ul class="nav nav-list">    
                        <li><a href="<?= site_url('tramites/disponibles') ?>">Iniciar trámite</a></li>
                        <li class="nav-header">En curso</li>
                        <?php $conteo=Doctrine::getTable('Tramite')->countBySeccion(UsuarioSesion::usuario()->id)?>
                        <li><a href="<?= site_url('tramites/inbox') ?>">Bandeja de Entrada (<?=$conteo->inbox?>)</a></li>
                        <li><a href="<?= site_url('tramites/sinasignar') ?>">Sin asignar  (<?=$conteo->sinasignar?>)</a></li>
                        <li><a href="<?= site_url('tramites/participados') ?>">Participados  (<?=$conteo->participados?>)</a></li>
                    </ul>
                </div>
                <div class="span9">
                    <?php $this->load->view($content) ?>
                </div>
            </div>


        </div> <!-- /container -->

        <div class="modal hide fade" id="loginModal">
            <div class="modal-header">
                <a class="close" data-dismiss="modal">×</a>
                <h3>Iniciar Sesión</h3>
            </div>
            <div class="modal-body">
                <form method="post" class="ajaxForm" action="<?= site_url('autenticacion/ajax_login') ?>">
                    <div class="validacion"></div>
                    <label>Rut</label>
                    <input name="usuario" type="text" class="span3">
                    <label>Contraseña</label>
                    <input name="password" type="password" class="span3">
                </form>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-primary" onclick="javascript:$('#loginModal').find('form').submit(); return false;">Enviar</a>
                <a href="#" data-dismiss="modal" class="btn">Cerrar</a>
            </div>
        </div>

        <!-- Le javascript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="assets/js/jquery-ui/js/jquery-1.7.1.min.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
        <script src="assets/js/jquery.chosen/chosen.jquery.min.js"></script> <?php //Soporte para selects con multiple choices   ?>
        <script src="assets/js/common.js"></script>
    </body>
</html>
