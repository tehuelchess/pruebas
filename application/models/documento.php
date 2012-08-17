<?php

class Documento extends Doctrine_Record {

    function setTableDefinition() {        
        $this->hasColumn('id');
        $this->hasColumn('nombre');
        $this->hasColumn('contenido');
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
            'foreign' => 'documento_id'
        ));
    }
    
    public function generar($tramite_id){
        $CI=& get_instance();
                
        $regla=new Regla($this->contenido);
        $contenido=$regla->getExpresionParaOutput($tramite_id);
        
        $CI->load->library('tcpdf');
        
        $CI->tcpdf->AddPage();
        $CI->tcpdf->writeHTML($contenido);
        
        $filename=sha1(uniqid()).'.pdf';
        $uploadDirectory='uploads/documentos/';

        
        $CI->tcpdf->Output($uploadDirectory.$filename,'F');
        
        return $filename;
    }
    

}
