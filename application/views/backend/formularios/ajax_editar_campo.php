<script type="text/javascript">
    $(document).ready(function(){
        
        //Funcionalidad del llenado de nombre usando el boton de asistencia
        $("#formEditarCampo .asistencia .dropdown-menu a").click(function(){
            var nombre=$(this).text();
            $("#formEditarCampo input[name=nombre]").val(nombre);
        });
        
        //Llenamos el select box de dependientes
        var selected=$("#formEditarCampo select[name=dependiente_campo]").val();
        var html='<option></option>';
        var names=new Array();
        $("#formEditarFormulario :input[name]").each(function(i,el){
            var name=$(el).attr("name");
            if($.inArray(name, names)==-1){
                names.push(name);
                html+='<option>'+name+'</option>';
            }
        });
        $("#formEditarCampo select[name=dependiente_campo]").html(html);
        $("#formEditarCampo select[name=dependiente_campo]").val(selected);
        
        //Funcionalidad en campo dependientes para seleccionar entre tipo regex y string
        $buttonRegex=$("#formEditarCampo .campoDependientes .buttonRegex");
        $buttonString=$("#formEditarCampo .campoDependientes .buttonString");
        $inputDependienteTipo=$("#formEditarCampo input[name=dependiente_tipo]");
        $buttonString.attr("disabled",$inputDependienteTipo.val()=="string");
        $buttonRegex.attr("disabled",$inputDependienteTipo.val()=="regex");
        $buttonRegex.click(function(){
            $buttonString.prop("disabled",false);
            $buttonRegex.prop("disabled",true);
            $inputDependienteTipo.val("regex");
        });
        $buttonString.click(function(){
            $buttonString.prop("disabled",true);
            $buttonRegex.prop("disabled",false);
            $inputDependienteTipo.val("string");
        });
        
        //Llenado automatico del campo nombre
        $("#formEditarCampo input[name=etiqueta]").blur(function(){
            ellipsize($("#formEditarCampo input[name=etiqueta]"),$("#formEditarCampo input[name=nombre]"));
        });
        //Llenado automatico del campo valor
        $("#formEditarCampo").on("blur","input[name$='[etiqueta]']",function(){
            var campoOrigen=$(this);
            var campoDestino=$(this).closest("tr").find("input[name$='[valor]']")
            ellipsize(campoOrigen,campoDestino);
        });
        
        function ellipsize(campoOrigen,campoDestino){
            if($(campoDestino).val()==""){
                var string=$(campoOrigen).val();
                string=string.toLowerCase();
                string=string.replace(/\s/g,"_");
                string=string.replace(/á/g,"a");
                string=string.replace(/é/g,"e");
                string=string.replace(/í/g,"i");
                string=string.replace(/ó/g,"o");
                string=string.replace(/ú/g,"u");
                string=string.replace(/\W/g,"");
                $(campoDestino).val(string);
            }
        }
        
    });
    
</script>

<div class="modal-header">
    <button class="close" data-dismiss="modal">×</button>
    <h3>Edición de Campo</h3>
