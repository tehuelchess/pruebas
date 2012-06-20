<script type="text/javascript">
    var formularioId=<?= $formulario->id ?>;
</script>

<ul class="breadcrumb">
    <li>
        <a href="<?= site_url('backend/procesos') ?>">Listado de Procesos</a> <span class="divider">/</span>
    </li>
    <li class="active"><?= $proceso->nombre ?></li>
</ul>

<ul class="nav nav-pills">
    <li><a href="<?= site_url('backend/procesos/editar/' . $proceso->id) ?>">Diseñador</a></li>
    <li class="active"><a href="<?= site_url('backend/formularios/listar/' . $proceso->id) ?>">Formularios</a></li>
    <li><a href="<?= site_url('backend/acciones/listar/' . $proceso->id) ?>">Acciones</a></li>
</ul>

<div id="areaFormulario">

    <div class="btn-toolbar">
        <div class="btn-group">
            <button class="btn" onclick="return agregarCampo(<?= $formulario->id ?>,'text')">Textbox</button>
            <button class="btn" onclick="return agregarCampo(<?= $formulario->id ?>,'textarea')">Textarea</button>
            <button class="btn" onclick="return agregarCampo(<?= $formulario->id ?>,'select')">Select</button>
            <button class="btn" onclick="return agregarCampo(<?= $formulario->id ?>,'radio')">Radio</button>
            <button class="btn" onclick="return agregarCampo(<?= $formulario->id ?>,'checkbox')">Checkbox</button>
            <button class="btn" onclick="return agregarCampo(<?= $formulario->id ?>,'file')">File</button>
            <button class="btn" onclick="return agregarCampo(<?= $formulario->id ?>,'date')">Date</button>
        </div>
    </div>

    <form onsubmit="return false">
        <div class="row-fluid">
            <div class="span3">&nbsp;</div>
            <div class="span6">
                <legend><?=$formulario->nombre?></legend>
            </div>
            <div class="span3">
                <a href="#" class="btn" onclick="return editarFormulario(<?= $formulario->id ?>)">Editar</a>
            </div>
        </div>
        <div class="edicionFormulario">
            <?php foreach ($formulario->Campos as $c): ?>
                <div data-id="<?= $c->id ?>" class="row-fluid">
                    <div class="span3">
                        <div class="handler pull-right"></div>
                    </div>
                    <div class="span6"><?= $c->display() ?></div>
                    <div class="span3">
                        <a href="#" class="btn" onclick="return editarCampo(<?= $c->id ?>)">Editar</a>
                        <a href="<?= site_url('backend/formularios/eliminar_campo/' . $c->id) ?>" class="btn" onclick="return confirm('¿Esta seguro que desea eliminar?')">Eliminar</a>
                    </div>

                </div>

            <?php endforeach; ?>
        </div>
    </form>

</div>

<div class="modal hide fade" id="modal">

</div>