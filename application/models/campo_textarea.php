<?php
require_once('campo.php');
class CampoTextArea extends Campo{
    
    public $requiere_datos=false;

    protected function display($modo, $dato) {       
        $display='<label class="control-label" for="'.$this->id.'">' . $this->etiqueta . (!in_array('required', $this->validacion) ? ' (Opcional)' : '') . '</label>';
        $display.='<div class="controls">';
        $display.='<textarea id="'.$this->id.'" rows="5" class="input-xxlarge" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' name="' . $this->nombre . '">' . ($dato?htmlspecialchars($dato->valor):'') . '</textarea>';
        $display.='</div>';
        
        return $display;
    }
    
}