<div class="modal-header">
    <button class="close" data-dismiss="modal">×</button>
    <h3>Agregar Campo</h3>
</div>
<div class="modal-body">
    <form id="formEditarCampo" class="ajaxForm" method="POST" action="<?=site_url('backend/formularios/agregar_campo_form/'.$formulario_id.'/'.$tipo)?>">
        <div class="validacion"></div>
        <label>Nombre</label>
        <input type="text" name="nombre" />
        <label>Etiqueta</label>
        <input type="text" name="etiqueta" />
        <label>Reglas de validación</label>
        <input type="text" name="validacion" />
    </form>
</div>
<div class="modal-footer">
    <a href="#" data-dismiss="modal" class="btn">Cerrar</a>
    <a href="#" onclick="javascript:$('#formEditarCampo').submit();return false;" class="btn btn-primary">Guardar</a>
</div>