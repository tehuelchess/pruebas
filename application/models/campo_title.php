<?php

class CampoTitle extends Campo{
    
    public $requiere_validacion=false;
    public $requiere_datos=false;
    public $requiere_readonly=false;

    protected function display($modo, $dato) {      
        $display='<h3>'.$this->etiqueta.'</h3>';
        
        return $display;
    }
    
    public function setReadonly($readonly){
        $this->_set('readonly', 1);
    }
    
}