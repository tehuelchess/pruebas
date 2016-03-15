<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h3 id="myModalLabel">Descarga de documentos</h3>
</div>
<div class="modal-body">
    <form enctype="multipart/form-data" id="formDescargarDocumentos" method='POST' action="<?= site_url('etapas/descargar_form/') ?>" >
        <label>Seleccione:</label>
        
        <div class="radio">
          <label>
            <input type="radio" name="opcionesDescarga" id="opcionesDescarga1" value="documento">
            Generados
          </label>
        </div>
        <div class="radio">
          <label>
            <input type="radio" name="opcionesDescarga" id="opcionesDescarga2" value="dato">
            Subidos
          </label>
        </div>
        <div class="radio">
          <label>
            <input type="radio" name="opcionesDescarga" id="opcionesDescarga3" value="all" checked>
            Todos
          </label>
        </div>
        <input type="hidden" id="tramites" name="tramites" value="<?= $tramites ?>">
    </form>

</div>

<div class="modal-footer">
    <button class="btn" data-dismiss="modal">Cerrar</button>
    <a href="#" onclick="javascript:$('#formDescargarDocumentos').submit();$('#modal').modal('hide')" class="btn btn-primary">Descargar</a>
</div>