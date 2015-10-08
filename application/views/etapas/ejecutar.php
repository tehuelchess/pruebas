<?php if($etapa->Tarea->vencimiento):?>
<div class="alert alert-warning">Atención. Esta etapa <?=$etapa->getFechaVencimientoAsString()?>.</div>
<?php endif ?>
<form method="POST" class="ajaxForm dynaForm form-horizontal" action="<?=site_url('etapas/ejecutar_form/'.$etapa->id.'/'.$secuencia.($qs?'?'.$qs:''))?>">
    <input type="hidden" name="_method" value="post">
     <div class="validacion"></div>
    <fieldset>
        <legend><?=$paso->Formulario->nombre?></legend>
        <?php foreach($paso->Formulario->Campos as $c):?>
            <div class="campo control-group" data-id="<?=$c->id?>" <?= $c->dependiente_campo ? 'data-dependiente-campo="' . $c->dependiente_campo.'" data-dependiente-valor="' . $c->dependiente_valor .'" data-dependiente-tipo="' . $c->dependiente_tipo.'" data-dependiente-relacion="'.$c->dependiente_relacion.'"' : '' ?> data-readonly="<?=$paso->modo=='visualizacion' || $c->readonly?>" >
                <?php
                    if($c->getExtra()){
                        if(preg_match('(@{2}\w+)',$c->getExtra()->ws,$matches)) {
                            $urlInnerVar = $matches[0];
                            $simpleVarValue = Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId(substr($matches[0],2), $etapa->id);
                            $c->setExtra(array("ws" => str_replace($matches[0], $simpleVarValue->valor,$c->getExtra()->ws)));
                        }
                    }
                ?>
                <?=$c->displayConDatoSeguimiento($etapa->id,$paso->modo)?>
            </div>
        <?php endforeach ?>
        <div class="form-actions">
            <?php if ($secuencia>0): ?><a class="btn" href="<?=site_url('etapas/ejecutar/'.$etapa->id.'/'.($secuencia-1).($qs?'?'.$qs:''))?>"><i class="icon-chevron-left"></i> Volver</a><?php endif; ?>
            <button class="btn btn-primary" type="submit">Siguiente <i class="icon-chevron-right icon-white"></i></button>
        </div>
    </fieldset>
</form>