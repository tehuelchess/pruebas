<div class="modal-header">
    <a class="close" data-dismiss="modal">×</a>
    <h3>Editar Conexión</h3>
</div>
<div class="modal-body" style="min-height: 320px;">
    <form id="formEditarConexion" class="ajaxForm" method="POST" action="<?= site_url('backend/procesos/editar_conexion_form/' . $conexion->id) ?>">
        <div class="validacion"></div>

        <label>Tarea Origen</label>
        <input type="text" value="<?=$conexion->TareaOrigen->nombre?>" disabled />

        <label>Tarea Destino</label>
        <input type="text" value="<?=$conexion->TareaDestino->nombre?>" disabled />

        <label>Regla</label>
        <p class="help-block">Los nombres de campos escribalos anteponiendo @@. Ej: @@edad >= 18</p>
        <input type="text" name="regla" value="<?=$conexion->regla?>" /> 
        
    </form>
</div>
<div class="modal-footer">
    <a href="<?= site_url('backend/procesos/eliminar_conexion/' . $conexion->id) ?>" class="btn btn-danger pull-left" onclick="return confirm('¿Esta seguro que desea eliminar esta conexión?')">Eliminar</a>
    <a href="#" data-dismiss="modal" class="btn">Cerrar</a>
    <a href="#" onclick="javascript:$('#formEditarConexion').submit();return false;" class="btn btn-primary">Guardar</a>
</div>