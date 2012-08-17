<?php

class CampoSelect extends Campo {
    
    protected function display($modo, $dato) {
        $display = '<label>' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display.='<select name="' . $this->nombre . '" ' . ($modo == 'visualizacion' ? 'disabled' : '') . '>';
        foreach ($this->datos as $d) {
            $display.='<option value="' . $d->valor . '" ' . ($dato && $d->valor == $dato->valor ? 'selected' : '') . '>' . $d->etiqueta . '</option>';
        }
        $display.='</select>';

        return $display;
    }

}