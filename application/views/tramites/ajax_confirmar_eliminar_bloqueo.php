<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h3 id="myModalLabel">Desbloquear Franja</h3>
</div>
    <div class="modal-body">
        <div class="validacion"></div>
        <label>¿Está seguro de desbloquar este intervalo?</label>
    </div>
<div class="modal-footer">
    <button class="btn js_cerrar_vcancelar" data-dismiss="modal">Cerrar</button>
    <a href="#" id="btnconfirmarunbloqueo" class="btn btn-primary">Aceptar</a>
</div>
<script>
    $(function(){
        $('#btnconfirmarunbloqueo').click(function(){
            $('.validacion').html('');
            var idbloqueo=<?= $id ?>;
            var urlbase='<?=site_url('/tramites/ajax_eliminar_bloqueo')?>';
            var form=$('#modalcancelar');
            $(form).append("<div class='ajaxLoader ajaxLoaderfunc'>Cargando</div>");
            var ajaxLoader=$(form).find(".ajaxLoader");
            $(ajaxLoader).css({
                left: ($(form).width()/2 - $(ajaxLoader).width()/2)+"px", 
                top: ($(form).height()/2 - $(ajaxLoader).height()/2)+"px"
            });
            $.ajax({
                url: urlbase,
                data:{
                    id:idbloqueo
                },
                dataType: "json",
                success: function( data ) {
                    if(data.code==200){
                        reload_dia();
                        $('#modalcancelar').modal('toggle');
                        $(".ajaxLoader").remove();
                    }else{
                        $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>'+data.mensaje+' .</div>');
                        $(".ajaxLoader").remove();
                    }
                }
            });
        });
        
    });
</script>