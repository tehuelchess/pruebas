<?php

class CampoSubtitle extends Campo{

    public $requiere_validacion=false;
    public $requiere_datos=false;
    public $requiere_readonly=false;
    
    protected function display($modo, $dato) {      
        $display='<h4>'.$this->etiqueta.'</h4>';
        
        return $display;
    }
    
    public function setReadonly($readonly){
        $this->_set('readonly', 1);
    }
    
}