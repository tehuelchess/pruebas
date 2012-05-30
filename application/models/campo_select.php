<?php

class CampoSelect extends Campo {

    public function display($modo = 'edicion', $tramite_id = NULL) {
        $dato = NULL;
        if ($tramite_id)
            $dato = $this->getDatoDeTramite($tramite_id);

        $display = '<label>' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display.='<select name="' . $this->nombre . '" ' . ($modo == 'visualizacion' ? 'disabled' : '') . '>';
        foreach ($this->datos as $d) {
            $display.='<option value="' . $d->valor . '" ' . ($dato && $d->valor == $dato->valor ? 'selected' : '') . '>' . $d->etiqueta . '</option>';
        }
        $display.='</select>';

        return $display;
    }

}