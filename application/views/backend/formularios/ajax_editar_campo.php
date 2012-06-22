<div class="modal-header">
    <button class="close" data-dismiss="modal">×</button>
    <h3>Edición de Campo</h3>
</div>
<div class="modal-body">
    <form id="formEditarCampo" class="ajaxForm" method="POST" action="<?= site_url('backend/formularios/editar_campo_form/' . (isset($campo) ? $campo->id : '')) ?>">
        <div class="validacion"></div>
        <?php if (!isset($campo)): ?>
            <input type="hidden" name="formulario_id" value="<?= $formulario->id ?>" />
            <input type="hidden" name="tipo" value="<?= $tipo ?>" />
        <?php endif; ?>
        <label>Nombre</label>
        <input type="text" name="nombre" value="<?= isset($campo) ? $campo->nombre : '' ?>" />
        <label>Etiqueta</label>
        <input type="text" name="etiqueta" value="<?= isset($campo) ? $campo->etiqueta : '' ?>" />
        <label>Reglas de validación</label>
        <input type="text"
               name="validacion"
               data-provide="typeahead"
               data-mode="multiple"
               data-delimiter="|"
               data-source="[&quot;required&quot;,&quot;rut&quot;,&quot;min_length[num]&quot;,&quot;max_length[num]&quot;,&quot;exact_length[num]&quot;,&quot;greater_than[num]&quot;,&quot;less_than[num]&quot;,&quot;alpha&quot;,&quot;alpha_numeric&quot;,&quot;alpha_dash&quot;,&quot;numeric&quot;,&quot;integer&quot;,&quot;decimal&quot;,&quot;is_natural&quot;,&quot;is_natural_no_zero&quot;,&quot;valid_email&quot;,&quot;valid_emails&quot;,&quot;valid_ip&quot;,&quot;valid_base64&quot;]"
               value="<?= isset($campo) ? implode('|',$campo->validacion) : '' ?>"/>
        <label>Visible solo si (Opcional)</label>
        <select name="dependiente_campo">
            <option value=""></option>
            <?php foreach($formulario->Campos as $c):?>
            <option value="<?=$c->nombre?>" <?=isset($campo) && $campo->dependiente_campo==$c->nombre?'selected':''?>><?=$c->nombre?></option>
            <?php endforeach; ?>
        </select>
        <span>=</span>
        <input type="text" name="dependiente_valor" value="<?=isset($campo)?$campo->dependiente_valor:''?>" />
        <?php if ((isset($campo) && ($campo->tipo=='select' || $campo->tipo=='radio' || $campo->tipo=='checkbox')) ||
                (!isset($campo) && ($tipo == 'select' || $tipo=='radio' || $tipo=='checkbox'))): ?>
            <div class="datos">
                <script type="text/javascript">
                    $(document).ready(function(){
                        $('#formEditarCampo .datos .nuevo').click(function(){
                            var pos=$('#formEditarCampo .datos table tbody tr').size();
                            var html='<tr>';
                            html+='<td><input class="input-small" type="text" name="datos['+pos+'][valor]" /></td>';
                            html+='<td><input type="text" name="datos['+pos+'][etiqueta]" /></td>';
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
                            <th>Valor</th>
                            <th>Etiqueta</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($campo)) foreach($campo->datos as $key=>$d):?>
                        <tr>
                            <td><input class="input-small" type="text" name="datos[<?=$key?>][valor]" value="<?=$d->valor?>" /></td>
                            <td><input type="text" name="datos[<?=$key?>][etiqueta]" value="<?=$d->etiqueta?>" /></td>
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