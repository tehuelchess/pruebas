<?php

class Reporte extends Doctrine_Record {

    function setTableDefinition() {        
        $this->hasColumn('id');
        $this->hasColumn('nombre');
        $this->hasColumn('campos');
        $this->hasColumn('proceso_id');
    }
        


    function setUp() {
        parent::setUp();

        $this->hasOne('Proceso', array(
            'local' => 'proceso_id',
            'foreign' => 'id'
        ));
        
        $this->hasMany('Campo as Campos', array(
            'local' => 'id',
            'foreign' => 'reporte_id'
        ));
    }
    
    public function setCampos($campos){
        $this->_set('campos', json_encode($campos));
    }
    
    public function getCampos(){
        return json_decode($this->_get('campos'));
    }
    
    public function generar(){
        $CI=& get_instance();
        
        $CI->load->library('Excel_XML');

        $header=array_merge(array('id','estado','etapa_actual','fecha_inicio','fecha_modificacion','fecha_termino'),$this->campos);  
        
        $excel[]=$header;
        
        $tramites=Doctrine_Query::create()
                ->from('Tramite t, t.Proceso p, t.Etapas e, e.DatosSeguimiento d')
                ->where('p.id = ?', $this->proceso_id)
                ->having('COUNT(d.id) > 0 OR COUNT(e.id) > 1')  //Mostramos solo los que se han avanzado o tienen datos
                ->groupBy('t.id')
                ->execute();
        
        foreach($tramites as $t){
            $etapas_actuales=$t->getEtapasActuales();
            $etapas_actuales_arr=array();
            foreach($etapas_actuales as $e)
                $etapas_actuales_arr[]=$e->Tarea->nombre;
            $etapas_actuales_str=implode(',', $etapas_actuales_arr);
            $row=array($t->id,$t->pendiente?'pendiente':'completado',$etapas_actuales_str,$t->created_at,$t->updated_at,$t->ended_at);
                 
            $datos=Doctrine_Query::create()
                ->select('d.*')
                ->from('DatoSeguimiento d, d.Etapa e, e.Tramite t')
                ->andWhere('t.id = ?',$t->id)
                ->having('d.id = MAX(d.id)')
                ->groupBy('d.nombre')
                ->execute(array(),Doctrine_Core::HYDRATE_ARRAY);
            
            foreach($datos as $d){
                $colindex=array_search($d['nombre'],$header);
                if($colindex!==FALSE)
                    $row[$colindex]=utf8_decode(is_string(json_decode($d['valor']))?json_decode($d['valor']):$d['valor']);
            }
            ksort($row);

            $excel[]=$row;
        }

        $CI->excel_xml->addArray($excel);
        $CI->excel_xml->generateXML('reporte');
    }

    

}
