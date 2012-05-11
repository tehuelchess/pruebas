<?php if ($paso>0): ?>
<a href="<?=site_url('etapas/ejecutar/'.$etapa->id.'/'.($paso-1))?>">Paso anterior</a>
<?php endif; ?>
<form method="POST" class="ajaxForm" action="<?=site_url('etapas/ejecutar_form/'.$etapa->id.'/'.$paso)?>">    
    <fieldset>
        <div class="validacion"></div>
        <legend><?=$etapa->Tarea->Pasos[$paso]->Formulario->nombre?></legend>
        <?php foreach($etapa->Tarea->Pasos[$paso]->Formulario->Campos as $c):?>
        <?=$c->display($etapa->Tarea->Pasos[$paso]->modo,$etapa->Tramite->id)?>
        <?php endforeach ?>
        <div class="form-actions">
            <button class="btn" type="submit"><?=$etapa->Tarea->Pasos->count()-1==$paso?'Finalizar':'Siguiente'?></button>
        </div>
    </fieldset>
</form>