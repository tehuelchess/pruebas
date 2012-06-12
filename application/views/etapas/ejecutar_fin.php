<form method="POST" class="ajaxForm" action="<?=site_url('etapas/ejecutar_fin_form/'.$etapa->id)?>">    
    <fieldset>
        <div class="validacion"></div>
        <legend>Finalizar</legend>
        <?php if($etapa->Tarea->final):?>
            <p>Este trámite será completado.</p>
        <?php else: ?>
            <?php if($tareas_proximas):?>
                <?php foreach($tareas_proximas as $t):?>
                    <p>Su formulario sera enviado a la siguiente etapa: <?=$t->nombre?></p>
                    <?php if($t->asignacion=='manual'):?>
                    <label>Asignar próxima etapa a</label>
                    <select name="usuarios_a_asignar[<?=$t->id?>]">
                        <?php foreach($t->getUsuarios() as $u):?>
                        <option value="<?=$u->id?>"><?=$u->usuario?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                    <p>Este hilo de ejecución quedará en stand-by.</p>
            <?php endif; ?>
        <?php endif; ?>
        

        <div class="form-actions">
            <a class="btn" href="<?=site_url('etapas/ejecutar/'.$etapa->id.'/'.($etapa->Tarea->Pasos->count()-1))?>"><i class="icon-chevron-left"></i> Volver</a>
            <button class="btn btn-primary" type="submit">Finalizar</button>
        </div>
    </fieldset>
</form>