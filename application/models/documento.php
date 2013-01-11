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

        $obj=new $CI->tcpdf;
        
        $obj->AddPage();
        $obj->writeHTML($contenido);
        
        $filename=sha1(uniqid()).'.pdf';
        $uploadDirectory='uploads/documentos/';

        //ob_start();        
        $obj->Output($uploadDirectory.$filename,'F');
        //ob_end_clean();
        
        return $filename;
    }
    

}
