<div class="front">
    <div class="cabecera">
        <h3><?=$widget->nombre?></h3>
        <a class="config" href="#" onclick="return widgetConfig(this)"><i class="icon-wrench icon-white"></i></a>
    </div>
    <div class="contenido">
        <?=$widget->display()?>
    </div>
</div>
<div class="back">
    <form class="ajaxForm" method="POST" action="<?= site_url('backend/portada/widget_config_form/'.$widget->id) ?>" data-onsuccess="widgetConfigOk">
        <div class="cabecera">
            <h3>Configuración</h3>
            <button type="submit" class="volver btn btn-mini">ok</button>
        </div>
        <div class="contenido">
            <div class="validacion"></div>
            <label>Nombre</label>
            <input type="text" name="nombre" value="<?=$widget->nombre?>" />
            <?= $widget->displayForm() ?>
        </div>
    </form>  
</div>

