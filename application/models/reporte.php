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

        $excel[]=array_merge(array('id','estado','etapa_actual'), $this->campos);
        
        $tramites=$this->Proceso->Tramites;
        foreach($tramites as $t){
            $etapas_actuales=array();
            foreach($t->getEtapasActuales() as $e)
                $etapas_actuales[]=$e->Tarea->nombre;
            $etapas_actuales=  implode(',', $etapas_actuales);
            $row=array($t->id,$t->pendiente?'pendiente':'completado',$etapas_actuales);
            foreach($this->campos as $c){
                $regla=new Regla('@@'.$c);
                $row[]=$regla->getExpresionParaOutput($t->id);
            }
            $excel[]=$row;
        }

        $CI->excel_xml->addArray($excel);
        $CI->excel_xml->generateXML('reporte');
    }

    

}
