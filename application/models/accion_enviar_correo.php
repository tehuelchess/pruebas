<?php

class AccionEnviarCorreo extends Accion {

    
    public function displayForm(){

        
        $display='<label>Para</label>';
        $display.='<input type="text" name="extra[para]" value="'.($this->extra?$this->extra->para:'').'" />';
        $display.='<label>Contenido</label>';
        $display.='<textarea name="extra[contenido]">'.($this->extra?$this->extra->contenido:'').'</textarea>';
        
        return $display;
    }


}
