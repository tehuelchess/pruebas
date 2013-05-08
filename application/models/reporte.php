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

        $excel[]=array_merge(array('id','estado','etapa_actual','fecha_inicio','fecha_modificacion','fecha_termino'), $this->campos);
        
        $tramites=Doctrine_Query::create()
                ->from('Tramite t, t.Proceso p, t.Etapas e, e.DatosSeguimiento d')
                ->where('p.id = ?', $this->proceso_id)
                ->having('COUNT(d.id) > 0 OR COUNT(e.id) > 1')  //Mostramos solo los que se han avanzado o tienen datos
                ->groupBy('t.id')
                ->execute();
        
        foreach($tramites as $t){
            $etapas_actuales=array();
            foreach($t->getEtapasActuales() as $e)
                $etapas_actuales[]=$e->Tarea->nombre;
            $etapas_actuales=  implode(',', $etapas_actuales);
            $row=array($t->id,$t->pendiente?'pendiente':'completado',$etapas_actuales,$t->created_at,$t->updated_at,$t->ended_at);
            foreach($this->campos as $c){
                $regla=new Regla('@@'.$c);
                $row[]=$regla->getExpresionParaOutput($t->getUltimaEtapa()->id);
            }
            $excel[]=$row;
        }

        $CI->excel_xml->addArray($excel);
        $CI->excel_xml->generateXML('reporte');
    }

    

}
