<form method="POST" class="ajaxForm" action="<?=site_url('cuenta/editar_form')?>">
    <fieldset>
        <legend>Edita la información de tu cuenta</legend>
        <div class="validacion"></div>
        <label>RUT</label>
        <input type="text" name="rut" value="<?=$usuario->rut?>" />
        <label>Nombre</label>
        <input type="text" name="nombre" value="<?=$usuario->nombre?>" />
        <label>Apellidos</label>
        <input type="text" name="apellidos" value="<?=$usuario->apellidos?>" />
        <label>Correo electrónico</label>
        <input type="text" name="email" value="<?=$usuario->email?>" />
        <input type="hidden" name="redirect" value="<?=$redirect?>" />
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Guardar</button>
            <button class="btn" type="button" onclick="javascript:history.back()">Cancelar</button>
        </div>
    </fieldset>
</form>