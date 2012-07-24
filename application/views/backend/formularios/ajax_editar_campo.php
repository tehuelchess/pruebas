<script type="text/javascript">
    $(document).ready(function(){
        
        //Funcionalidad del llenado de nombre usando el boton de asistencia
        $("#formEditarCampo .asistencia .dropdown-menu a").click(function(){
            var nombre=$(this).text();
            $("#formEditarCampo input[name=nombre]").val(nombre);
        });
        
        //Llenamos el select box de dependientes
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
        <input type="text" name="etiqueta" value="<?= $campo->etiqueta ?>" />
        <label>Nombre</label>

        <input type="text" name="nombre" value="<?= $campo->nombre ?>" />
        <div class="btn-group asistencia" style="display: inline-block; vertical-align: top;">
            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-th-list"></i><span class="caret"></span></a>
            <ul class="dropdown-menu">
                <?php foreach ($formulario->Proceso->getCampos() as $c): ?>
                    <li><a href="#"><?= $c->nombre ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>



        <?php if ($campo->requiere_readonly): ?>
            <label><input type="checkbox" name="readonly" value="1" /> Solo lectura</label>
        <?php endif; ?>
        <?php if ($campo->requiere_validacion): ?>
            <label>Reglas de validación</label>
            <input type="text"
                   name="validacion"
                   data-provide="typeahead"
                   data-mode="multiple"
                   data-delimiter="|"
                   data-source="[&quot;required&quot;,&quot;rut&quot;,&quot;min_length[num]&quot;,&quot;max_length[num]&quot;,&quot;exact_length[num]&quot;,&quot;greater_than[num]&quot;,&quot;less_than[num]&quot;,&quot;alpha&quot;,&quot;alpha_numeric&quot;,&quot;alpha_dash&quot;,&quot;numeric&quot;,&quot;integer&quot;,&quot;decimal&quot;,&quot;is_natural&quot;,&quot;is_natural_no_zero&quot;,&quot;valid_email&quot;,&quot;valid_emails&quot;,&quot;valid_ip&quot;,&quot;valid_base64&quot;]"
                   value="<?= isset($campo) ? implode('|', $campo->validacion) : '' ?>"/>
               <?php endif; ?>
        <label>Visible solo si</label>
        <select name="dependiente_campo">
            <option value=""></option>
        </select>
        <span>=</span>
        <input type="text" name="dependiente_valor" value="<?= isset($campo) ? $campo->dependiente_valor : '' ?>" />
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


    </form>
</div>
<div class="modal-footer">
    <a href="#" data-dismiss="modal" class="btn">Cerrar</a>
    <a href="#" onclick="javascript:$('#formEditarCampo').submit();return false;" class="btn btn-primary">Guardar</a>
</div>