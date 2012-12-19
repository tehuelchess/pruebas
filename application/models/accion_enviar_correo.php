<?php
require_once('accion.php');

class AccionEnviarCorreo extends Accion {

    public function displayForm() {


        $display = '<label>Para</label>';
        $display.='<input type="text" name="extra[para]" value="' . ($this->extra ? $this->extra->para : '') . '" />';
        $display.='<label>Tema</label>';
        $display.='<input type="text" name="extra[tema]" value="' . ($this->extra ? $this->extra->tema : '') . '" />';
        $display.='<label>Contenido</label>';
        $display.='<textarea name="extra[contenido]">' . ($this->extra ? $this->extra->contenido : '') . '</textarea>';

        return $display;
    }

    public function validateForm() {
        $CI = & get_instance();
        $CI->form_validation->set_rules('extra[para]', 'Para', 'required');
        $CI->form_validation->set_rules('extra[tema]', 'Tema', 'required');
        $CI->form_validation->set_rules('extra[contenido]', 'Contenido', 'required');
    }

    public function ejecutar($tramite_id) {
        $regla=new Regla($this->extra->para);
        $to=$regla->getExpresionParaOutput($tramite_id);
        $regla=new Regla($this->extra->tema);
        $subject=$regla->getExpresionParaOutput($tramite_id);
        $regla=new Regla($this->extra->contenido);
        $message=$regla->getExpresionParaOutput($tramite_id);
        
        $CI = & get_instance();
        $CI->email->from($CI->config->item('email_from'), 'Tramitador');
        $CI->email->to($to);
        $CI->email->subject($subject);
        $CI->email->message($message);
        $CI->email->send();
    }

}
