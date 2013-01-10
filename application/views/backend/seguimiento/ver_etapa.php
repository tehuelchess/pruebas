<ul class="breadcrumb">
    <li><a href="<?=site_url('backend/seguimiento')?>">Listado de Trámites</a> <span class="divider">/</span></li>
    <li><a href="<?=site_url('backend/seguimiento/ver/'.$etapa->tramite_id)?>"><?=$etapa->Tramite->Proceso->nombre?></a> <span class="divider">/</span></li>
    <li><a href="<?=site_url('backend/seguimiento/ver_etapa/'.$etapa->id)?>"><?=$etapa->Tarea->nombre?></a> <span class="divider">/</span></li>
    <li class="active">Paso <?=$paso+1?></li>
</ul>

<div class="row-fluid">
    <div class="span3">
        <div class="well">
            <p>Estado: <?= $etapa->pendiente == 0 ? 'Completado' : 'Pendiente' ?></p>
            <p><?= $etapa->created_at ? 'Inicio: ' . strftime('%c', mysql_to_unix($etapa->created_at)) : '' ?></p>
            <p><?= $etapa->ended_at ? 'Término: ' . strftime('%c', mysql_to_unix($etapa->ended_at)) : '' ?></p>
            <script>
                $(document).ready(function(){
                    $("#reasignarLink").click(function(){
                        $("#reasignarForm").show();
                        return false;
                    });
                });
            </script>
            <p>Asignado a: <?=!$etapa->usuario_id?'Ninguno':!$etapa->Usuario->registrado?'No registrado':$etapa->Usuario->usuario?> <?php if($etapa->pendiente):?>(<a id="reasignarLink" href="<?=site_url('seguimiento/reasignar')?>">Reasignar</a>)<?php endif?></p>
            <form id="reasignarForm" method="POST" action="<?=site_url('backend/seguimiento/reasignar_form/'.$etapa->id)?>" class="ajaxForm hide">
                <div class="validacion"></div>
                <label>¿A quien deseas asignarle esta etapa?</label>
                <select name="usuario_id">
                    <?php foreach($etapa->Tarea->getUsuarios() as $u):?>
                    <option value="<?=$u->id?>" <?=$u->id==$etapa->usuario_id?'selected':''?>><?=$u->usuario?></option>
                    <?php endforeach?>
                </select>
                <button class="btn btn-primary" type="submit">Reasignar</button>
            </form>
        </div>
    </div>
    <div class="span9">
        <form class="dynaForm" onsubmit="return false;">    
            <fieldset>
                <div class="validacion"></div>
                <legend><?= $etapa->Tarea->Pasos[$paso]->Formulario->nombre ?></legend>
                <?php foreach ($etapa->Tarea->Pasos[$paso]->Formulario->Campos as $c): ?>
                    <div class="campo" data-id="<?= $c->id ?>" <?= $c->dependiente_campo ? 'data-dependiente-campo=' . $c->dependiente_campo : '' ?> <?= $c->dependiente_valor ? 'data-dependiente-valor=' . $c->dependiente_valor : '' ?> >
                        <?= $c->displayConDatoSeguimiento($etapa->id,$etapa->Tarea->Pasos[$paso]->modo) ?>
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