<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">
        <base href="<?= base_url() ?>" />
        <title>Tramitador</title>

        <!-- Le styles -->

        <link href="assets/css/bootstrap.css" rel="stylesheet">
        <link href="assets/css/bootstrap-responsive.css" rel="stylesheet">
        <link href="assets/js/jquery.chosen/chosen.css" rel="stylesheet">
        <link href="assets/css/modelador-procesos.css" rel="stylesheet">
        <link href="assets/css/modelador-formularios.css" rel="stylesheet">


        <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

        <!-- Le fav and touch icons -->

        <script src="assets/js/jquery-ui/js/jquery-1.7.1.min.js" type="text/javascript"></script>
        <script src="assets/js/bootstrap.min.js" type="text/javascript"></script> <?php //JS base  ?>
        <script src="assets/js/jquery.chosen/chosen.jquery.min.js"></script> <?php //Soporte para selects con multiple choices  ?>
        <script src="assets/js/jquery-ui/js/jquery-ui-1.8.18.custom.min.js" type="text/javascript"></script>
        <script src="assets/js/jquery.ui.touch-punch/jquery.ui.touch-punch.min.js" type="text/javascript"></script> <?php //JS para soportar drag & drop en iPad  ?>
        <script src="assets/js/jquery.ui.livedraggable/jquery.ui.livedraggable.js" type="text/javascript"></script> <?php //JS para que evento draggable sea live  ?>
        <script src="assets/js/jquery.doubletap/jquery.doubletap.js" type="text/javascript"></script> <?php //JS para soportar dobleclick en iPad  ?>      
        <script src="http://js.pusher.com/1.11/pusher.min.js" type="text/javascript"></script> <?php //JS para recibir eventos push via websockets  ?>
        <script type="text/javascript">
            var pusher = new Pusher('<?=$this->config->item('pusher_api_key')?>');
            var channel = pusher.subscribe('modelador');
            pusher.connection.bind('connected', function() {
                socketId = pusher.connection.socket_id;
            });
        </script>
        <script src="assets/js/json-js/json2.js" type="text/javascript"></script> <?php //JS para convertir objetos a notacion JSON en multiples browsers  ?>
        <script src="assets/js/jquery.jsplumb/jquery.jsPlumb-1.3.7-all-min.js" type="text/javascript"></script> <?php //JS para conectar elementos como diagramas de flujos  ?>
        <script type="text/javascript">
            var site_url="<?= site_url() ?>";
            var base_url="<?= base_url() ?>";
        </script>
        <script src="assets/js/common.js" type="text/javascript"></script>
        <script src="assets/js/modelador-procesos.js" type="text/javascript"></script>
        <script src="assets/js/modelador-formularios.js" type="text/javascript"></script>


        <link rel="shortcut icon" href="assets/ico/favicon.ico">

        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png">
        <link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png">
    </head>
    <body>
        <div class="container-fluid">
            
            <div class="row">
                <ul class="nav nav-pills pull-right">
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Bienvenido <?=UsuarioBackendSesion::usuario()->nombre?><b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="<?=site_url('backend/autenticacion/logout')?>">Cerrar sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
            
            <ul class="nav nav-tabs">
                <li <?= $this->uri->segment(2) == 'portada' ? 'class="active"' : '' ?>><a href="<?= site_url('backend/portada') ?>">Inicio</a></li>
                <li <?= $this->uri->segment(2) == 'procesos' || $this->uri->segment(2) == 'formularios' ? 'class="active"' : '' ?>><a href="<?= site_url('backend/procesos') ?>">Modelador de Procesos</a></li>
                <li <?= $this->uri->segment(2) == 'configuracion' ? 'class="active"' : '' ?>><a href="<?= site_url('backend/configuracion') ?>">Configuración</a></li>
            </ul>
            <?php $this->load->view($content) ?>
        </div>

    </body>
</html>
