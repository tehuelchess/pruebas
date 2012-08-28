<?php

class CampoParagraph extends Campo{
    
    public $requiere_validacion=false;
    public $requiere_datos=false;
    public $siempre_readonly=true;
    public $siempre_estatico=true;

    protected function display($modo, $dato) {      
        $display='<p>'.$this->etiqueta.'</p>';
        
        return $display;
    }
    
    
    public function setReadonly($readonly){
        $this->_set('readonly', 1);
    }
    
    public function setEstatico($estatico){
        $this->_set('estatico', 1);
    }
    
}