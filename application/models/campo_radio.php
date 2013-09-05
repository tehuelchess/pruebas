<?php
require_once('campo.php');
class CampoRadio extends Campo {

    protected function display($modo, $dato) {
        $display = '<label class="control-label">' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display.='<div class="controls">';
        foreach ($this->datos as $d) {
            $display.='<label class="radio">';
            $display.='<input ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' type="radio" name="' . $this->nombre . '" value="' . $d->valor . '" ' . ($dato && $d->valor == $dato->valor ? 'checked' : '') . ' />';
            $display.=$d->etiqueta . '</label>';
        }
        if($this->ayuda)
            $display.='<span class="help-block">'.$this->ayuda.'</span>';
        $display.='</div>';
        return $display;
    }
    
    public function backendExtraValidate(){
        $CI=&get_instance();
        $CI->form_validation->set_rules('datos','Datos','required');
    }

}