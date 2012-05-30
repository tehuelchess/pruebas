<?php

class CampoTextArea extends Campo{
    

    public function display($modo = 'edicion', $tramite_id = NULL) {
        $dato=NULL;
        if($tramite_id)
            $dato=$this->getDatoDeTramite($tramite_id);
        
        $display='<label>' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display.='<textarea ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' name="' . $this->nombre . '">' . ($dato?$dato->valor:'') . '</textarea>';
    
        return $display;
    }
    
}