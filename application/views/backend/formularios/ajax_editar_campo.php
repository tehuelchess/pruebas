<div class="modal-header">
    <button class="close" data-dismiss="modal">×</button>
    <h3>Edición de Campo</h3>
</div>
<div class="modal-body">
    <form id="formEditarCampo" class="ajaxForm" method="POST" action="<?= site_url('backend/formularios/editar_campo_form/' . (isset($campo) ? $campo->id : '')) ?>">
        <div class="validacion"></div>
        <?php if (!isset($campo)): ?>
            <input type="hidden" name="formulario_id" value="<?= $formulario_id ?>" />
            <input type="hidden" name="tipo" value="<?= $tipo ?>" />
        <?php endif; ?>
        <label>Nombre</label>
        <input type="text" name="nombre" value="<?= isset($campo) ? $campo->nombre : '' ?>" />
        <label>Etiqueta</label>
        <input type="text" name="etiqueta" value="<?= isset($campo) ? $campo->etiqueta : '' ?>" />
        <label>Reglas de validación</label>
        <input type="text" name="validacion" value="<?= isset($campo) ? $campo->validacion : '' ?>" />
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
                        <?php if(isset($campo)) foreach($campo->getDatosFromJSON() as $key=>$d):?>
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