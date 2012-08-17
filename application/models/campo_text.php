<?php

class CampoText extends Campo{
    
    public $requiere_datos=false;

    protected function display($modo, $dato) {        
        $display='<label>' . $this->etiqueta . (!$this->readonly && !in_array('required', $this->validacion) ? ' (Opcional)' : '') . '</label>';    
        $display.='<input ' . ($this->readonly || $modo == 'visualizacion' ? 'readonly' : '') . ' type="text" name="' . $this->nombre . '" value="' . ($dato?htmlspecialchars($dato->valor):'') . '" />';
    
        return $display;
    }
    
}