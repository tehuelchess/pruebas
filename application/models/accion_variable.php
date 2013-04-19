<?php
require_once('accion.php');

class AccionVariable extends Accion {

    public function displayForm() {


        $display = '<label>Variable</label>';
        $display.='<div class="input-prepend">';
        $display.='<span class="add-on">@@</span>';
        $display.='<input type="text" name="extra[variable]" value="' . ($this->extra ? $this->extra->variable : '') . '" />';
        $display.='</div>';
        $display.='<label>Expresión a evaluar</label>';
        $display.='<textarea name="extra[expresion]" class="input-xxlarge">' . ($this->extra ? $this->extra->expresion : '') . '</textarea>';

        return $display;
    }

    public function validateForm() {
        $CI = & get_instance();
        $CI->form_validation->set_rules('extra[variable]', 'Variable', 'required');
        $CI->form_validation->set_rules('extra[expresion]', 'Expresión a evaluar', 'required');
    }

    public function ejecutar(Etapa $etapa) {
        $regla=new Regla($this->extra->expresion);
        $valor=$regla->evaluar($etapa->tramite_id);
        
        $dato = Doctrine::getTable('Dato')->findOneByTramiteIdAndNombre($etapa->tramite_id, $this->extra->variable);
        if (!$dato)
            $dato = new Dato();
        $dato->nombre = $this->extra->variable;
        $dato->valor = $valor;
        $dato->tramite_id = $etapa->tramite_id;
        $dato->save(null,$etapa);
    }

}
