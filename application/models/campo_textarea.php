<?php

class CampoTextArea extends Campo{
    

    protected function display($modo, $dato) {       
        $display='<label>' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display.='<textarea ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' name="' . $this->nombre . '">' . ($dato?$dato->valor:'') . '</textarea>';
    
        return $display;
    }
    
}