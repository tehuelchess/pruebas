<?php
require_once('campo.php');
class CampoFile extends Campo {

    protected function display($modo, $dato) {   
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