</div>
<div class="modal-body">
    <form id="formEditarCampo" class="ajaxForm" method="POST" action="<?= site_url('backend/formularios/editar_campo_form/' . ($edit ? $campo->id : '')) ?>">
        <div class="validacion"></div>
        <?php if (!$edit): ?>
            <input type="hidden" name="formulario_id" value="<?= $formulario->id ?>" />
            <input type="hidden" name="tipo" value="<?= $campo->tipo ?>" />
        <?php endif; ?>
        <label>Etiqueta</label>
        <input type="text" name="etiqueta" value="<?= htmlspecialchars($campo->etiqueta) ?>" />
        <?php if($campo->requiere_nombre):?>
        <label>Nombre</label>
        <input type="text" name="nombre" value="<?= $campo->nombre ?>" />
        <?php $campos_asistencia=$formulario->Proceso->getCampos($campo->tipo) ?>
        <?php if($campos_asistencia->count()):?>
        <div class="btn-group asistencia" style="display: inline-block; vertical-align: top;">
            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-th-list"></i><span class="caret"></span></a>
            <ul class="dropdown-menu">
                <?php foreach ($campos_asistencia as $c): ?>
                    <li><a href="#"><?= $c->nombre ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif ?>
        <?php else: ?>
        <input type="hidden" name="nombre" value="<?=$campo->nombre?$campo->nombre:uniqid();?>" />
        <?php endif; ?>

        <?php if(!$campo->estatico):?>
        <label>Ayuda contextual (Opcional)</label>
        <input type="text" class="input-xxlarge" name="ayuda" />
        <?php endif ?>
        
        <?php if (!$campo->estatico): ?>
            <label class="checkbox"><input type="checkbox" name="readonly" value="1" <?=$campo->readonly?'checked':''?> /> Solo lectura</label>
        <?php endif; ?>
        <?php if (!$campo->estatico): ?>
            <label>Reglas de validación</label>
            <input type="text"
                   name="validacion"
                   data-provide="typeahead"
                   data-mode="multiple"
                   data-delimiter="|"
                   data-source="[&quot;required&quot;,&quot;rut&quot;,&quot;min_length[num]&quot;,&quot;max_length[num]&quot;,&quot;exact_length[num]&quot;,&quot;greater_than[num]&quot;,&quot;less_than[num]&quot;,&quot;alpha&quot;,&quot;alpha_numeric&quot;,&quot;alpha_dash&quot;,&quot;numeric&quot;,&quot;integer&quot;,&quot;decimal&quot;,&quot;is_natural&quot;,&quot;is_natural_no_zero&quot;,&quot;valid_email&quot;,&quot;valid_emails&quot;,&quot;valid_ip&quot;,&quot;valid_base64&quot;]"
                   value="<?= $edit ? implode('|', $campo->validacion) : 'required' ?>"/>
               <?php endif; ?>
            <?php if(!$campo->estatico):?>
            <label>Valor por defecto</label>
            <input type="text" name="valor_default" value="<?=$campo->valor_default?>" />
            <?php endif ?>
            <div class="campoDependientes">                
                <label>Visible solo si</label>
                <select class="input-medium" name="dependiente_campo">
                    <option value="<?=$campo->dependiente_campo?>"><?=$campo->dependiente_campo?></option>
                </select>
                <span>=</span>
                <span class="input-append">
                    <input type="text" name="dependiente_valor" value="<?= isset($campo) ? $campo->dependiente_valor : '' ?>" /><button type="button" class="buttonString btn">String</button><button type="button" class="buttonRegex btn">Regex</button>
                </span>
                <input type="hidden" name="dependiente_tipo" value="<?=isset($campo) && $campo->dependiente_tipo? $campo->dependiente_tipo:'string' ?>" />
            </div>
            
            <?=$campo->extraForm()?$campo->extraForm():''?>
            
        <?php if ($campo->requiere_datos): ?>
            <div class="datos">
                <script type="text/javascript">
                    $(document).ready(function(){
                        $('#formEditarCampo .datos .nuevo').click(function(){
                            var pos=$('#formEditarCampo .datos table tbody tr').size();
                            var html='<tr>';
                            html+='<td><input type="text" name="datos['+pos+'][etiqueta]" /></td>';
                            html+='<td><input class="input-small" type="text" name="datos['+pos+'][valor]" /></td>';
                            html+='<td><button type="button" class="btn eliminar"><i class="icon-remove"></i> Eliminar</button></td>';
                            html+='</tr>';
                                            
                            $('#formEditarCampo .datos table tbody').append(html);
                        });
                        $('#formEditarCampo .datos').on('click','.eliminar',function(){
                            $(this).closest('tr').remove();
                        });
                    });
                </script>
                <h4>Datos</h4>
                <button class="btn nuevo" type="button"><i class="icon-plus"></i> Nuevo</button>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Etiqueta</th>
                            <th>Valor</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($campo->datos) foreach ($campo->datos as $key => $d): ?>
                                <tr>
                                    <td><input type="text" name="datos[<?= $key ?>][etiqueta]" value="<?= $d->etiqueta ?>" /></td>
                                    <td><input class="input-small" type="text" name="datos[<?= $key ?>][valor]" value="<?= $d->valor ?>" /></td>           
                                    <td><button type="button" class="btn eliminar"><i class="icon-remove"></i> Eliminar</button></td>
                                </tr>
                            <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <?=$campo->backendExtraFields()?>


    </form>
</div>
<div class="modal-footer">
    <a href="#" data-dismiss="modal" class="btn">Cerrar</a>
    <a href="#" onclick="javascript:$('#formEditarCampo').submit();return false;" class="btn btn-primary">Guardar</a>
</div>