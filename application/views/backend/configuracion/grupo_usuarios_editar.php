<div class="row-fluid">

    <div class="span3">
        <?php $this->load->view('backend/configuracion/sidebar') ?>
    </div>
    <div class="span9">
        <ul class="breadcrumb">
            <li>
                <a href="<?= site_url('backend/configuracion') ?>">Configuración</a> <span class="divider">/</span>
            </li>
            <li>
                <a href="<?= site_url('backend/configuracion/grupos_usuarios') ?>">Grupos de Usuarios</a> <span class="divider">/</span>
            </li>
            <li class="active"><?= isset($grupo_usuarios) ?$grupo_usuarios->nombre:'Crear' ?></li>
        </ul>

        <form class="ajaxForm" method="post" action="<?= site_url('backend/configuracion/grupo_usuarios_editar_form/' . (isset($grupo_usuarios)?$grupo_usuarios->id:'')) ?>">
            <fieldset>
                <legend>Editar grupo de usuario</legend>
                <div class="validacion"></div>
                <label>Nombre</label>
                <input type="text" name="nombre" value="<?=isset($grupo_usuarios)?$grupo_usuarios->nombre:''?>"/>
                <label>Este grupo lo componen</label>
                <!--            
                <select multiple readonly>
                    <?php foreach($grupo_usuarios->Usuarios as $u): ?>
                    <option><?=$u->usuario?></option>
                    <?php endforeach; ?>
                </select>
                -->
                <select class="chosen" name="usuarios[]" data-placeholder="Seleccione los usuarios" multiple>
                    <?php foreach($usuarios as $g): ?>
                    <option value="<?=$g->id?>" <?=isset($grupo_usuarios) && $grupo_usuarios->hasUsuario($g->id)?'selected':''?>><?=$g->usuario?> (<?=$g->nombre?> <?=$g->apellidos?>)</option>
                    <?php endforeach; ?>
                </select>
                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Guardar</button>
                    <a class="btn" href="<?=site_url('backend/configuracion/grupos_usuarios')?>">Cancelar</a>
                </div>
            </fieldset>
        </form>

    </div>
</div>