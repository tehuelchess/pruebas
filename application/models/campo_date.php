<?php

class CampoDate extends Campo{
    

    public function display($modo = 'edicion', $tramite_id = NULL) {
        $dato=NULL;
        if($tramite_id)
            $dato=$this->getDatoDeTramite($tramite_id);
        
        $display='<label>' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';    
        $display.='<input class="datepicker" ' . ($modo == 'visualizacion' ? 'disabled' : '') . ' type="text" value="' . ($dato?date('d/m/Y',mysql_to_unix($dato->valor)):'') . '" />';
        $display.='<input type="hidden" name="'.$this->nombre.'" value="" />';
        
        return $display;
    }
    
}