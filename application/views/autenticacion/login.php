<!DOCTYPE html>
<html lang="en">
    <head>
        <?php $this->load->view('head')?>
    </head>

    <body>

        <div class="container">
            <div class="row" style="margin-top: 100px;">
                <div class="span6 offset3">
                    <form method="post" class="ajaxForm" action="<?= site_url('autenticacion/login_form') ?>">        
                        <fieldset>
                            <legend>Autenticación</legend>
                            <div class="validacion"></div>
                            <label>Usuario</label>
                            <input name="usuario" type="text" class="input-xlarge">
                            <label>Contraseña</label>
                            <input name="password" type="password" class="input-xlarge">
                            <input type="hidden" name="redirect" value="<?=$redirect?>" />
                            
                            <p>O utilice <a href="<?=site_url('autenticacion/login_openid?redirect='.$redirect)?>"><img src="<?= base_url() ?>assets/img/openid_clave_unica.png" alt="OpenID"/></a></p>

                            <div class="form-actions">
                                <a class="btn" href="#" onclick="javascript:history.back();">Volver</a>
                                <button class="btn btn-primary" type="submit">Ingresar</button>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>


        </div> <!-- /container -->




    </body>
</html>
