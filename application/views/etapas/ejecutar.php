
<form method="POST" class="ajaxForm" action="<?=site_url('etapas/ejecutar_form/'.$etapa->id.'/'.$paso)?>">    
    <fieldset>
        <div class="validacion"></div>
        <legend><?=$etapa->Tarea->Pasos[$paso]->Formulario->nombre?></legend>
        <?php foreach($etapa->Tarea->Pasos[$paso]->Formulario->Campos as $c):?>
        <?=$c->display($etapa->Tarea->Pasos[$paso]->modo,$etapa->Tramite->id)?>
        <?php endforeach ?>
        <div class="form-actions">
            <?php if ($paso>0): ?><a class="btn" href="<?=site_url('etapas/ejecutar/'.$etapa->id.'/'.($paso-1))?>"><i class="icon-chevron-left"></i> Volver</a><?php endif; ?>
            <button class="btn btn-primary" type="submit">Siguiente</button>
        </div>
    </fieldset>
</form>