<?php 

?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h3 id="myModalLabel">Editar Recurso</h3>
</div>
    <div class="modal-body">
        <div class="validacion val-recursos"></div>
        <form id="frmconfeditrec" action="<?= base_url('backend/recursos/ajaxsaveedit'); ?>" >
            <input type="hidden" name="id" value="<?= $id ?>" >
            <input type="hidden" name="noderef" id="edit_txtnoderef" value="<?= $noderef ?>" >
            <label>Archivo</label>
            <p class="link">
                <a target="_blank" href="<?= $urlfile ?>"><?= $nombre ?></a>
            </p>
            <label>Identificador</label>
            <input type="text" name="identificador" id="identificador" value="<?= $identificador ?>" />
            <label>Descripci&oacute;n</label>
            <textarea id="desc" name="desc" style="width: 300px; resize: none;"><?= $desc ?></textarea>
        </form>
    </div>
<div class="modal-footer">
    <button class="btn js_cerrar_vcancelar" data-dismiss="modal">Cerrar</button>
    <a href="#" class="btn btn-primary js-save-edit-recur">Actualizar</a>
</div>
<script>
    $(function(){
        $('.val-recursos').html('');
        $('.js-save-edit-recur').click(function(){            
            var identificador = $.trim($("#identificador").val());
            var noderef = $.trim($("#edit_txtnoderef").val()).replace("://", "/");
            
            if (identificador != "") {
                if ($.trim($("#desc").val()) != "") {
                    var idExiste = false;
                    $('#tblresources tbody tr').each(function() {
                        var idTabla = $.trim($(this).find('td:first a.link-id').text()).toUpperCase();
                        var noderefTabla = $.trim($(this).find('td:last a.js-editar-recurso').attr('data-id'));                        
                        
                        if (identificador.toUpperCase() == idTabla && noderef != noderefTabla) {
                            idExiste = true;
                            return;
                        }
                    });

                    if (!idExiste) {
                        var form=$('.modal-body');
                        $(form).append("<div class='ajaxLoader ajaxLoaderfunc'>Cargando</div>");
                        var ajaxLoader=$(form).find(".ajaxLoader");
                        $(ajaxLoader).css({
                            left: ($(form).width()/2 - $(ajaxLoader).width()/2)+"px", 
                            top: ($(form).height()/2 - $(ajaxLoader).height()/2)+"px"
                        });
                        var param=$('#frmconfeditrec').serialize();
                        $.ajax({
                            url: $('#frmconfeditrec').attr('action'),
                            data:param,
                            dataType: "json",
                            success: function( data ) {
                                $(ajaxLoader).remove();
                                if(data.status=="error"){
                                    $('.val-recursos').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>'+data.error+' .</div>');
                                }else{
                                    $("#modaleditresources").modal('toggle');
                                    ajax_cargar_resources();
                                }                    
                            }
                        });
                    } else {
                        $('.val-recursos').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>El identificador ya existe.</div>');
                    }
                } else {
                    $('.val-recursos').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>La descripción del archivo es requerida.</div>');
                }
            } else {
                $('.val-recursos').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>El identificador de archivo es requerido.</div>');
            }
        });        
    });
</script>