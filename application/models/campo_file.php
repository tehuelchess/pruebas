<?php

class CampoFile extends Campo {

    public function display($modo = 'edicion', $tramite_id = NULL) {
        $dato = NULL;
        if ($tramite_id)
            $dato = $this->getDatoDeTramite($tramite_id);
        
        $display='<label>' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display.='<div>';
        $display.='<div class="' . ($modo == 'visualizacion' ? '' : 'file-uploader') . '" name="' . $this->nombre . '" value="' . ($dato ? $dato->valor : '') . '"></div>';
        $display.='<input type="hidden" name="' . $this->nombre . '" value="' . ($dato ? $dato->valor : '') . '" />';
        if ($dato)
            $display.='<p><a href="' . site_url('uploader/datos_get/' . $dato->valor) . '" target="_blank">' . $dato->valor . '</a></p>';
        $display.='</div>';

        return $display;
    }

}