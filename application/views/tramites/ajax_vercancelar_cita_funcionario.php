<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">×</button>
    <h3 id="myModalLabel">Cita</h3>
</div>
    <div class="modal-body">
        <div class="validacion"></div>
        <table>
            <tr>
                <td style="width: 140px;"><strong>Tramite: </strong></td>
                <td><?= $tramite ?></td>
            </tr>
            <tr>
                <td><strong>Solicitante: </strong></td>
                <td><?= $solicitante ?></td>
            </tr>
            <tr>
                <td><strong>Fecha: </strong></td>
                <td id="txtfechadet"><?= $dia ?></td>
            </tr>
            <tr>
                <td><strong>Hora: </strong></td>
                <td><?= $hora ?></td>
            </tr>
            <tr>
                <td><strong>Correo Solicitante: </strong></td>
                <td><?= $correo ?></td>
            </tr>
        </table>
    </div>
<div class="modal-footer">
    <button class="btn js_cerrar_vcancelar" data-dismiss="modal">Cerrar</button>
    <a href="#" onclick="cancelar_cita(<?= $idcita ?>);" class="btn btn-primary">Cancelar Cita</a>
</div>
<?php
    $fe=explode('-',$dia);
?>
<script>
    $(function(){
        moment.lang('es');
        var d=<?= $fe[0] ?>;
        var m=<?= $fe[1] ?>;
        var y=<?= $fe[2] ?>;
        var f=moment(y+"-"+m+"-"+d).format("LL");
        $("#txtfechadet").text(f);
    });
</script>
