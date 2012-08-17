<?php

class CampoParagraph extends Campo{
    
    public $requiere_validacion=false;
    public $requiere_datos=false;
    public $requiere_readonly=false;

    protected function display($modo, $dato) {      
        $display='<p>'.$this->etiqueta.'</p>';
        
        return $display;
    }
    
    public function getReadonly(){
        return 1;
    }
    
    public function setReadonly($readonly){
        $this->_set('readonly', 1);
    }
    
}