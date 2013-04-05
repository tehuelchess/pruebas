<?php
require_once('campo.php');
class CampoDate extends Campo{
    
    public $requiere_datos=false;
    
    protected function display($modo, $dato) {
        $display='<label class="control-label" for="'.$this->id.'">' . $this->etiqueta . (!in_array('required', $this->validacion) ? ' (Opcional)' : '') . '</label>';
        $display.='<div class="controls">';
        $display.='<input id="'.$this->id.'" '.($modo=='visualizacion'?'disabled':'').' class="datepicker" ' . ($modo == 'visualizacion' ? 'disabled' : '') . ' type="text" value="' . ($dato && $dato->valor?date('d/m/Y',strtotime($dato->valor)):'') . '" />';
        $display.='<input type="hidden" name="'.$this->nombre.'" value="'.($dato?$dato->valor:'').'" />';
        $display.='</div>';
        
        return $display;
    }
    
}