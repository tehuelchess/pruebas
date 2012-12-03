<div class="row-fluid">

    <div class="span3">
        <?php $this->load->view('backend/configuracion/sidebar') ?>
    </div>
    <div class="span9">
        <ul class="breadcrumb">
            <li>
                <a href="<?= site_url('backend/configuracion') ?>">Configuración</a> <span class="divider">/</span>
            </li>
            <li>
                <a href="<?= site_url('backend/configuracion/usuarios') ?>">Mi Cuenta</a> <span class="divider">/</span>
            </li>
            <li class="active">Cambiar contraseña</li>
        </ul>
        
        <?php if($this->session->flashdata('message')):?>
        <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <?=$this->session->flashdata('message')?></div>
        <?php endif?>

        <form class="ajaxForm" method="post" action="<?= site_url('backend/configuracion/cuenta_form/' . (isset($usuario)?$usuario->id:'')) ?>">
            <fieldset>
                <legend>Cambiar contraseña</legend>
                <div class="validacion"></div>
                <label>Contraseña</label>
                <input type="password" name="password" value=""/>
                <label>Confirmar contraseña</label>
                <input type="password" name="password_confirm" value=""/>
                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Guardar</button>
                    <a class="btn" href="#" onclick="javascript:history.back()">Cancelar</a>
                </div>
            </fieldset>
        </form>

    </div>
</div>