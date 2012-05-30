<?php

class CampoCheckbox extends Campo {

    public function display($modo = 'edicion', $tramite_id = NULL) {
        $dato = NULL;
        if ($tramite_id)
            $dato = $this->getDatoDeTramite($tramite_id);

        $display = '<label>' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        foreach ($this->getDatosFromJSON() as $d) {
            $display.='<label class="checkbox">';
            $display.='<input ' . ($modo == 'visualizacion' ? 'disabled' : '') . ' type="checkbox" name="' . $this->nombre . '[]" value="' . $d->valor . '" ' . ($dato && $dato->valor && in_array($d->valor, $dato->valor) ? 'checked' : '') . ' />';
            $display.=$d->etiqueta . '</label>';
        }

        return $display;
    }

}