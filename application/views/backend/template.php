<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="widpth=device-width, initial-scale=1.0">
        <title>Tramitador</title>

        <!-- Le styles -->

        <link href="<?= base_url() ?>assets/css/bootstrap.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/css/bootstrap-responsive.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/css/common.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/js/bootstrap-datepicker/css/datepicker.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/js/jquery.chosen/chosen.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/js/file-uploader/fileuploader.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/css/diagrama-procesos.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/css/modelador-formularios.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/css/dashboard.css" rel="stylesheet">


        <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

        <!-- Le fav and touch icons -->

        <script src="<?= base_url() ?>assets/js/jquery-ui/js/jquery-1.7.2.min.js" type="text/javascript"></script>
        <script src="<?= base_url() ?>assets/js/bootstrap.min.js" type="text/javascript"></script> <?php //JS base    ?>
        <script src="<?= base_url() ?>assets/js/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
        <script src="<?= base_url() ?>assets/js/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js"></script>
        <script src="<?= base_url() ?>assets/js/bootstrap-typeahead-multiple/bootstrap-typeahead-multiple.js" type="text/javascript"></script> <?php //JS typeahead modificado para multiples items    ?>
        <script src="<?= base_url() ?>assets/js/jquery.chosen/chosen.jquery.min.js"></script> <?php //Soporte para selects con multiple choices    ?>
        <script src="<?= base_url() ?>assets/js/file-uploader/fileuploader.js"></script> <?php //Soporte para subir archivos con ajax    ?>
        <script src="<?= base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.8.21.custom.min.js" type="text/javascript"></script>
        <script src="<?= base_url() ?>assets/js/jquery.ui.touch-punch/jquery.ui.touch-punch.min.js" type="text/javascript"></script> <?php //JS para soportar drag & drop en iPad    ?>
        <script src="<?= base_url() ?>assets/js/jquery.ui.livedraggable/jquery.ui.livedraggable.js" type="text/javascript"></script> <?php //JS para que evento draggable sea live    ?>
        <script src="<?= base_url() ?>assets/js/jquery.doubletap/jquery.doubletap.js" type="text/javascript"></script> <?php //JS para soportar dobleclick en iPad    ?>      
        <script src="http://js.pusher.com/1.11/pusher.min.js" type="text/javascript"></script> <?php //JS para recibir eventos push via websockets    ?>
        <script src="<?= base_url() ?>assets/js/json-js/json2.js" type="text/javascript"></script> <?php //JS para convertir objetos a notacion JSON en multiples browsers    ?>
        <script src="<?= base_url() ?>assets/js/jquery.jsplumb/jquery.jsPlumb-1.3.16-all-min.js" type="text/javascript"></script> <?php //JS para conectar elementos como diagramas de flujos    ?>
        <script src="<?= base_url() ?>assets/js/highcharts/highcharts.js" type="text/javascript"></script> <?php //JS para hacer graficos    ?>
        <script type="text/javascript">
            var site_url="<?= site_url() ?>";
            var base_url="<?= base_url() ?>";
        </script>
        <script src="<?= base_url() ?>assets/js/common.js" type="text/javascript"></script>


    </head>
    <body>
        <div class="container-fluid">

            <header class="row-fluid">
                <div class="span4">
                <h1><a href="<?=site_url('backend/portada')?>"><img src="<?= base_url() ?>assets/img/logo.png" alt="Tramitador" /></a></h1>
                </div>
                <div class="span8">
                    <div class="row-fluid">
                        <div class="span12">
                <ul id="userMenu" class="nav nav-pills">
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Bienvenido <?= UsuarioBackendSesion::usuario()->usuario ?><b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="<?= site_url('backend/configuracion/cuenta') ?>">Mi Cuenta</a></li>
                            <li><a href="<?= site_url('backend/autenticacion/logout') ?>">Cerrar sesión</a></li>
                        </ul>
                    </li>
                </ul>
                        </div>
                    </div>
                    <div class="row-fluid">
                        <div class="span12">
                <ul id="menu" class="nav nav-pills">
                    <?php if(UsuarioBackendSesion::usuario()->rol=='super' || UsuarioBackendSesion::usuario()->rol=='gestion'):?>
                    <li <?= $this->uri->segment(2) == 'gestion' || !$this->uri->segment(2) ? 'class="active"' : '' ?>><a href="<?= site_url('backend/gestion') ?>">Inicio</a></li>
                    <?php endif ?>
                    <?php if(UsuarioBackendSesion::usuario()->rol=='super' || UsuarioBackendSesion::usuario()->rol=='modelamiento'):?>
                    <li <?= $this->uri->segment(2) == 'procesos' || $this->uri->segment(2) == 'formularios' ? 'class="active"' : '' ?>><a href="<?= site_url('backend/procesos') ?>">Modelador de Procesos</a></li>
                    <?php endif ?>
                    <?php if(UsuarioBackendSesion::usuario()->rol=='super' || UsuarioBackendSesion::usuario()->rol=='operacion'):?>
                    <li <?= $this->uri->segment(2) == 'seguimiento' ? 'class="active"' : '' ?>><a href="<?= site_url('backend/seguimiento') ?>">Seguimiento</a></li>
                    <?php endif ?>
                    <?php if(UsuarioBackendSesion::usuario()->rol=='super'):?>
                    <li <?= $this->uri->segment(2) == 'configuracion' ? 'class="active"' : '' ?>><a href="<?= site_url('backend/configuracion') ?>">Configuración</a></li>
                    <?php endif ?>
                    <li><a href="http://ayuda.chilesinpapeleo.cl/simple" target="_blank">Ayuda</a></li>
                </ul>
                        </div>
                    </div>
                </div>
            </header>


            <?php $this->load->view($content) ?>
            
            <footer class="row">
                <div class="span12">
                    <p style="text-align: center;"><a class="label label-info" href="http://instituciones.chilesinpapeleo.cl/page/view/simple">Powered by SIMPLE</a></p>
                </div>
            </footer>
        </div>

    </body>
</html>
