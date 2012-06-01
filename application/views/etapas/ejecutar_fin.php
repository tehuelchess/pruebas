<form method="POST" class="ajaxForm" action="<?=site_url('etapas/ejecutar_fin_form/'.$etapa->id)?>">    
    <fieldset>
        <div class="validacion"></div>
        <legend>Finalizar</legend>
        <p>Su formulario sera enviado a la siguiente etapa.</p>
        <?php if($etapa->Tramite->getTareaProxima()->asignacion=='manual'):?>
        <label>Asignar próxima etapa a</label>
        <select name="usuario_id">
            <?php foreach($etapa->Tramite->getTareaProxima()->getUsuarios() as $u):?>
            <option value="<?=$u->id?>"><?=$u->usuario?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
        <div class="form-actions">
            <a class="btn" href="<?=site_url('etapas/ejecutar/'.$etapa->id.'/'.($etapa->Tarea->Pasos->count()-1))?>"><i class="icon-chevron-left"></i> Volver</a>
            <button class="btn btn-primary" type="submit">Finalizar</button>
        </div>
    </fieldset>
</form>