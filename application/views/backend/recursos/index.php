<style>
    a.bnt-recursos {
        width: 65px;
        margin-top: 5px;
    }
</style>
<div class="row-fluid">
    <div class="span3">
        <?php $this->load->view('backend/configuracion/sidebar') ?>
    </div>
    <div class="span9">
        <ul class="breadcrumb">
            <li>
                <a href="<?=site_url('backend/configuracion')?>">Configuración</a> <span class="divider">/</span>
            </li>
            <li class="active">Recursos Generales</li>
        </ul>
        <?php $this->load->view('messages') ?>
        <div>
            <button class="btn btn-warning js-nuevo-recurso">Cargar Nuevo Recurso</button>
        </div>
        <table id="tblresources" class="table">
            <thead>
                <tr>
                    <th style="width: 250px">Identificador</th>
                    <th>Tipo(MIME)</th>
                    <th>Descripci&oacute;n</th>
                    <th style="width: 250px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($checked) && $checked == 1) {
                    if (isset($data) && is_array($data)) {
                        if (count($data) > 0) {                            
                            foreach($data as $item) {
                                $id = str_replace('://','/', $item->noderef);
                                $acciones = '<a class="btn btn-primary bnt-recursos js-editar-recurso" data-id="'.$id.'" data-noderef="'.$item->noderef.'" data-nombre="'.$item->nombre.'" data-desc="'.$item->descripcion.'" data-identificador="'.$item->identificador.'" data-tipo="'.$item->tipo.'" href="javascript:;"><i class="icon-white icon-edit"></i> Editar</a> <a class="btn btn-danger bnt-recursos btncanappofun js-eliminar-recurso" href="javascript:;" ><i class="icon-white icon-remove"></i> Eliminar</a>';
                            }
                        } else {
                            echo '<tr><td colspan="4">No existen recursos</td></tr>';
                        }
                    } else {
                        echo '<tr><td colspan="4">No existen recursos</td></tr>';
                    }
                } else {
                    echo '<tr><td colspan="4">La configuraci&oacute;n CMS del sistema no est&aacute; habilitada.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<form id="frmdata">
    <input type="hidden" name="nombre" id="nombre">
    <input type="hidden" name="id" id="id">
    <input type="hidden" name="desc" id="desc">
    <input type="hidden" name="_tipo" id="_tipo">
    <input type="hidden" name="_identificador" id="_identificador">
</form>
<div id="modaleditresources" class="modal hide fade"></div>
<div id="modalnuevorecurso" class="modal hide fade">
    <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h3 id="myModalLabel">Nuevo Recurso</h3>
    </div>
        <div class="modal-body">
            <form id="frmnresource">                
                <div class="validacion"></div>
                <input type="hidden" id="namefile" name="namefile" />
                <div class="file-uploader" data-action="<?=site_url('alfrescocms/uploadfiletosimple')?>"></div>
                <p class="link"></p>
                <label>Identificador</label>
                <input id="txtidentificador" type="text" name="identificador" maxlength="25" />
                <label>Tipo (Dublin Core Type metadata)</label>
                <select id="cmbtipo" name="tipo">
                    <option value="">Seleccione el tipo de documento</option>
                    <option value="1">text.document</option>
                    <option value="2">image</option>
                    <option value="3">image.icon</option>
                </select>
                <label>Descripci&oacute;n</label>
                <textarea placeholder="Dublin Core Subject Metadata" id="txtdescripcion" name="descripcion" style="width: 300px; resize: none;"></textarea>
            </form>
        </div>
    <div class="modal-footer">
        <button class="btn js_cerrar_vcancelar" data-dismiss="modal">Cerrar</button>
        <button id="btngrabresource" class="btn btn-primary" >Grabar</button>
    </div>
</div>
<script>
    
    $(document).ready(function() {
        ajax_cargar_resources();
        
        $('#btngrabresource').click(function() {
            $('.validacion').html('');
            
            var $obja = $('.link').children('a');
            var file = $($obja[0]).html();
            var identificador = $.trim($("#txtidentificador").val());
            
            if (typeof file != "undefined" && file !== null) {
                if (identificador != "") {
                    if ($.trim($("#cmbtipo").val()) != "") {
                        if ($.trim($("#txtdescripcion").val()) != "") {
                            var idExiste = false;
                            $('#tblresources tbody tr td.identificador a.link-id').each(function() {
                                var idTabla = $.trim($(this).text()).toUpperCase();
                                if (identificador.toUpperCase() == idTabla) {
                                    idExiste = true;
                                    return;
                                }
                            });
                            
                            if (!idExiste) {
                                var form = $('.modal-body');
                                $(form).append("<div class='ajaxLoader ajaxLoaderfunc'>Cargando</div>");
                                var ajaxLoader = $(form).find(".ajaxLoader");

                                $(ajaxLoader).css({
                                    left: ($(form).width()/2 - $(ajaxLoader).width()/2) + "px", 
                                    top: ($(form).height()/2 - $(ajaxLoader).height()/2) + "px"
                                });

                                var url = "<?= site_url('backend/recursos/save/') ?>";
                                $('#namefile').val(file);
                                $.ajax({
                                    url: url,
                                    data: {namefile: $('#namefile').val(), identificador: $('#txtidentificador').val(), tipo: $('#cmbtipo').val(), descripcion: $('#txtdescripcion').val()},
                                    dataType: "json",
                                    success: function(data) {
                                        if ($.trim(data.status) == "success") {
                                            $('.ajaxLoader').remove();
                                            $("#modalnuevorecurso").modal('toggle');
                                            ajax_cargar_resources();
                                        } else {
                                            $('.ajaxLoader').remove();
                                            $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>'+data.error+'</div>');
                                        }
                                    },
                                    error:function(xhr, status, error){
                                        $('.ajaxLoader').remove();  
                                    }
                                });
                            } else {
                                $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>El identificador ya existe.</div>');
                            }
                        } else {
                            $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>La descripción del archivo es requerida.</div>');
                        }
                    } else {
                        $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>El tipo de documento es requerido.</div>');
                    }
                } else {
                    $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>El identificador de archivo es requerido.</div>');
                }
            } else {
                $('.validacion').html('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>El archivo es requerido.</div>');
            }
        });
        
        $('.js-nuevo-recurso').click(function() {
            $('.validacion').html('');
            $("#txtidentificador").val('');
            $("#cmbtipo").val('');
            $("#txtdescripcion").val('');
            $('.qq-upload-list').html('');
            $('.link').html('');
            $("#modalnuevorecurso").modal();
            $('#modalnuevorecurso').on('hidden.bs.modal', function (e) {
                var $obja = $('.link').children('a');
                var file = $($obja[0]).html();
                eliminar_archivo_simple(file);
            })
        });
        
        $(document).on('click', '.js-editar-recurso', function() {
            $('.validacion').html('');
            var nombre = $(this).attr('data-nombre');
            var desc = $(this).attr('data-desc');
            var id = $(this).attr('data-id');
            var identificador = $(this).attr('data-identificador');
            var tipo = $(this).attr('data-tipo');
            $('#nombre').val(nombre);
            $('#id').val(id);
            $('#desc').val(desc);
            $('#_identificador').val(identificador);
            $('#_tipo').val(tipo);
            var param = $("#frmdata").serialize();
            $("#modaleditresources").load("<?= site_url('backend/recursos/ajaxviewedit/') ?>" + '?' + param);
            $("#modaleditresources").modal();
        });
        
        $(document).on('click', '.js-eliminar-recurso', function() {
            var nombre = $(this).prev().attr('data-nombre');
            eliminar_archivo(nombre, $(this));
        });
    });
    
    function eliminar_archivo_simple(nombre) {        
        $.ajax({
            url: "<?= site_url('backend/recursos/deletefile') ?>",
            data: {
                file: nombre
            },
            dataType: "json",
            success: function( data ) {
                
            }
        });
    }
    
    function eliminar_archivo(nombre, obj) {        
        $.ajax({
            url: "<?= site_url('backend/recursos/ajaxdelete') ?>",            
            data: {
                nombre: nombre
            },
            dataType: "json",
            success: function(data) {
                if (!data.error) {
                    obj.parent().parent().remove();
                    var tbody = $('#tblresources tbody');
                    if (tbody.find('tr:first').length == 0) {
                        tbody.html('<tr><td colspan="4">No existen recursos</td></tr>');
                    }
                } else {
                    alert(data.error);
                }
            }
        });
    }
    
    function ajax_cargar_resources() {        
        var form = $('#main');
        $(form).append("<div class='ajaxLoader ajaxLoaderfunc'>Cargando</div>");
        var ajaxLoader = $(form).find(".ajaxLoader");
        
        $(ajaxLoader).css({
            left: ($(form).width()/2 - $(ajaxLoader).width()/2) + "px", 
            top: ($(form).height()/2 - $(ajaxLoader).height()/2) + "px"
        });
        
        $("#tblresources tbody").html('');
        $.ajax({
            url: "<?= site_url('backend/recursos/ajaxview') ?>",
            dataType: "json",
            success: function(data) {
                $.each(data.rows, function(index, element) {
                    $("#tblresources tbody").append(element);
                });                
                $('.ajaxLoader').remove();
            },
            error:function(xhr, status, error) {
                $('.ajaxLoader').remove();
            }
        });
    }
</script>