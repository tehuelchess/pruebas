<?php

class CampoRadio extends Campo {

    public function display($modo = 'edicion', $tramite_id = NULL) {
        $dato = NULL;
        if ($tramite_id)
            $dato = $this->getDatoDeTramite($tramite_id);

        $display = '<label>' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        foreach ($this->datos as $d) {
            $display.='<label class="radio">';
            $display.='<input ' . ($modo == 'visualizacion' ? 'disabled' : '') . ' type="radio" name="' . $this->nombre . '" value="' . $d->valor . '" ' . ($dato && $d->valor == $dato->valor ? 'checked' : '') . ' />';
            $display.=$d->etiqueta . '</label>';
        }

        return $display;
    }

}