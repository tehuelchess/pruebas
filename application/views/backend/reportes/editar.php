<ul class="breadcrumb">
    <li>
        <a href="<?= site_url('backend/reportes') ?>">Gestión</a> <span class="divider">/</span>
    </li>
    <li><a href="<?=site_url('backend/reportes/listar/'.$proceso->id)?>"><?= $proceso->nombre ?></a></li> <span class="divider">/</span>
    <li class="active"><?=$reporte->nombre?></li>
</ul>


<form class="ajaxForm" method="POST" action="<?=site_url('backend/reportes/editar_form/'.($edit?$reporte->id:''))?>">
    <fieldset>
        <legend>Crear Reporte</legend>
        <div class="validacion"></div>
        <?php if(!$edit):?>
        <input type="hidden" name="proceso_id" value="<?=$proceso->id?>" />
        <?php endif; ?>
        <label>Nombre</label>
        <input type="text" name="nombre" value="<?=$edit?$reporte->nombre:''?>" />
        <label>Campos</label>
        <select name="campos[]" style="height: 240px;" multiple>
            <?php foreach($proceso->getNombresDeCampos() as $c):?>
            <option value="<?=$c?>" <?=$edit && in_array($c,$reporte->campos)?'selected':''?>><?=$c?></option>
            <?php endforeach; ?>
        </select>
        
        
        <div class="form-actions">
            <a class="btn" href="<?=site_url('backend/reportes/listar/'.$proceso->id)?>">Cancelar</a>
            <input class="btn btn-primary" type="submit" value="Guardar" />
        </div>
    </fieldset>
</form>




</div>