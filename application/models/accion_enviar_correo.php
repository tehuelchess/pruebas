<?php
require_once('accion.php');

class AccionEnviarCorreo extends Accion {

    public function displayForm() {


        $display = '<label>Para</label>';
        $display.='<input type="text" name="extra[para]" value="' . (isset($this->extra->para) ? $this->extra->para : '') . '" />';
        $display.= '<label>CC</label>';
        $display.='<input type="text" name="extra[cc]" value="' . (isset($this->extra->cc) ? $this->extra->cc : '') . '" />';
        $display.= '<label>CCO</label>';
        $display.='<input type="text" name="extra[cco]" value="' . (isset($this->extra->cco) ? $this->extra->cco : '') . '" />';
        $display.='<label>Tema</label>';
        $display.='<input type="text" name="extra[tema]" value="' . (isset($this->extra->tema) ? $this->extra->tema : '') . '" />';
        $display.='<label>Contenido</label>';
        $display.='<textarea name="extra[contenido]">' . (isset($this->extra->contenido) ? $this->extra->contenido : '') . '</textarea>';

        return $display;
    }

    public function validateForm() {
        $CI = & get_instance();
        $CI->form_validation->set_rules('extra[para]', 'Para', 'required');
        $CI->form_validation->set_rules('extra[tema]', 'Tema', 'required');
        $CI->form_validation->set_rules('extra[contenido]', 'Contenido', 'required');
    }

    public function ejecutar(Etapa $etapa) {
        $regla=new Regla($this->extra->para);
        $to=$regla->getExpresionParaOutput($etapa->id);
        if(isset($this->extra->cc)){
            $regla=new Regla($this->extra->cc);
            $cc=$regla->getExpresionParaOutput($etapa->id);
        }
        if(isset($this->extra->cco)){
            $regla=new Regla($this->extra->cco);
            $bcc=$regla->getExpresionParaOutput($etapa->id);
        }
        $regla=new Regla($this->extra->tema);
        $subject=$regla->getExpresionParaOutput($etapa->id);
        $regla=new Regla($this->extra->contenido);
        $message=$regla->getExpresionParaOutput($etapa->id);
        
        $CI = & get_instance();
        $cuenta=$etapa->Tramite->Proceso->Cuenta;
        $CI->email->from($cuenta->nombre.'@'.$this->config->item('main_domain'), $cuenta->nombre_largo);
        $CI->email->to($to);
        if(isset($cc))$CI->email->cc($cc);
        if(isset($bcc))$CI->email->bcc($bcc);      
        $CI->email->subject($subject);
        $CI->email->message($message);
        $CI->email->send();
    }

}
