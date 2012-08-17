<?php

class CampoRadio extends Campo {

    protected function display($modo, $dato) {
        $display = '<label>' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        foreach ($this->datos as $d) {
            $display.='<label class="radio">';
            $display.='<input ' . ($modo == 'visualizacion' ? 'disabled' : '') . ' type="radio" name="' . $this->nombre . '" value="' . $d->valor . '" ' . ($dato && $d->valor == $dato->valor ? 'checked' : '') . ' />';
            $display.=$d->etiqueta . '</label>';
        }

        return $display;
    }

}