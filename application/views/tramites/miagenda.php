<?php
    function transformarFecha($fecha){
        //date_default_timezone_set('UTC');
        $mes='';
        switch($fecha[1]){
            case 1:
                $mes='Enero';
            break;
            case 2:
                $mes='Febrero';
            break;
            case 3:
                $mes='Marzo';
            break;
            case 4:
                $mes='Abril';
            break;
            case 5:
                $mes='Mayo';
            break;
            case 6:
                $mes='Junio';
            break;
            case 7:
                $mes='Julio';
            break;
            case 8:
                $mes='Agosto';
            break;
            case 9:
                $mes='Septiembre';
            break;
            case 10:
                $mes='Octubre';
            break;
            case 11:
                $mes='Noviembre';
            break;
            case 12:
                $mes='Diciembre';
            break;
        }

        $val=$fecha[0].' de '.$mes.' de '.$fecha[2];
        return $val;
    }
?>
<link rel="stylesheet" href= "<?= base_url('assets/calendar/css/calendar.css') ?>" >
<script src= "<?= base_url('/assets/js/jquery-ui/js/jquery-ui.js') ?>"></script>
<script src= "<?= base_url('/assets/js/moment.min.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/calendar/components/underscore/underscore-min.js') ?>"></script>
<!-- <script type="text/javascript" src="<?= base_url('assets/calendar/components/bootstrap2/js/bootstrap.min.js') ?>"></script> -->
<script type="text/javascript" src="<?= base_url('assets/calendar/components/jstimezonedetect/jstz.min.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/calendar/js/language/es-CO.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/calendar/js/calendar.js?v=0.1') ?>"></script>
<script src="<?= base_url() ?>assets/js/collapse.js"></script>
<script src="<?= base_url() ?>assets/js/transition.js"></script>
<script src="<?= base_url() ?>assets/js/bootstrap-datetimepicker.min.js"></script>
<input type="hidden" name="" id="urlbase" value="<?= base_url() ?>">
<script src= "<?= base_url('/assets/calendar/js/moment-2.2.1.js') ?>"></script>
<h2>Mis citas</h2>
<div class="containter-tab-agenda">
    <table class="table js-tab-agenda">
        <thead>
            <tr>
                <th>Tramite</th>
                <th>Atiende</th>
                <th>Cuando</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
                if(isset($data) && is_array($data) && count($data)>0){
                    foreach($data as $item){
                        $fecha=date('d/m/Y H:i:s',strtotime($item->appointment_time));
                        $fecha2=date('d/m/Y H:i',strtotime($item->appointment_time));
                        $tmp=explode(' ',$fecha);
                        $tmp2=explode(' ',$fecha2);
                        $fe=explode('/',$tmp[0]);
                        $dcita=transformarFecha($fe);
                        $fechaparam=$fe[0].'-'.$fe[1].'-'.$fe[2];
                        $acciones='<a class="btn btn-primary" onclick="editar('.$item->appointment_id.','.$item->calendar_id.');" href="#"><i class="icon-white icon-edit"></i> Editar</a> <a class="btn btn-danger" href="#" onclick="cancelarCita(\''.$item->appointment_id.'\',\''.$fechaparam.'\');"><i class="icon-white icon-remove"></i> Cancelar</a>';
                        echo '<tr> <td>'.$item->tramite.'</td><td>'.$item->owner_name.'</td><td>'.$dcita.' a las '.$tmp2[1].'</td><td>'.$acciones.'</td></tr>';
                    }
                }else{
                    echo '<tr><td colspan="4">No existen citas</td></tr>';
                }
            ?>
        </tbody>
    </table>
</div>
<div id="paginador" style="text-align: center;" class="clearfix">
    <ul class="pagination" style="max-width: 255px;">
        <li><a href="<?=site_url('/tramites/miagenda')?>">&laquo;</a></li>
        <?php
            for($i=$pagina_desde;$i<=$pagina_hasta;$i++){
                if($i>0 && $i<=$total_paginas){
                   echo '<li><a href="'.site_url('/tramites/miagenda/'.$i).'">'.$i.'</a></li>';
                }
            }
        ?>
        <li><a href="<?=site_url('/tramites/miagenda/'.$total_paginas)?>">&raquo;</a></li>
    </ul>
</div>
<script type="text/javascript">
    $(function(){
        moment.lang('es');
    });
    function cancelarCita(id,fecha) {
        //$("#modalcancelar").load(site_url + "backend/agendasusuario/ajax_cancelar_cita/" + id+"/"+fecha);
        $("#modalcancelar").load(site_url + "backend/agendasusuario/ajax_cancelar_cita?id="+id+"&fecha="+fecha);
        $("#modalcancelar").modal();
    }
    function editar(idcita,idcalendar){
        calendarioFront(idcalendar,0,idcita);
    }
</script>
<div id="modalcancelar" class="modal hide fade"></div>
<div id="modalcalendar" class="modal hide fade modalconfg modcalejec"></div>