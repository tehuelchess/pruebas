<?php
require_once('campo.php');
class CampoFile extends Campo {
    
    public $requiere_datos=false;

    protected function display($modo, $dato,$etapa_id) {
        if(!$etapa_id){
            $display='<label>' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
            $display.='<button type="button" class="btn">Subir archivo</button>';
            return $display;
        }
        
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        
        $display='<label>' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display.='<div>';
        if($modo!='visualizacion')
            $display.='<div class="file-uploader" data-action="'.site_url('uploader/datos/'.$this->id.'/'.$etapa->id).'"></div>';
        $display.='<input type="hidden" name="' . $this->nombre . '" value="' . ($dato ? htmlspecialchars($dato->valor) : '') . '" />';
        if ($dato)
            $display.='<p><a href="' . site_url('uploader/datos_get/' . htmlspecialchars ($dato->valor)) . '" target="_blank">' . htmlspecialchars ($dato->valor) . '</a></p>';
        $display.='</div>';

        return $display;
    }

}