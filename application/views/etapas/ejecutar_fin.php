<form method="POST" class="ajaxForm dynaForm" action="<?= site_url('etapas/ejecutar_fin_form/' . $etapa->id.($qs?'?'.$qs:'')) ?>">
    <fieldset>
        <div class="validacion"></div>
        <legend>Paso final</legend>
        <?php if ($tareas_proximas->estado == 'pendiente'): ?>
            <?php foreach ($tareas_proximas->tareas as $t): ?>
                <p>Para confirmar y enviar el formulario a la siguiente etapa (<?= $t->nombre ?>) haga click en Finalizar.</p>
                <?php if ($t->asignacion == 'manual'): ?>
                    <label>Asignar próxima etapa a</label>
                    <select name="usuarios_a_asignar[<?= $t->id ?>]">
                        <?php foreach ($t->getUsuarios($etapa->id) as $u): ?>
                            <option value="<?= $u->id ?>"><?= $u->usuario ?> <?=$u->nombres?'('.$u->nombres.' '.$u->apellido_paterno.')':''?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php elseif($tareas_proximas->estado=='standby'): ?>
            <p>Luego de hacer click en Finalizar este hilo de ejecución quedara detenido momentaneamente hasta que se completen otras etapas.</p>
        <?php elseif($tareas_proximas->estado=='completado'):?>
            <p>Luego de hacer click en Finalizar este trámite quedará completado.</p>
        <?php elseif($tareas_proximas->estado=='sincontinuacion'):?>
            <p>Este trámite no tiene hilo donde continuar.</p>
        <?php endif; ?>


        <div class="form-actions">
            <a class="btn" href="<?= site_url('etapas/ejecutar/' . $etapa->id . '/' . (count($etapa->getPasosEjecutables()) - 1).($qs?'?'.$qs:'')) ?>"><i class="icon-chevron-left"></i> Volver</a>
            <?php if($tareas_proximas->estado!='sincontinuacion'):?><button class="btn btn-success" type="submit"><i class="icon-ok icon-white"></i> Finalizar</button><?php endif?>
        </div>
    </fieldset>
</form>