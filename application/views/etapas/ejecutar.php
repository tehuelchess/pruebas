<link rel="stylesheet" href= "<?= base_url('assets/calendar/css/calendar.css') ?>" >
<script src= "<?= base_url('/assets/js/jquery-ui/js/jquery-ui.js') ?>"></script>
<script src= "<?= base_url('/assets/js/moment.min.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/calendar/components/underscore/underscore-min.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/calendar/components/jstimezonedetect/jstz.min.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/calendar/js/language/es-CO.js') ?>"></script>
<script src="<?= base_url() ?>assets/js/bootstrap-datetimepicker.min.js"></script>
<script src= "<?= base_url('/assets/calendar/js/moment-2.2.1.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/calendar/js/calendar.js?v=0.2') ?>"></script>
<script src="<?= base_url() ?>assets/js/collapse.js"></script>
<script src="<?= base_url() ?>assets/js/transition.js"></script>


<?php if($etapa->Tarea->vencimiento):?>
<div class="alert alert-warning">Atención. Esta etapa <?=$etapa->getFechaVencimientoAsString()?>.</div>
<?php endif ?>
<form method="POST" class="ajaxForm dynaForm form-horizontal" action="<?=site_url('etapas/ejecutar_form/'.$etapa->id.'/'.$secuencia.($qs?'?'.$qs:''))?>">
    <input type="hidden" name="_method" value="post">
     <div class="validacion"></div>
    <fieldset>
        <legend><?=$paso->Formulario->nombre?></legend>
        <?php foreach($paso->Formulario->Campos as $c):?>
            <?php
             ?>
            <div class="campo control-group" data-id="<?=$c->id?>" <?= $c->dependiente_campo ? 'data-dependiente-campo="' . $c->dependiente_campo.'" data-dependiente-valor="' . $c->dependiente_valor .'" data-dependiente-tipo="' . $c->dependiente_tipo.'" data-dependiente-relacion="'.$c->dependiente_relacion.'"' : '' ?> style="display: <?= $c->isCurrentlyVisible($etapa->id)? 'block' : 'none'?>;" data-readonly="<?=$paso->modo=='visualizacion' || $c->readonly?>" >
            <?=$c->displayConDatoSeguimiento($etapa->id,$paso->modo)?>
            </div>
        <?php endforeach ?>
        <div class="form-actions">
            <?php if ($secuencia>0): ?><a class="btn" href="<?=site_url('etapas/ejecutar/'.$etapa->id.'/'.($secuencia-1).($qs?'?'.$qs:''))?>"><i class="icon-chevron-left"></i> Volver</a><?php endif; ?>
            <button class="btn btn-primary" type="submit">Siguiente <i class="icon-chevron-right icon-white"></i></button>
        </div>
    </fieldset>
</form>
<div id="modalcalendar" class="modal hide fade modalconfg modcalejec"></div>
<input type="hidden" id="urlbase" value="<?= base_url() ?>" />