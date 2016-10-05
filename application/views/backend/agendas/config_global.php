<link rel="stylesheet" href= "<?= base_url('assets/calendar/css/calendar.css') ?>" >

<link rel="stylesheet" href="<?= base_url('/assets/calendar2/css/clndr.css') ?>" type="text/css" />
<script src="<?= base_url('/assets/calendar2/js/underscore-min.js') ?>"></script>
<script src= "<?= base_url('/assets/calendar2/js/moment-2.2.1.js') ?>"></script>
<script src="<?= base_url('/assets/calendar2/js/clndr.js') ?>"></script>

<script type="text/javascript" src="<?= base_url('assets/calendar/components/underscore/underscore-min.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/calendar/components/bootstrap2/js/bootstrap.min.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/calendar/components/jstimezonedetect/jstz.min.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/calendar/js/language/es-CO.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/calendar/js/calendar.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/js/calendarbackend.js') ?>"></script>
<ul class="breadcrumb">
    <li>
        Configuraci&oacute;n Global
    </li>
</ul>
<div class="containter-calendar">
    <input type="hidden" id="urlbase" value="<?= base_url() ?>" />
    <div class="page-header">
        <div class="pull-right form-inline">
            <div class="btn-group">
                <button class="btn btn-primary" data-calendar-nav="prev"><< Anterior</button>
                <button class="btn" data-calendar-nav="today">Hoy</button>
                <button class="btn btn-primary" data-calendar-nav="next">Siguiente >></button>
            </div>
            <div class="btn-group">
                <button class="btn btn-warning" data-calendar-view="year">A&ntilde;o</button>
                <button class="btn btn-warning active" data-calendar-view="month">Mes</button>
                <button class="btn btn-warning" data-calendar-view="week">Semana</button>
                <button class="btn btn-warning" data-calendar-view="day">Dia</button>
            </div>
        </div>
        <h3></h3>
    </div>
    <div id="calendar" class="calendar float-left"></div>
    <div class="detallecal">
        <div class="labeldetconfglob"><label>D&iacute;as Feriados Registrados</label></div>
        <div id="desccalendar" class="det col-md-5 col-sm-12 col-xs-12"></div>
        <div class="container-bot"><a class="btn btn-danger" href="#" onclick="eliminarDia();"><i class="icon-white icon-remove"></i> Eliminar</a></div>
    </div>
</div>
<div id="agregardia" class="modal hide fade modalconfg"></div>
<input type="hidden" id="fechaaelim" value="" />
<script>
    window.listado=null;
    var calendars = {};
    $(function(){
        carcarObjectCalendar();
        
    });    
    function carcarObjectCalendar(){
        moment.lang('es');
        var thisMonth = moment().format('YYYY-MM');
    }
    function getListado(){
        return listado;
    }
    function cargarPanelDetalles(url,fecha){
        $.ajax({
            url: url,
            dataType: "json",
            data:{
                fechaactual:fecha
            },
            success: function( data ) {
                if(data.code==200){
                    var items=data.fechas;
                    $('#desccalendar').html('');
                    listado=items;
                    $.each(items, function(index, element) {
                        var f=element.fecha.split('/');
                        var val=moment(f[1]+'/'+f[0]+'/'+f[2]).format("LL");
                        var impar='';
                        if((index+1)%2==1){
                            impar='impar';
                        }
                        var row='<div id="'+element.fecha+'" class="rowcalendar js_row_calendar '+impar+' clearfix"><div class="labelc">'+val+'</div><div class="detallec">'+element.descripcion+'</div></div>';
                        $('#desccalendar').append(row);
                        //
                    });
                    $('.js_row_calendar').off('click');
                    $('.js_row_calendar').on('click',function(){
                        $('.rowactive').removeClass('rowactive');
                        $(this).addClass('rowactive');
                        var id=$(this).attr('id');
                        $('#fechaaelim').val(id);
                    });
                }                    
            }
        });
    }
    function eliminarDia(){
        var swselecciono=0;
        var fecha='0';
        if(jQuery.trim($('#fechaaelim').val())!=''){
            swselecciono=1;
            var fe=$('#fechaaelim').val().split('/');
            fecha=fe[0]+'-'+fe[1]+'-'+fe[2];
        }
        
        $("#agregardia").load(site_url + "backend/agendas/ajax_confirmar_eliminar_dia/"+swselecciono+"/"+fecha);
        $("#agregardia").modal();
    }
</script>