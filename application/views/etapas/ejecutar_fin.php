<form method="POST" class="ajaxForm" action="<?= site_url('etapas/ejecutar_fin_form/' . $etapa->id) ?>">
    <fieldset>
        <div class="validacion"></div>
        <legend>Finalizar</legend>
        <?php if ($tareas_proximas->estado == 'pendiente'): ?>
            <?php foreach ($tareas_proximas->tareas as $t): ?>
                <p>Su formulario sera enviado a la siguiente etapa: <?= $t->nombre ?></p>
                <?php if ($t->asignacion == 'manual'): ?>
                    <label>Asignar próxima etapa a</label>
                    <select name="usuarios_a_asignar[<?= $t->id ?>]">
                        <?php foreach ($t->getUsuarios() as $u): ?>
                            <option value="<?= $u->id ?>"><?= $u->usuario ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php elseif($tareas_proximas->estado=='standby'): ?>
            <p>Este hilo de ejecución quedara detenido momentaneamente hasta que se completen otras etapas.</p>
        <?php elseif($tareas_proximas->estado=='completado'):?>
            <p>Este tramite será finalizado.</p>
        <?php elseif($tareas_proximas->estado=='sincontinuacion'):?>
            <p>Este tramite no se puede continuar.</p>
        <?php endif; ?>


        <div class="form-actions">
            <a class="btn" href="<?= site_url('etapas/ejecutar/' . $etapa->id . '/' . ($etapa->Tarea->Pasos->count() - 1)) ?>"><i class="icon-chevron-left"></i> Volver</a>
            <?php if($tareas_proximas->estado!='sincontinuacion'):?><button class="btn btn-primary" type="submit">Finalizar</button><?php endif ?>
        </div>
    </fieldset>
</form>