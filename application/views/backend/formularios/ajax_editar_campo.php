<div class="modal-header">
    <button class="close" data-dismiss="modal">×</button>
    <h3>Edición de Campo</h3>
</div>
<div class="modal-body">
    <form id="formEditarCampo" class="ajaxForm" method="POST" action="<?=site_url('backend/formularios/editar_campo_form/'.(isset($campo)?$campo->id:''))?>">
        <div class="validacion"></div>
        <?php if(!isset($campo)):?>
        <input type="hidden" name="formulario_id" value="<?=$formulario_id?>" />
        <input type="hidden" name="tipo" value="<?=$tipo?>" />
        <?php endif; ?>
        <label>Nombre</label>
        <input type="text" name="nombre" value="<?=isset($campo)?$campo->nombre:''?>" />
        <label>Etiqueta</label>
        <input type="text" name="etiqueta" value="<?=isset($campo)?$campo->etiqueta:''?>" />
        <label>Reglas de validación</label>
        <input type="text" name="validacion" value="<?=isset($campo)?$campo->validacion:''?>" />
    </form>
</div>
<div class="modal-footer">
    <a href="#" data-dismiss="modal" class="btn">Cerrar</a>
    <a href="#" onclick="javascript:$('#formEditarCampo').submit();return false;" class="btn btn-primary">Guardar</a>
</div>