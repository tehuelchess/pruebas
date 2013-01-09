<script type="text/javascript">
    $(document).ready(function(){
        $(".chosen").chosen();
        
        $("[rel=tooltip]").tooltip();
        
        $(".datepicker")
        .datepicker({
            format: "dd-mm-yyyy",
            weekStart: 1,
            autoclose: true,
            language: "es"
        })
        
        $('#formEditarTarea .nav-tabs a').click(function (e) {
            e.preventDefault();
            $(this).tab('show');
        });
        
        //Permite borrar pasos
        $(".tab-pasos").on("click",".delete",function(){
            $(this).closest("tr").remove();
            return false;
        });
        //Permite agregar nuevos pasos
        $(".tab-pasos .form-agregar-paso button").click(function(){
            var $form=$(".tab-pasos .form-agregar-paso");
            
            var pos=1+$(".tab-pasos table tbody tr").size();
            var formularioId=$form.find(".pasoFormulario option:selected").val();
            var formularioNombre=$form.find(".pasoFormulario option:selected").text();
            var modo=$form.find(".pasoModo option:selected").val();
            var regla=$form.find(".pasoRegla").val();
            
            var html="<tr>";
            html+="<td>"+pos+"</td>";
            html+='<td><a title="Editar" target="_blank" href="'+site_url+'backend/formularios/editar/'+formularioId+'">'+formularioNombre+'</td>';
            html+="<td>"+regla+"</td>";
            html+="<td>"+modo+"</td>";
            html+='<td>';
            html+='<input type="hidden" name="pasos['+pos+'][formulario_id]" value="'+formularioId+'" />';
            html+='<input type="hidden" name="pasos['+pos+'][regla]" value="'+regla+'" />';
            html+='<input type="hidden" name="pasos['+pos+'][modo]" value="'+modo+'" />';
            html+='<a class="delete" title="Eliminar" href="#"><i class="icon-remove"></i></a>';
            html+='</td>';
            html+="</tr>";
            
            $(".tab-pasos table tbody").append(html);
            
            return false;
        });
        //Permite que los pasos sean reordenables
        $(".tab-pasos table tbody").sortable({
            revert: true,
            stop: function(){
                //Reordenamos las posiciones
                $(this).find("tr").each(function(i,e){
                    $(e).find("td:nth-child(1)").text(i+1);
                    $(e).find("input[name*=formulario_id]").attr("name","pasos["+(i+1)+"][formulario_id]");
                    $(e).find("input[name*=regla]").attr("name","pasos["+(i+1)+"][regla]");
                    $(e).find("input[name*=modo]").attr("name","pasos["+(i+1)+"][modo]");
                });
            }
        });
        
        
        //Permite borrar eventos
        $(".tab-eventos").on("click",".delete",function(){
            $(this).closest("tr").remove();
            return false;
        });
        //Permite agregar nuevos eventos
        $(".tab-eventos .form-agregar-evento button").click(function(){
            var $form=$(".tab-eventos .form-agregar-evento");
            
            var pos=1+$(".tab-eventos table tbody tr").size();
            var accionId=$form.find(".eventoAccion option:selected").val();
            var accionNombre=$form.find(".eventoAccion option:selected").text();
            var regla=$form.find(".eventoRegla").val();
            var instante=$form.find(".eventoInstante option:selected").val()
            
            var html="<tr>";
            html+="<td>"+pos+"</td>";
            html+='<td><a title="Editar" target="_blank" href="'+site_url+'backend/acciones/editar/'+accionId+'">'+accionNombre+'</td>';
            html+="<td>"+regla+"</td>";
            html+="<td>"+instante+"</td>";
            html+='<td>';
            html+='<input type="hidden" name="eventos['+pos+'][accion_id]" value="'+accionId+'" />';
            html+='<input type="hidden" name="eventos['+pos+'][regla]" value="'+regla+'" />';
            html+='<input type="hidden" name="eventos['+pos+'][instante]" value="'+instante+'" />';
            html+='<a class="delete" title="Eliminar" href="#"><i class="icon-remove"></i></a>';
            html+='</td>';
            html+="</tr>";
            
            $(".tab-eventos table tbody").append(html);
            
            return false;
        });
        
        //$("#modalEditarTarea form input[name=socket_id_emisor]").val(socketId);
        //$("#modalEditarTarea .botonEliminar").attr("href",function(i,href){return href+"?socket_id_emisor="+socketId;})
    });
</script>


