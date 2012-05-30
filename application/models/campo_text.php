<?php

class CampoText extends Campo{
    

    public function display($modo = 'edicion', $tramite_id = NULL) {
        $dato=NULL;
        if($tramite_id)
            $dato=$this->getDatoDeTramite($tramite_id);
        
        $display='<label>' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';    
        $display.='<input ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' type="text" name="' . $this->nombre . '" value="' . ($dato?$dato->valor:'') . '" />';
    
        return $display;
    }
    
}