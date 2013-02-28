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


        <script src="<?= base_url() ?>assets/js/jquery-ui/js/jquery-1.7.2.min.js" type="text/javascript"></script>
        <script src="<?= base_url() ?>assets/js/bootstrap.min.js" type="text/javascript"></script> <?php //JS base    ?>
        <script type="text/javascript">
            var site_url="<?= site_url() ?>";
            var base_url="<?= base_url() ?>";
        </script>


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
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Bienvenido <?=  UsuarioManagerSesion::usuario()->usuario?><b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="<?= site_url('manager/autenticacion/logout') ?>">Cerrar sesión</a></li>
                        </ul>
                    </li>
                </ul>
                        </div>
                    </div>
                    <div class="row-fluid">
                        <div class="span12">
                <ul id="menu" class="nav nav-pills">
                    <li <?= $this->uri->segment(2) == 'portada' || !$this->uri->segment(2) ? 'class="active"' : '' ?>><a href="<?= site_url('backend/portada') ?>">Inicio</a></li>
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