<div class="modal-header">
    <a class="close" data-dismiss="modal">×</a>
    <h3>Editar Tarea</h3>
</div>
<div class="modal-body" >
    <form id="formEditarTarea" class="ajaxForm" method="POST" action="<?= site_url('backend/procesos/editar_tarea_form/' . $tarea->id) ?>">
        <div class="validacion"></div>

        <div class="tabbable">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab1">Definición</a></li>
                <li><a href="#tab2">Asignación</a></li>
                <li><a href="#tab3">Usuarios</a></li>
                <li><a href="#tab4">Pasos</a></li>
                <li><a href="#tab5">Eventos</a></li>
                <li><a href="#tab6">Vencimiento</a></li>
                <li><a href="#tab7">Otros</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab1">
                    <div class="row-fluid">
                        <div class="span6">
                            <label>Nombre</label>
                            <input name="nombre" type="text" value="<?= $tarea->nombre ?>" />
                            <label class="checkbox"><input name="inicial" value="1" type="checkbox" <?= $tarea->inicial ? 'checked' : '' ?>> Tarea Inicial</label>
                            <label class="checkbox"><input name="final" value="1" type="checkbox" <?= $tarea->final ? 'checked' : '' ?>> Tarea Final</label>
                        </div>
                        <div class="span6">
                            <script>
                                $(document).ready(function(){
                                    $("input[name=activacion]").change(function(){
                                        if($("input[name=activacion]:checked").val()=='entre_fechas')
                                            $("#activacionEntreFechas").show();
                                        else
                                            $("#activacionEntreFechas").hide();  
                                    }).change();
                                
                                });
                            </script>
                            <label class="radio"><input name="activacion" value="si" type="radio" <?= $tarea->activacion == 'si' ? 'checked' : '' ?>>Tarea activada</label>
                            <label class="radio"><input name="activacion" value="entre_fechas" type="radio" <?= $tarea->activacion == 'entre_fechas' ? 'checked' : '' ?>>Tarea activa entre fechas</label>
                            <div id="activacionEntreFechas" class="hide" style="margin-left: 20px;">
                                <label>Fecha inicial</label>
                                <input class="datepicker" rel="tooltip" title="Deje el campo en blanco para no considerar una fecha inicial" type="text" name="activacion_inicio" value="<?= $tarea->activacion_inicio ? date('d-m-Y', $tarea->activacion_inicio) : '' ?>" placeholder="DD-MM-AAAA" />
                                <label>Fecha final</label>
                                <input class="datepicker" rel="tooltip" title="Deje el campo en blanco para no considerar una fecha final" type="text" name="activacion_fin" value="<?= $tarea->activacion_fin ? date('d-m-Y', $tarea->activacion_fin) : '' ?>" placeholder="DD-MM-AAAA" />
                            </div>
                            <label class="radio"><input name="activacion" value="no" type="radio" <?= $tarea->activacion == 'no' ? 'no' : '' ?>>Tarea desactivada</label>
                        </div>
                    </div>



                </div>
                <div class="tab-pane" id="tab2">
                    <script type="text/javascript">
                        $(document).ready(function(){
                            $("input[name=asignacion]").click(function(){
                                if(this.value=="usuario")
                                    $("#optionalAsignacionUsuario").removeClass("hide");
                                else
                                    $("#optionalAsignacionUsuario").addClass("hide");
                            });
                        });
                    </script>
                    <label>Regla de asignación</label>
                    <label class="radio" rel="tooltip" title="Los usuarios se asignan en forma ciclica. Se van turnando dentro del grupo de usuarios en forma circular."><input type="radio" name="asignacion" value="ciclica" <?= $tarea->asignacion == 'ciclica' ? 'checked' : '' ?> /> Cíclica</label>
                    <label class="radio" rel="tooltip" title="Al finalizar la tarea anterior, se le pregunta al usuario a quien se le va a asignar esta tarea."><input type="radio" name="asignacion" value="manual" <?= $tarea->asignacion == 'manual' ? 'checked' : '' ?> /> Manual</label>
                    <label class="radio" rel="tooltip" title="La tarea queda sin asignar, y los usuarios mismos deciden asignarsela segun corresponda."><input type="radio" name="asignacion" value="autoservicio" <?= $tarea->asignacion == 'autoservicio' ? 'checked' : '' ?> /> Auto Servicio</label>
                    <label class="radio" rel="tooltip" title="Ingresar el id de usuario a quien se le va asignar. Se puede ingresar una variable que haya almacenado esta información. Ej: @@usuario_inical"><input type="radio" name="asignacion" value="usuario" <?= $tarea->asignacion == 'usuario' ? 'checked' : '' ?> /> Usuario</label>
                    <div id="optionalAsignacionUsuario" class="<?= $tarea->asignacion == 'usuario' ? '' : 'hide' ?>">
                        <input type="text" name="asignacion_usuario" value="<?= $tarea->asignacion_usuario ?>" />
                    </div>
                    <br />
                    <label><input type="checkbox" name="asignacion_notificar" value="1" <?= $tarea->asignacion_notificar ? 'checked' : '' ?> /> Notificar vía correo electrónico al usuario asignado.</label>
                </div>
                <div class="tab-pane" id="tab3">
                    <script type="text/javascript">
                        $(document).ready(function(){
                            $("input[name=acceso_modo]").change(function(){
                                if(this.value=="grupos_usuarios")
                                    $("#optionalGruposUsuarios").removeClass("hide");
                                else
                                    $("#optionalGruposUsuarios").addClass("hide");
                            });
                        });
                    </script>
                    <label><input type="radio" name="acceso_modo" value="publico" <?= $tarea->acceso_modo == 'publico' ? 'checked' : '' ?> /> Cualquier persona puede acceder.</label>
                    <label><input type="radio" name="acceso_modo" value="registrados" <?= $tarea->acceso_modo == 'registrados' ? 'checked' : '' ?> /> Sólo los usuarios registrados.</label>
                    <label><input type="radio" name="acceso_modo" value="grupos_usuarios" <?= $tarea->acceso_modo == 'grupos_usuarios' ? 'checked' : '' ?> /> Sólo los siguientes grupos de usuarios pueden acceder.</label>
                    <div id="optionalGruposUsuarios" style="height: 300px;" class="<?= $tarea->acceso_modo == 'grupos_usuarios' ? '' : 'hide' ?>">
                        <select name="grupos_usuarios[]" class="chosen" multiple>
                            <?php foreach ($tarea->Proceso->Cuenta->GruposUsuarios as $g): ?>
                                <option value="<?= $g->id ?>" <?= $tarea->hasGrupoUsuarios($g->id) ? 'selected="selected"' : '' ?>><?= $g->nombre ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="tab-pasos tab-pane" id="tab4">

                    <table class="table">
                        <thead>
                            <tr class="form-agregar-paso">
                                <td></td>
                                <td>
                                    <select class="pasoFormulario">
                                        <?php foreach ($formularios as $f): ?>
                                            <option value="<?= $f->id ?>"><?= $f->nombre ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input class="pasoRegla" type="text" placeholder="Escribir regla condición aquí" />
                                </td>
                                <td>
                                    <select class="pasoModo input-small">
                                        <option value="edicion">Edición</option>
                                        <option value="visualizacion">Visualización</option>
                                    </select>
                                </td>
                                <td>
                                    <button type="button" class="btn" title="Agregar"><i class="icon-plus"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <th>#</th>
                                <th>Formulario</th>
                                <th>Condición</th>
                                <th>Modo</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tarea->Pasos as $key => $p): ?>
                                <tr>
                                    <td><?= $key + 1 ?></td>
                                    <td><a title="Editar" target="_blank" href="<?= site_url('backend/formularios/editar/' . $p->Formulario->id) ?>"><?= $p->Formulario->nombre ?></a></td>
                                    <td><?= $p->regla ?></td>
                                    <td><?= $p->modo ?></td>
                                    <td>
                                        <input type="hidden" name="pasos[<?= $key + 1 ?>][formulario_id]" value="<?= $p->formulario_id ?>" />
                                        <input type="hidden" name="pasos[<?= $key + 1 ?>][regla]" value="<?= $p->regla ?>" />
                                        <input type="hidden" name="pasos[<?= $key + 1 ?>][modo]" value="<?= $p->modo ?>" />
                                        <a class="delete" title="Eliminar" href="#"><i class="icon-remove"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="tab-eventos tab-pane" id="tab5">
                    <table class="table">
                        <thead>
                            <tr class="form-agregar-evento">
                                <td></td>
                                <td>
                                    <select class="eventoAccion input-medium">
                                        <?php foreach ($acciones as $f): ?>
                                            <option value="<?= $f->id ?>"><?= $f->nombre ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input class="eventoRegla" type="text" placeholder="Escribir regla condición aquí" />
                                </td>
                                <td>
                                    <select class="eventoInstante input-medium">
                                        <option value="antes">Antes de ejecutar tarea</option>
                                        <option value="despues">Después de finalizar tarea</option>
                                    </select>
                                </td>
                                <td>
                                    <button type="button" class="btn" title="Agregar"><i class="icon-plus"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <th>#</th>
                                <th>Accion</th>
                                <th>Condición</th>
                                <th>Instante</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tarea->Eventos as $key => $p): ?>
                                <tr>
                                    <td><?= $key + 1 ?></td>
                                    <td><a title="Editar" target="_blank" href="<?= site_url('backend/acciones/editar/' . $p->Accion->id) ?>"><?= $p->Accion->nombre ?></a></td>
                                    <td><?= $p->regla ?></td>
                                    <td><?= $p->instante ?></td>
                                    <td>
                                        <input type="hidden" name="eventos[<?= $key + 1 ?>][accion_id]" value="<?= $p->accion_id ?>" />
                                        <input type="hidden" name="eventos[<?= $key + 1 ?>][regla]" value="<?= $p->regla ?>" />
                                        <input type="hidden" name="eventos[<?= $key + 1 ?>][instante]" value="<?= $p->instante ?>" />
                                        <a class="delete" title="Eliminar" href="#"><i class="icon-remove"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane" id="tab6">
                    <script>
                        $(document).ready(function(){
                            $("input[name=vencimiento]").change(function(){
                                if(this.checked)
                                    $("#vencimientoConfig").show();
                                else
                                    $("#vencimientoConfig").hide();
                            }).change();
                        });
                    </script>
                    <label class="checkbox"><input type="checkbox" name="vencimiento" value="1" <?=$tarea->vencimiento?'checked':''?> /> ¿La etapa tiene vencimiento?</label>
                    <div id="vencimientoConfig" class="hide" style="margin-left: 20px;">
                        La etapa se vencera
                        <input type="text" name="vencimiento_valor" class="input-mini" value="<?= $tarea->vencimiento_valor?$tarea->vencimiento_valor:5 ?>" />
                        <select name="vencimiento_unidad" class="input-small">
                            <option value="D" <?= $tarea->vencimiento_unidad == 'D' ? 'selected' : '' ?>>días</option>
                            <option value="W" <?= $tarea->vencimiento_unidad == 'W' ? 'selected' : '' ?>>meses</option>
                            <option value="M" <?= $tarea->vencimiento_unidad == 'M' ? 'selected' : '' ?>>años</option>
                        </select>
                        despues de completada la etapa anterior.
                        <br />
                        <label class="checkbox"><input type="checkbox" name="vencimiento_notificar" value="1" <?=$tarea->vencimiento_notificar?'checked':''?> /> Notificar cuando quede 1 día al siguiente correo:</label>
                         <input style="margin-left: 20px;" type="text" name="vencimiento_notificar_email" placeholder="ejemplo@mail.com" value="<?=$tarea->vencimiento_notificar_email?>" />
                    </div>
                </div>
                <div class="tab-pane" id="tab7">
                    <script type="text/javascript">
                        $(document).ready(function(){
                            $("input[name=almacenar_usuario]").click(function(){
                                if(this.checked)
                                    $("#optionalAlmacenarUsuario").removeClass("hide");
                                else
                                    $("#optionalAlmacenarUsuario").addClass("hide");
                            });
                        });
                    </script>
                    <label><input type="checkbox" name="almacenar_usuario" value="1" <?= $tarea->almacenar_usuario ? 'checked' : '' ?> /> ¿Almacenar el identificador del usuario que lleva a cabo esta tarea?</label>
                    <div id="optionalAlmacenarUsuario" class="<?= $tarea->almacenar_usuario ? '' : 'hide' ?>">
                        <label>Variable</label>
                        <div class="input-prepend">
                            <span class="add-on">@@</span><input type="text" name="almacenar_usuario_variable" value="<?= $tarea->almacenar_usuario_variable ?>" />
                        </div>
                    </div>
                </div>
            </div>
        </div>




    </form>
</div>
<div class="modal-footer">
    <a href="<?= site_url('backend/procesos/eliminar_tarea/' . $tarea->id) ?>" class="btn btn-danger pull-left" onclick="return confirm('¿Esta seguro que desea eliminar esta tarea?')">Eliminar</a>
    <a href="#" data-dismiss="modal" class="btn">Cerrar</a>
    <a href="#" onclick="javascript:$('#formEditarTarea').submit();return false;" class="btn btn-primary">Guardar</a>
</div>
