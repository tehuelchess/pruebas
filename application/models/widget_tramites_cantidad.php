<?php
require_once 'widget.php';

class WidgetTramitesCantidad extends Widget {

    public function display() {
        if (!$this->config) {
            $display = '<p>Widget requiere configuración</p>';
            return $display;
        }

        $datos = array();

        $tmp = Doctrine_Query::create()
                ->select('p.id, p.nombre, COUNT(p.id) as cantidad')
                ->from('Proceso p, p.Tramites t, p.Cuenta c')
                ->where('c.id = ?', $this->cuenta_id)
                ->andWhereIn('p.id', $this->config->procesos)
                ->andWhere('t.pendiente=1')
                ->groupBy('p.id')
                ->execute();
        
        
        foreach ($tmp as $p)
            $datos[$p->nombre]['pendientes'] = $p->cantidad;

        $tmp2 = Doctrine_Query::create()
                ->select('p.id, p.nombre, COUNT(p.id) as cantidad')
                ->from('Proceso p, p.Tramites t, p.Cuenta c')
                ->where('c.id = ?', $this->cuenta_id)
                ->andWhereIn('p.id', $this->config->procesos)
                ->andWhere('t.pendiente=0')
                ->groupBy('p.id')
                ->execute();

        foreach ($tmp2 as $p)
            $datos[$p->nombre]['completados'] = $p->cantidad;
        
        
        foreach ($datos as $key => $val) {
            $categories[] = $key;
            $pendientes[] = isset($val['pendientes']) ? (int)$val['pendientes'] : 0;
            $completados[] = isset($val['completados']) ? (int)$val['completados'] : 0;
        }
        $categories = json_encode($categories);
        $pendientes = json_encode($pendientes);
        $completados = json_encode($completados);
        
        $display = '<div class="grafico"></div>';
        $display.='
        <script type="text/javascript">
            $(document).ready(function(){
                new Highcharts.Chart({
                    chart: {
                        renderTo: $(".widget[data-id=' . $this->id . '] .grafico")[0],
                        type: "column"
                    },
                    title: null,
                    yAxis: {
                        title: {
                            text: "Nº de Trámites"
                        },
                    },
                    xAxis: {
                        categories: ' . $categories . '
                    },
                    series: [{
                        name: "Pendientes",
                        data: ' . $pendientes . '
                        },
                        {
                        name: "Completados",
                        data: ' . $completados . '
                    }]
                });
            });
        </script>';


        return $display;
    }

    public function displayForm() {
        $procesos = $this->Cuenta->Procesos;
        
        $procesos_array=$this->config?$this->config->procesos:array();
        
        $display = '<label>Procesos a desplegar</label>';
        foreach ($procesos as $p)
            $display.='<label><input type="checkbox" name="config[procesos][]" value="' . $p->id . '" ' . (in_array($p->id, $procesos_array) ? 'checked' : '') . ' /> ' . $p->nombre . '</label>';

        return $display;
    }

    public function validateForm() {
        $CI = & get_instance();
        $CI->form_validation->set_rules('config[procesos]', 'Procesos', 'required');
    }

}
