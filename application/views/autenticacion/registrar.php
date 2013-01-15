<!DOCTYPE html>
<html lang="en">
    <head>
        <?php $this->load->view('head')?>
    </head>

    <body>

        <div class="container">
            <div class="row" style="margin-top: 100px;">
                <div class="span6 offset3">
                    <form method="post" class="ajaxForm" action="<?= site_url('autenticacion/registrar_form') ?>">        
                        <fieldset>
                            <legend>Registrarse en el sistema</legend>
                            <div class="validacion"></div>
                            <label>Usuario</label>
                            <input name="usuario" type="text" class="input-xlarge">
                            <label>Contraseña</label>
                            <input name="password" type="password" class="input-xlarge">
                            <label>Confirmar contraseña</label>
                            <input name="password_confirm" type="password" class="input-xlarge">
                            <label>Correo electrónico</label>
                            <input type="text" name="email" class="input-xlarge" />
                            <p class="help-block">En este correo recibiras notificaciones del estado de sus trámites.</p>
                            <div class="form-actions">
                                <button class="btn" onclick="history.back()">Volver</button>
                                <button class="btn btn-primary" type="submit">Ingresar</button>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>


        </div> <!-- /container -->




    </body>
</html>
