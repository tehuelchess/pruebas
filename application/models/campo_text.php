<?php

class CampoText extends Campo{
    

    protected function display($modo, $dato) {      
        $display='<label>' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';    
        $display.='<input ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' type="text" name="' . $this->nombre . '" value="' . ($dato?$dato->valor:'') . '" />';
    
        return $display;
    }
    
}