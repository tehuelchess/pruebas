<?php
require_once('campo.php');
class CampoParagraph extends Campo{
    
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

    protected function display($modo, $dato, $etapa_id) {
        if($etapa_id){
            $etapa=Doctrine::getTable('Etapa')->find($etapa_id);
            $regla=new Regla($this->etiqueta);
            $etiqueta=$regla->getExpresionParaOutput($etapa->tramite_id);
        }else{
            $etiqueta=$this->etiqueta;
        }
        
        $display='<p>'.$etiqueta.'</p>';
        
        return $display;
    }
    
    public function setEstatico($estatico){
        $this->_set('estatico', 1);
    }
    
    public function setReadonly($readonly){
        $this->_set('readonly', 1);
    }
    

}