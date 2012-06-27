<?php

class CampoDate extends Campo{
    

    protected function display($modo, $dato) {       
        $display='<label>' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';    
        $display.='<input class="datepicker" ' . ($modo == 'visualizacion' ? 'disabled' : '') . ' type="text" value="' . ($dato?date('d/m/Y',mysql_to_unix($dato->valor)):'') . '" />';
        $display.='<input type="hidden" name="'.$this->nombre.'" value="" />';
        
        return $display;
    }
    
}