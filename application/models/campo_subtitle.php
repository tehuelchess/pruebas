<?php
require_once("campo.php");

class CampoSubtitle extends Campo{

    public $requiere_datos=false;
    
    function setTableDefinition() {
        parent::setTableDefinition();
        
        $this->hasColumn('estatico','bool',1,array('default'=>1));
        $this->hasColumn('readonly','bool',1,array('default'=>1));
    }
    
    function setUp() {
        parent::setUp();
        $this->setTableName("campo");
    }
    
    protected function display($modo, $dato) {      
        $display='<h4>'.$this->etiqueta.'</h4>';
        
        return $display;
    }
    
    public function setEstatico($estatico){
        $this->_set('estatico', 1);
    }
    
    public function setReadonly($readonly){
        $this->_set('readonly', 1);
    }
    

}
