<script>
    $(document).ready(function() {
        $(".datepicker")
                .datepicker({
            format: "dd-mm-yyyy",
            weekStart: 1,
            autoclose: true,
            language: "es"
        })
    });

</script>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h3 id="myModalLabel">Editar Fecha de Vencimiento</h3>
</div>
<div class="modal-body">
    <form id="formEditarVencimiento" method='POST' class='ajaxForm' action="<?= site_url('backend/seguimiento/editar_vencimiento_form/' . $etapa->id) ?>">
        <div class='validacion'></div>
        <label>Fecha de Vencimiento</label>
        <input class='datepicker' name='vencimiento_at' type='text' value='<?= $etapa->vencimiento_at?date('d-m-Y',  strtotime($etapa->vencimiento_at)):'' ?>' placeholder='DD-MM-YYYY' />
    </form>

</div>
<div class="modal-footer">
    <button class="btn" data-dismiss="modal">Cerrar</button>
    <a href="#" onclick="javascript:$('#formEditarVencimiento').submit();
        return false;" class="btn btn-primary">Guardar</a>
</div>