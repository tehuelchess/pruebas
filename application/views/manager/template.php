<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="widpth=device-width, initial-scale=1.0">
        <title>Tramitador</title>

        <!-- Le styles -->

        <link href="<?= base_url() ?>assets/css/bootstrap.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/css/responsive.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/js/file-uploader/fileuploader.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/css/common.css" rel="stylesheet">


        <script src="<?= base_url() ?>assets/js/jquery/jquery-1.8.3.min.js" type="text/javascript"></script>
        <script src="<?= base_url() ?>assets/js/bootstrap.min.js" type="text/javascript"></script> <?php //JS base       ?>
        <script src="<?= base_url() ?>assets/js/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
        <script src="<?= base_url() ?>assets/js/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js"></script>
        <script src="<?= base_url() ?>assets/js/jquery.chosen/chosen.jquery.min.js"></script> <?php //Soporte para selects con multiple choices     ?>
        <script src="<?= base_url() ?>assets/js/file-uploader/fileuploader.js"></script> <?php //Soporte para subir archivos con ajax     ?>
        <script src="<?= base_url() ?>assets/js/common.js"></script>
        <script type="text/javascript">
            var site_url = "<?= site_url() ?>";
            var base_url = "<?= base_url() ?>";
        </script>


    </head>
    <body>
        <div class="container-fluid">

            <header class="row-fluid">
                <div class="span4">
                    <h1><a href="<?= site_url('backend/portada') ?>"><img src="<?= base_url() ?>assets/img/logo.png" alt="Tramitador" /></a></h1>
                </div>
                <div class="span8">
                    <div class="row-fluid">
                        <div class="span12">
                            <ul id="userMenu" class="nav nav-pills pull-right">
                                <li class="dropdown">
                                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">Bienvenido <?= UsuarioManagerSesion::usuario()->usuario ?><span class="caret"></span></a>
                                    <ul class="dropdown-menu">
                                        <li><a href="<?= site_url('manager/autenticacion/logout') ?>">Cerrar sesión</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>
            </header>
            <div id="main">
                <div class="row-fluid">
                    <div class="span3">
                        <ul class="nav nav-list">
                            <li><a href="<?= site_url('manager') ?>">Portada</a></li>
                            <li class="nav-header">Administración</li>
                            <li><a href="<?= site_url('manager/cuentas') ?>">Cuentas</a></li>
                            <li><a href="<?= site_url('manager/usuarios') ?>">Usuarios Backend</a></li>
                            <li class="nav-header">Estadisticas</li>
                            <li><a href="<?= site_url('manager/estadisticas/cuentas') ?>">Trámites en curso</a></li>
                        </ul>
                    </div>
                    <div class="span9">
                        <?=$this->session->flashdata('message')?'<div class="alert alert-success">'.$this->session->flashdata('message').'</div>':''?>
                        <?php $this->load->view($content) ?>
                    </div>
                </div>
            </div>




            <footer class="row-fluid">
                <div class="span12">
                    <p style="text-align: center;"><a class="label label-info" href="http://instituciones.chilesinpapeleo.cl/page/view/simple">Powered by SIMPLE</a></p>
                </div>
            </footer>
        </div>

    </body>
</html>
