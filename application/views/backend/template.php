<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?=Cuenta::cuentaSegunDominio()!='localhost'?Cuenta::cuentaSegunDominio()->nombre_largo:'SIMPLE'?> - <?= $title ?></title>

        <!-- Le styles -->

        <link href="<?= base_url() ?>assets/css/bootstrap.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/css/responsive.css" rel="stylesheet">
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
        <link rel="shortcut icon" href="<?= base_url() ?>assets/img/favicon.png">

        <script src="<?= base_url() ?>assets/js/jquery-ui/js/jquery-1.7.2.min.js" type="text/javascript"></script>
        <script src="<?= base_url() ?>assets/js/bootstrap.min.js" type="text/javascript"></script> <?php //JS base     ?>
        <script src="<?= base_url() ?>assets/js/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
        <script src="<?= base_url() ?>assets/js/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js"></script>
        <script src="<?= base_url() ?>assets/js/bootstrap-typeahead-multiple/bootstrap-typeahead-multiple.js" type="text/javascript"></script> <?php //JS typeahead modificado para multiples items     ?>
        <script src="<?= base_url() ?>assets/js/jquery.chosen/chosen.jquery.min.js"></script> <?php //Soporte para selects con multiple choices     ?>
        <script src="<?= base_url() ?>assets/js/file-uploader/fileuploader.js"></script> <?php //Soporte para subir archivos con ajax     ?>
        <script src="<?= base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.8.21.custom.min.js" type="text/javascript"></script>
        <script src="<?= base_url() ?>assets/js/jquery.ui.touch-punch/jquery.ui.touch-punch.min.js" type="text/javascript"></script> <?php //JS para soportar drag & drop en iPad     ?>
        <script src="<?= base_url() ?>assets/js/jquery.ui.livedraggable/jquery.ui.livedraggable.js" type="text/javascript"></script> <?php //JS para que evento draggable sea live     ?>
        <script src="<?= base_url() ?>assets/js/jquery.doubletap/jquery.doubletap.js" type="text/javascript"></script> <?php //JS para soportar dobleclick en iPad     ?>      
        <script src="//d3dy5gmtp8yhk7.cloudfront.net/1.11/pusher.min.js" type="text/javascript"></script> <?php //JS para recibir eventos push via websockets     ?>
        <script src="<?= base_url() ?>assets/js/json-js/json2.js" type="text/javascript"></script> <?php //JS para convertir objetos a notacion JSON en multiples browsers     ?>
        <script src="<?= base_url() ?>assets/js/jquery.jsplumb/jquery.jsPlumb-1.3.16-all-min.js" type="text/javascript"></script> <?php //JS para conectar elementos como diagramas de flujos     ?>
        <script src="<?= base_url() ?>assets/js/highcharts/highcharts.js" type="text/javascript"></script> <?php //JS para hacer graficos     ?>
        <script type="text/javascript">
            var site_url = "<?= site_url() ?>";
            var base_url = "<?= base_url() ?>";
        </script>
        <script src="<?= base_url() ?>assets/js/common.js" type="text/javascript"></script>
        <script src="<?= base_url() ?>assets/js/backend.js" type="text/javascript"></script>


    </head>
    <body>

        <header>
            <div class="container-fluid">
                <div class="row-fluid">
                    <div class="span2">
                        <h1 id="logo"><a href="<?= site_url('backend/portada') ?>"><img src="<?= base_url() ?>assets/img/logo.png" alt="Tramitador" /></a></h1>
                    </div>
                    <div class="span10">
                        <div class="row-fluid">
                            <div class="span12">
                                <ul id="userMenu" class="nav nav-pills pull-right">
                                    <li class="dropdown">
                                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Bienvenido <?= UsuarioBackendSesion::usuario()->email ?><b class="caret"></b></a>
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
                                <ul id="menu" class="nav nav-pills pull-right">
                                    <?php if (UsuarioBackendSesion::usuario()->rol == 'super' || UsuarioBackendSesion::usuario()->rol == 'gestion'): ?>
                                        <li <?= $this->uri->segment(2) == 'gestion' || !$this->uri->segment(2) ? 'class="active"' : '' ?>><a href="<?= site_url('backend/gestion') ?>">Inicio</a></li>
                                    <?php endif ?>
                                    <?php if (UsuarioBackendSesion::usuario()->rol == 'super' || UsuarioBackendSesion::usuario()->rol == 'modelamiento'): ?>
                                        <li <?= $this->uri->segment(2) == 'procesos' || $this->uri->segment(2) == 'formularios' ? 'class="active"' : '' ?>><a href="<?= site_url('backend/procesos') ?>">Modelador de Procesos</a></li>
                                    <?php endif ?>
                                    <?php if (UsuarioBackendSesion::usuario()->rol == 'super' || UsuarioBackendSesion::usuario()->rol == 'operacion'): ?>
                                        <li <?= $this->uri->segment(2) == 'seguimiento' ? 'class="active"' : '' ?>><a href="<?= site_url('backend/seguimiento') ?>">Seguimiento</a></li>
                                    <?php endif ?>
                                    <?php if (UsuarioBackendSesion::usuario()->rol == 'super' || UsuarioBackendSesion::usuario()->rol == 'gestion'): ?>
                                        <li <?= $this->uri->segment(2) == 'reportes' || !$this->uri->segment(2) ? 'class="active"' : '' ?>><a href="<?= site_url('backend/reportes') ?>">Gestión</a></li>
                                    <?php endif ?>
                                    <?php if (UsuarioBackendSesion::usuario()->rol == 'super'): ?>
                                        <li <?= $this->uri->segment(2) == 'configuracion' ? 'class="active"' : '' ?>><a href="<?= site_url('backend/configuracion') ?>">Configuración</a></li>
                                    <?php endif ?>
                                    <li><a href="http://ayuda.chilesinpapeleo.cl/simple" target="_blank">Ayuda</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div id="main">
            <div class="container-fluid">
        <?php $this->load->view($content) ?>
            </div>
        </div>

        <footer>
            <div class="area1">
                <div class="container-fluid">
                    <div class="row-fluid">
                        <div class="span2">
                            <div class="col">
                                <h3>Otros proyectos</h3>
                            </div>
                        </div>
                        <div class="offset6 span4">

                        </div>
                    </div>
                    <div class="row-fluid">
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
                <div class="container-fluid">
                    <div class="row-fluid">
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
                    <a href="http://www.gob.cl" target="_blank"><img class="footerGob" src="<?= base_url() ?>assets/img/gobierno_chile.png" alt="Gobierno de Chile" /></a>
                </div>
            </div>

        </footer>

    </body>
</html>
