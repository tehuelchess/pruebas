<div class="modal-header">
    <button class="close" data-dismiss="modal">×</button>
    <h3>Edición de Campo</h3>
</div>
<div class="modal-body">
    <form id="formEditarCampo" class="ajaxForm" method="POST" action="<?=site_url('backend/formularios/editar_campo_form/'.$campo->id)?>">
        <div class="validacion"></div>
        <label>Nombre</label>
        <input type="text" name="nombre" value="<?=$campo->nombre?>" />
        <label>Etiqueta</label>
        <input type="text" name="etiqueta" value="<?=$campo->etiqueta?>" />
        <label>Reglas de validación</label>
        <input type="text" name="validacion" value="<?=$campo->validacion?>" />
    </form>
</div>
<div class="modal-footer">
    <a href="#" data-dismiss="modal" class="btn">Cerrar</a>
    <a href="#" onclick="javascript:$('#formEditarCampo').submit();return false;" class="btn btn-primary">Guardar</a>
</div>