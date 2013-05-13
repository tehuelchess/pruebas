<meta charset="utf-8">
        <title><?=Cuenta::cuentaSegunDominio()!='localhost'?Cuenta::cuentaSegunDominio()->nombre_largo:'SIMPLE'?> - <?= $title ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">

        <!-- Le styles -->
        <link href="<?= base_url() ?>assets/css/bootstrap.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/css/responsive.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/js/bootstrap-datepicker/css/datepicker.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/js/jquery.chosen/chosen.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/js/file-uploader/fileuploader.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/css/common.css" rel="stylesheet">
        <link href="<?= base_url() ?>assets/css/frontend.css" rel="stylesheet">

        <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

        <!-- Le fav and touch icons -->
        <link rel="shortcut icon" href="<?= base_url() ?>assets/img/favicon.png">
        
        <script src="<?= base_url() ?>assets/js/jquery-ui/js/jquery-1.7.2.min.js"></script>
        <script src="<?= base_url() ?>assets/js/bootstrap.min.js"></script>
        <script src="<?= base_url() ?>assets/js/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
        <script src="<?= base_url() ?>assets/js/bootstrap-datepicker/js/locales/bootstrap-datepicker.es.js"></script>
        <script src="<?= base_url() ?>assets/js/jquery.chosen/chosen.jquery.min.js"></script> <?php //Soporte para selects con multiple choices    ?>
        <script src="<?= base_url() ?>assets/js/file-uploader/fileuploader.js"></script> <?php //Soporte para subir archivos con ajax    ?>
        <script src="http://www.java.com/js/deployJava.js"></script>    <?php //Para incrustar applets java y ser compatibles con distintos browsers ?>
        <script type="text/javascript">
            var site_url="<?= site_url() ?>";
            var base_url="<?= base_url() ?>";
        </script>
        <script src="<?= base_url() ?>assets/js/common.js"></script>