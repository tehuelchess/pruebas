<?php
require_once('campo.php');
class CampoCheckbox extends Campo {

    protected function display($modo, $dato) {
        $display = '<label class="control-label">' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display .= '<div class="controls">';
        foreach ($this->datos as $d) {
            $display.='<label class="checkbox">';
            $display.='<input ' . ($modo == 'visualizacion' ? 'disabled' : '') . ' type="checkbox" name="' . $this->nombre . '[]" value="' . $d->valor . '" ' . ($dato && $dato->valor && in_array($d->valor, $dato->valor) ? 'checked' : '') . ' />';
            $display.=$d->etiqueta . '</label>';
        }
        $display.='</div>';

        return $display;
    }

}