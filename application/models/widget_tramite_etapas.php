<?php
require_once 'widget.php';

class WidgetTramiteEtapas extends Widget {

    
    public function display(){
        if (!$this->config) {
            $display = '<p>Widget requiere configuración</p>';
            return $display;
        }
        
    $proceso=Doctrine::getTable('Proceso')->find($this->config->proceso_id);
    
    $tmp=  Doctrine_Query::create()
            ->select('tar.id, tar.nombre, COUNT(tar.id) as cantidad')
            ->from('Tarea tar, tar.Etapas e, e.DatosSeguimiento d, e.Tramite t, t.Proceso p, p.Cuenta c')
            ->where('p.id = ? and c.id = ?',array($proceso->id,$this->cuenta_id))
            ->andWhere('e.pendiente = 1')
            ->having('COUNT(d.id) > 0 OR COUNT(e.id) > 1')  //Mostramos solo los que se han avanzado o tienen datos
            ->groupBy('tar.id')
            ->execute();
    
    $datos=array();
    foreach($tmp as $t)
        $datos[]=array($t->nombre,(float)$t->cantidad);
        
    $datos=json_encode($datos);

    $display='<div class="grafico"></div>';
    $display.='
        <script type="text/javascript">
            $(document).ready(function(){
                new Highcharts.Chart({
                    chart: {
                        renderTo: $(".widget[data-id='.$this->id.'] .grafico")[0],
                        type: "pie"
                    },
                    title: {
                        text: "'.$proceso->nombre.'"
                    },
                    tooltip: {
                        pointFormat: "{point.y} trámites: <b>{point.percentage:.1f}%</b>"
                    },
                    series: [{
                            data: '.$datos.'
                        }]
                });
            });
        </script>';
        
        return $display;
    }
    
    public function displayForm(){
        $proceso_id=$this->config?$this->config->proceso_id:null;

        
        $display='<label>Proceso</label>';
        $procesos=$this->Cuenta->Procesos;
        $display.= '<select name="config[proceso_id]">';
        foreach($procesos as $p)
            $display.= '<option value="'.$p->id.'" '.($proceso_id==$p->id?'selected':'').'>'.$p->nombre.'</option>';
        $display.= '</select>';
        
    
        
        return $display;

    }
    
    public function validateForm(){
        $CI=& get_instance();
        $CI->form_validation->set_rules('config[proceso_id]','Proceso','required');
    }


}
