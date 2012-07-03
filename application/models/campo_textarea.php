<?php

class CampoTextArea extends Campo{
    
    public $requiere_datos=false;

    protected function display($modo, $dato) {       
        $display='<label>' . $this->etiqueta . (!$this->readonly && !in_array('required', $this->validacion) ? ' (Opcional)' : '') . '</label>';
        $display.='<textarea ' . ($this->readonly || $modo == 'visualizacion' ? 'readonly' : '') . ' name="' . $this->nombre . '">' . ($dato?$dato->valor:'') . '</textarea>';
    
        return $display;
    }
    
}