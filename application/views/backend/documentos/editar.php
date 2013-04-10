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

<script>
    $(document).ready(function() {
        handleRadio();
        $("input[name=tipo]").change(handleRadio);
        
        function handleRadio() {
            var value=$("input[name=tipo]:checked").val();
            if (value == "blanco") {
                $("#certificadoArea").hide();
            } else {
                $("#certificadoArea").show();
            }
        }
    });
</script>

<form class="ajaxForm" method="POST" action="<?= site_url('backend/documentos/editar_form/' . ($edit ? $documento->id : '')) ?>">
    <fieldset>
        <legend>Crear Documento</legend>
        <div class="validacion"></div>
        <?php if (!$edit): ?>
            <input type="hidden" name="proceso_id" value="<?= $proceso->id ?>" />
        <?php endif; ?>
        <label>Nombre</label>
        <input type="text" name="nombre" value="<?= $edit ? $documento->nombre : '' ?>" />

        <label>Tipo de documento</label>
        <label class="radio"><input type="radio" name="tipo" value="blanco" <?= !$edit || ($edit && $documento->tipo) == 'blanco' ? 'checked' : '' ?> /> En blanco</label>
        <label class="radio"><input type="radio" name="tipo" value="certificado" <?= $edit && $documento->tipo == 'certificado' ? 'checked' : '' ?> /> Certificado</label>

        <div id="certificadoArea">
            <label>Servicio que emite el documento</label>
            <input class="input-xxlarge" type="text" name="servicio" value="<?= $edit ? $documento->servicio : '' ?>" placeholder="Ej: Ministerio Secretaría General de la Presidencia" />
            <label>URL al sitio web del servicio</label>
            <input class="input-xxlarge" type="text" name="servicio_url" value="<?= $edit ? $documento->servicio_url : '' ?>" placeholder="Ej: http://www.minsegpres.gob.cl" />
            <label>Logo del Servicio (Opcional)</label>
            <div id="file-uploader-logo"></div>
            <input type="hidden" name="logo" value="<?=$edit?$documento->logo:'' ?>" />
            <img class="logo" src="<?= $edit && $documento->logo ? site_url('backend/uploader/logo_certificado_get/' . $documento->logo) : '#' ?>" alt="" width="200" />
            <script>
                var uploader = new qq.FileUploader({
                    element: document.getElementById('file-uploader-logo'),
                    action: site_url + 'backend/uploader/logo_certificado',
                    onComplete: function(id, filename, respuesta) {
                        $("input[name=logo]").val(respuesta.file_name);
                        $("img.logo").attr("src", site_url + "backend/uploader/logo_certificado_get/" + respuesta.file_name);
                    }
                });
            </script>
            <label>Imagen del timbre (Opcional)</label>
            <div id="file-uploader-timbre"></div>
            <input type="hidden" name="timbre" value="<?=$edit?$documento->timbre:'' ?>" />
            <img class="timbre" src="<?= $edit && $documento->timbre ? site_url('backend/uploader/timbre_get/' . $documento->timbre) : '#' ?>" alt="" width="200" />
            <script>
                var uploader = new qq.FileUploader({
                    element: document.getElementById('file-uploader-timbre'),
                    action: site_url + 'backend/uploader/timbre',
                    onComplete: function(id, filename, respuesta) {
                        $("input[name=timbre]").val(respuesta.file_name);
                        $("img.timbre").attr("src", site_url + "backend/uploader/timbre_get/" + respuesta.file_name);
                    }
                });
            </script>
            <label>Nombre de la persona que firma</label>
            <input class="input-xxlarge" type="text" name="firmador_nombre" value="<?= $edit ? $documento->firmador_nombre : '' ?>" placeholder="Ej: Juan Perez" />
            <label>Cargo de la persona que firma</label>
            <input class="input-xxlarge" type="text" name="firmador_cargo" value="<?= $edit ? $documento->firmador_cargo : '' ?>" placeholder="Ej: Jefe de Servicio" />
            <label>Servicio al que pertenece la persona que firma</label>
            <input class="input-xxlarge" type="text" name="firmador_servicio" value="<?= $edit ? $documento->firmador_servicio : '' ?>" placeholder="Ej: Ministerio Secretaría General de la Presidencia" />
            <label>Imagen de la firma</label>
            <div id="file-uploader"></div>
            <input type="hidden" name="firmador_imagen" value="<?=$edit?$documento->firmador_imagen:'' ?>" />
            <img class="firma" src="<?= $edit && $documento->firmador_imagen ? site_url('backend/uploader/firma_get/' . $documento->firmador_imagen) : base_url('assets/img/certificados/firma.png') ?>" alt="firma" width="200" />
            <script>
                var uploader = new qq.FileUploader({
                    element: document.getElementById('file-uploader'),
                    action: site_url + 'backend/uploader/firma',
                    onComplete: function(id, filename, respuesta) {
                        $("input[name=firmador_imagen]").val(respuesta.file_name);
                        $("img.firma").attr("src", site_url + "backend/uploader/firma_get/" + respuesta.file_name);
                    }
                });
            </script>
            <label>Numero de dias de validez (Dejar en blanco para periodo ilimitado)</label>
            <input class="input-mini" type="text" name="validez" value="<?= $edit ? $documento->validez : '' ?>" placeholder="Ej: 90" />
        </div>

        <label>Contenido</label>
        <textarea name="contenido" class="input-xxlarge" rows="20"><?= $edit ? $documento->contenido : '' ?></textarea>


        <div class="form-actions">
            <a class="btn" href="<?= site_url('backend/documentos/listar/' . $proceso->id) ?>">Cancelar</a>
            <input class="btn btn-primary" type="submit" value="Guardar" />
        </div>
    </fieldset>
</form>




</div>