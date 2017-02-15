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
        $display.='<label>Adjunto (para más de un archivo separar por comas) </label>';
        $display.='<textarea name="extra[adjunto]">' . (isset($this->extra->adjunto) ? $this->extra->adjunto : '') . '</textarea>';

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
        $CI->email->from($cuenta->nombre.'@'.$CI->config->item('main_domain'), $cuenta->nombre_largo);
        $CI->email->to($to);
        if(isset($cc))$CI->email->cc($cc);
        if(isset($bcc))$CI->email->bcc($bcc);

        if(isset($this->extra->adjunto)){
            $attachments = explode(",",trim($this->extra->adjunto));
            $array_files = array();
            $ruta_tmp = 'uploads/tmp/';
            foreach ($attachments as $a) {
                $regla=new Regla($a);
                $filename=$regla->getExpresionParaOutput($etapa->id);
                $file=Doctrine_Query::create()
                    ->from('File f, f.Tramite t')
                    ->where('f.filename = ? AND t.id = ?',array($filename,$etapa->Tramite->id))
                    ->fetchOne();
                if($file){
                    //integración con alfresco
                    $swrepo=false;
                    $cms=null;
                    try{
                        $cms=new Config_cms_alfresco();
                        $cms->setAccount(UsuarioSesion::usuario()->cuenta_id);
                        $cms->loadData();    
                        if($cms->getCheck()==1){
                            $swrepo=true;
                        }
                    }catch(Exception $err){
                        echo $err->getMessage();
                    }
                    if($swrepo){
                        try{
                            $noderef = str_replace('://', '/', $file->alfresco_noderef);
                            $alfresco = new Alfresco();
                            $file_data=$alfresco->getFile($cms, $noderef);
                            if($cms!=null){
                                $df = finfo_open();
                                $mime_type = finfo_buffer($df, $file_data, FILEINFO_MIME_TYPE);
                                $pathfile=$ruta_tmp.''.$file->filename;
                                file_put_contents($pathfile, $file_data);
                                $CI->email->attach($pathfile);
                                array_push($array_files, $file->filename);
                                $countfile++;
                            }
                        }catch(Exception $err){
                            echo $err->getMessage();
                        }
                    }
                }
            }
        }

        $CI->email->subject($subject);
        $CI->email->message($message);
        $CI->email->send();

        foreach($array_files as $file){
            unlink($ruta_tmp.$file);
        }
    }

}
