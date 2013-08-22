<?php
require_once 'widget.php';

class WidgetEtapaUsuarios extends Widget {

    
    public function display(){
        if (!$this->config) {
            $display = '<p>Widget requiere configuración</p>';
            return $display;
        }
        
    $tarea=Doctrine::getTable('Tarea')->find($this->config->tarea_id);
    
    $tmp=  Doctrine_Query::create()
            ->select('u.*, COUNT(e.id) as cantidad')
            ->from('Usuario u, u.Etapas e, e.Tarea t, t.Proceso.Cuenta c')
            ->where('t.id = ? and c.id = ?',array($tarea->id,$this->cuenta_id))
            ->andWhere('e.pendiente = 1') 
            ->groupBy('u.id')
            ->execute();
        
    $datos=array();
    foreach($tmp as $t)
        $datos[]=array($t->usuario,(float)$t->cantidad);
        
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
                        text: "'.$tarea->nombre.'"
                    },
                    tooltip: {
                        pointFormat: "{point.y} trámites pendientes: <b>{point.percentage:.1f}%</b>"
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
        $tarea_id=$this->config?$this->config->tarea_id:null;

        
        $display='<label>Tareas</label>';
        $procesos=$this->Cuenta->Procesos;
        $display.= '<select name="config[tarea_id]">';
        foreach($procesos as $p){
            $display.='<optgroup label="'.$p->nombre.'">';
            foreach($p->Tareas as $t)
                $display.= '<option value="'.$t->id.'" '.($tarea_id==$t->id?'selected':'').'>'.$t->nombre.'</option>';
            $display.='</optgroup>';
        }
        $display.= '</select>';
        
    
        
        return $display;

    }
    
    public function validateForm(){
        $CI=& get_instance();
        $CI->form_validation->set_rules('config[tarea_id]','Tarea','required');
    }


}
