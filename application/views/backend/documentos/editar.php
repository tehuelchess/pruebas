<ul class="breadcrumb">
    <li>
        <a href="<?= site_url('backend/procesos') ?>">Listado de Procesos</a> <span class="divider">/</span>
    </li>
    <li class="active"><?= $proceso->nombre ?></li>
</ul>

<ul class="nav nav-tabs">
    <li><a href="<?= site_url('backend/procesos/editar/' . $proceso->id) ?>">Diseñador</a></li>
    <li><a href="<?= site_url('backend/formularios/listar/' . $proceso->id) ?>">Formularios</a></li>
    <li class="active"><a href="<?= site_url('backend/documentos/listar/' . $proceso->id) ?>">Documentos</a></li>
    <li><a href="<?= site_url('backend/acciones/listar/' . $proceso->id) ?>">Acciones</a></li>
</ul>


<form class="ajaxForm" method="POST" action="<?=site_url('backend/documentos/editar_form/'.($edit?$documento->id:''))?>">
    <fieldset>
        <legend>Crear Documento</legend>
        <div class="validacion"></div>
        <?php if(!$edit):?>
        <input type="hidden" name="proceso_id" value="<?=$proceso->id?>" />
        <?php endif; ?>
        <label>Nombre</label>
        <input type="text" name="nombre" value="<?=$edit?$documento->nombre:''?>" />
        <label>Contenido</label>
        <textarea name="contenido" class="input-xxlarge" rows="20"><?=$edit?$documento->contenido:''?></textarea>
        
        
        <div class="form-actions">
            <a class="btn" href="<?=site_url('backend/documentos/listar/'.$proceso->id)?>">Cancelar</a>
            <input class="btn btn-primary" type="submit" value="Guardar" />
        </div>
    </fieldset>
</form>




</div>