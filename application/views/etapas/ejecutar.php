<form method="POST" class="ajaxForm dynaForm" action="<?=site_url('etapas/ejecutar_form/'.$etapa->id.'/'.$secuencia.($qs?'?'.$qs:''))?>">  
    <input type="hidden" name="csrf" />
    <fieldset>
        <div class="validacion"></div>
        <legend><?=$paso->Formulario->nombre?></legend>
        <?php foreach($paso->Formulario->Campos as $c):?>
            <div class="campo" data-id="<?=$c->id?>" <?= $c->dependiente_campo ? 'data-dependiente-campo="' . $c->dependiente_campo.'" data-dependiente-valor="' . $c->dependiente_valor .'" data-dependiente-tipo="' . $c->dependiente_tipo.'"' : '' ?> >
            <?=$c->displayConDato($etapa->id,$paso->modo)?>
            </div>
        <?php endforeach ?>
        <div class="form-actions">
            <?php if ($secuencia>0): ?><a class="btn" href="<?=site_url('etapas/ejecutar/'.$etapa->id.'/'.($secuencia-1).($qs?'?'.$qs:''))?>"><i class="icon-chevron-left"></i> Volver</a><?php endif; ?>
            <button class="btn btn-primary" type="submit">Siguiente <i class="icon-chevron-right icon-white"></i></button>
        </div>
    </fieldset>
</form>