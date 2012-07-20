<?php
require_once('campo.php');
class CampoFile extends Campo {
    
    public $requiere_datos=false;

    protected function display($modo, $dato) {   
        $display='<label>' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display.='<div>';
        $display.='<div class="' . ($modo == 'visualizacion' ? '' : 'file-uploader') . '"></div>';
        $display.='<input type="hidden" name="' . $this->nombre . '" value="' . ($dato ? htmlspecialchars($dato->valor) : '') . '" />';
        if ($dato)
            $display.='<p><a href="' . site_url('uploader/datos_get/' . htmlspecialchars ($dato->valor)) . '" target="_blank">' . htmlspecialchars ($dato->valor) . '</a></p>';
        $display.='</div>';

        return $display;
    }

}