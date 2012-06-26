<div class="row-fluid">
    <div class="span3">
        <div class="well">
            <p>Estado: <?= $etapa->pendiente == 0 ? 'Completado' : 'Pendiente' ?></p>
            <p><?= $etapa->created_at ? 'Inicio: ' . strftime('%c', mysql_to_unix($etapa->created_at)) : '' ?></p>
            <p><?= $etapa->ended_at ? 'Término: ' . strftime('%c', mysql_to_unix($etapa->ended_at)) : '' ?></p>
            <p>Asignado a: <?=!$etapa->usuario_id?'Ninguno':!$etapa->Usuario->registrado?'No registrado':$etapa->Usuario->usuario?></p>
        </div>
    </div>
    <div class="span9">
        <form onsubmit="return false;">    
            <fieldset>
                <div class="validacion"></div>
                <legend><?= $etapa->Tarea->Pasos[$paso]->Formulario->nombre ?></legend>
                <?php foreach ($etapa->Tarea->Pasos[$paso]->Formulario->Campos as $c): ?>
                    <div class="campo" data-id="<?= $c->id ?>" <?= $c->dependiente_campo ? 'data-dependiente-campo=' . $c->dependiente_campo : '' ?> <?= $c->dependiente_valor ? 'data-dependiente-valor=' . $c->dependiente_valor : '' ?> >
                        <?= $c->display($etapa->Tarea->Pasos[$paso]->modo, $etapa->Tramite->id) ?>
                    </div>
                <?php endforeach ?>
                <div class="form-actions">
                    <?php if ($paso > 0): ?><a class="btn" href="<?= site_url('backend/seguimiento/ver_etapa/' . $etapa->id . '/' . ($paso - 1)) ?>"><i class="icon-chevron-left"></i> Volver</a><?php endif; ?>
                    <?php if ($paso + 1 < $etapa->Tarea->Pasos->count()): ?><a class="btn btn-primary" href="<?= site_url('backend/seguimiento/ver_etapa/' . $etapa->id . '/' . ($paso + 1)) ?>">Siguiente</a><?php endif; ?>
                </div>
            </fieldset>
        </form>
    </div>
</div>