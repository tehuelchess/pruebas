<form class="form-horizontal dynaForm" onsubmit="return false;">    
    <fieldset>
        <div class="validacion"></div>
        <legend><?= $paso->Formulario->nombre ?></legend>
        <?php foreach ($paso->Formulario->Campos as $c): ?>
            <div class="control-group campo" data-id="<?= $c->id ?>" <?= $c->dependiente_campo ? 'data-dependiente-campo=' . $c->dependiente_campo : '' ?> <?= $c->dependiente_valor ? 'data-dependiente-valor=' . $c->dependiente_valor : '' ?> data-readonly="<?=$paso->modo=='visualizacion' || $c->readonly?>" >
                <?= $c->displayConDatoSeguimiento($etapa->id, 'visualizacion') ?>
            </div>
        <?php endforeach ?>
        <div class="form-actions">
            <?php if ($secuencia > 0): ?><a class="btn" href="<?= site_url('etapas/ver/' . $etapa->id . '/' . ($secuencia - 1)) ?>"><i class="icon-chevron-left"></i> Volver</a><?php endif; ?>
            <?php if ($secuencia + 1 < count($etapa->getPasosEjecutables())): ?><a class="btn btn-primary" href="<?= site_url('etapas/ver/' . $etapa->id . '/' . ($secuencia + 1)) ?>">Siguiente</a><?php endif; ?>
        </div>
    </fieldset>
</form>