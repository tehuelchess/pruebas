<?php
require_once('campo.php');
class CampoTextArea extends Campo{
    
    public $requiere_datos=false;

    protected function display($modo, $dato) {       
        $display='<label>' . $this->etiqueta . (!in_array('required', $this->validacion) ? ' (Opcional)' : '') . '</label>';
        $display.='<textarea rows="5" class="input-xxlarge" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' name="' . $this->nombre . '">' . ($dato?htmlspecialchars($dato->valor):'') . '</textarea>';
    
        return $display;
    }
    
}