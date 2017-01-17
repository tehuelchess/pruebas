<?php 

?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h3 id="myModalLabel">Editar Recurso</h3>
</div>
    <div class="modal-body">
        <div class="validacion"></div>
        <form id="frmconfeditrec" action="<?= base_url('manager/recursosgenerales/ajax_grabar_editar_recurso'); ?>" >                       
            <label>Archivo</label>
            <p class="link"><a target="_blank" href="<?= $urlfile ?>"><?= $nombre ?></a></p>
            <input type="hidden" name="edit_noderef" id="edit_txtnoderef" value="<?= $noderef ?>" />
            <label>Identificador</label>
            <input id="edit_txtidentificador" type="text" name="edit_identificador" maxlength="25" value="<?= $identificador ?>" />
            <div style="display: none;">
                <label>Tipo (Dublin Core Type Metadata)</label>
                <select id="edit_cmbtipo" name="edit_tipo">
                    <option value="">Seleccione el tipo de documento</option>
                    <option value="1" <?= $tipo == 1 ? "selected" : "" ?>>text.document</option>
                    <option value="2" <?= $tipo == 2 ? "selected" : "" ?>>image</option>
                    <option value="3" <?= $tipo == 3 ? "selected" : "" ?>>image.icon</option>
                </select>
            </div>
            <label>Descripci&oacute;n</label>
            <textarea id="edit_txtdescripcion" name="edit_descripcion" style="width: 300px; resize: none;"><?= $desc ?></textarea>
        </form>
    </div>
<div class="modal-footer">
    <button class="btn js_cerrar_vcancelar" data-dismiss="modal">Cerrar</button>
    <a href="javascript:;" class="btn btn-primary js-save-edit-recur">Actualizar</a>
</div>
