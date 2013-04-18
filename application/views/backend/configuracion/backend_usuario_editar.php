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
                <a href="<?= site_url('backend/configuracion/usuarios') ?>">Usuarios</a> <span class="divider">/</span>
            </li>
            <li class="active"><?= isset($usuario) ?$usuario->email:'Crear' ?></li>
        </ul>

        <form class="ajaxForm" method="post" action="<?= site_url('backend/configuracion/backend_usuario_editar_form/' . (isset($usuario)?$usuario->id:'')) ?>">
            <fieldset>
                <legend>Editar usuario</legend>
                <div class="validacion"></div>
                <label>E-Mail</label>
                <input type="text" name="email" value="<?=isset($usuario)?$usuario->email:''?>" <?=  isset($usuario)?'disabled':''?>/>
                <label>Contraseña</label>
                <input type="password" name="password" value=""/>
                <label>Confirmar contraseña</label>
                <input type="password" name="password_confirm" value=""/>
                <label>Nombre</label>
                <input type="text" name="nombre" value="<?=isset($usuario)?$usuario->nombre:''?>"/>
                <label>Apellidos</label>
                <input type="text" name="apellidos" value="<?=isset($usuario)?$usuario->apellidos:''?>"/>
                <label>Rol</label>
                <select name="rol">
                    <option value="super" <?=  isset($usuario) && $usuario->rol=='super'?'selected':''?>>super</option>
                    <option value="modelamiento" <?=  isset($usuario) && $usuario->rol=='modelamiento'?'selected':''?>>modelamiento</option>
                    <option value="operacion" <?=  isset($usuario) && $usuario->rol=='operacion'?'selected':''?>>operacion</option>
                    <option value="gestion" <?=  isset($usuario) && $usuario->rol=='gestion'?'selected':''?>>gestion</option>
                </select>
                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Guardar</button>
                    <a class="btn" href="<?=site_url('backend/configuracion/backend_usuarios')?>">Cancelar</a>
                </div>
            </fieldset>
        </form>

    </div>
</div>