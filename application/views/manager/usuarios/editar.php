<ul class="breadcrumb">
    <li><a href="<?= site_url('manager/cuentas') ?>">Usuarios Backend</a></li> /
    <li class="active"><?= $title ?></li>
</ul>

<form class="ajaxForm" method="post" action="<?= site_url('manager/usuarios/editar_form/' . $usuario->id) ?>">
    <fieldset>
        <legend><?= $title ?></legend>
        <div class="validacion"></div>
        <label>Correo Electrónico</label>
        <input type="text" name="email" value="<?=$usuario->email?>" />
        <label>Contraseña</label>
        <input type="password" name="password" value=""/>
        <label>Confirmar contraseña</label>
        <input type="password" name="password_confirm" value=""/>
        <label>Nombre</label>
        <input type="text" name="nombre" value="<?= $usuario->nombre?>"/>
        <label>Apellidos</label>
        <input type="text" name="apellidos" value="<?= $usuario->apellidos?>"/>
        <label>Cuenta</label>
        <select name="cuenta_id">
            <?php foreach($cuentas as $c):?>
            <option value="<?=$c->id?>" <?=$c->id==$usuario->cuenta_id?'selected':''?>><?=$c->nombre?></option>
            <?php endforeach ?>
        </select>
        <label>Rol</label>
        <select name="rol">
            <option value="super" <?= $usuario->rol == 'super' ? 'selected' : '' ?>>super</option>
            <option value="modelamiento" <?= $usuario->rol == 'modelamiento' ? 'selected' : '' ?>>modelamiento</option>
            <option value="operacion" <?= $usuario->rol == 'operacion' ? 'selected' : '' ?>>operacion</option>
            <option value="gestion" <?= $usuario->rol == 'gestion' ? 'selected' : '' ?>>gestion</option>
            <option value="gestion" <?= $usuario->rol == 'reportes' ? 'selected' : '' ?>>gestion</option>
        </select>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Guardar</button>
            <a class="btn" href="<?= site_url('manager/usuarios') ?>">Cancelar</a>
        </div>
    </fieldset>
</form>