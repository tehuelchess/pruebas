<!DOCTYPE html>
<html lang="en">
    <head>
        <?php $this->load->view('head') ?>
    </head>

    <body>

        <div class="container">
            <div class="row" style="margin-top: 100px;">
                <div class="span6 offset3">
                    <form method="post" class="well ajaxForm" action="<?= site_url('autenticacion/olvido_form') ?>">        
                        <fieldset>
                            <legend>¿Olvidaste tu contraseña?</legend>
                            <?php if ($this->session->flashdata('message')): ?>
                                <div class="alert alert-success">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <?= $this->session->flashdata('message') ?>
                                </div>
                            <?php endif ?>
                            <div class="validacion"></div>

                            <p>Al hacer click en Reestablecer se te enviara un email indicando las instrucciones para reestablecer tu contraseña.</p>

                            <label>Usuario</label>
                            <input name="usuario" type="text" class="input-xlarge">


                            <div class="form-actions">
                                <a class="btn" href="#" onclick="javascript:history.back();">Volver</a>
                                <button class="btn btn-primary" type="submit">Reestablecer</button>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>


        </div> <!-- /container -->




    </body>
</html>
