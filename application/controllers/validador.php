<?php

class Validador extends MY_Controller {

    function __construct() {
        parent::__construct();

    }
    
    public function index(){
        redirect('validador/documento');
    }

    public function documento(){
        if($this->input->get('id'))
            $_POST['id']=$this->input->get('id');
        if($this->input->get('key'))
            $_POST['key']=$this->input->get('key');
        
        $this->form_validation->set_rules('id','Folio','required|callback_check_documento');
        $this->form_validation->set_rules('key','Código de verificación','required');
        
        if($this->form_validation->run()==TRUE){
            $file=Doctrine::getTable('File')->find($this->input->post('id'));
            $filename_copia=  str_replace('.pdf', '.copia.pdf', $file->filename);
            $path='uploads/documentos/'.$filename_copia;
            header('Content-Type: '.  mime_content_type($path));
            header('Content-Length: ' . filesize($path));
            readfile($path);
        }
        
        $this->load->view('validador/documento');
    }
    
    /*
    public function documento_get(){
        
        $codigo=$this->input->get_post('codigo');
        $filename=$codigo.'.pdf';
        $filename_copia=$codigo.'.copia.pdf';
        
        $file=Doctrine_Query::create()
                ->from('File f')
                ->where('f.filename = ? AND f.tipo = ?',array($filename,'documento'))
                ->fetchOne();
        
        if(!$file){
            echo 'Usuario no tiene permisos para ver este archivo.';
            exit;
        }
        
        $path='uploads/documentos/'.$filename_copia;
        
        header('Content-Type: '.  mime_content_type($path));
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }
     * 
     */
    
    public function check_documento($id){  
        $key=$this->input->post('key');
        $key=  preg_replace('/\W/', '', $key);
                
        $file=Doctrine_Query::create()
                ->from('File f')
                ->where('f.id = ?',$id)
                ->fetchOne();
        
        if(!$file){
            $this->form_validation->set_message('check_documento','Folio y/o código no válido.');
            return FALSE;
        }
        

        if($file->llave_copia!=$key){
            $this->form_validation->set_message('check_documento','Folio y/o código no válido.');
            return FALSE;
        }
        
        if($file->validez!==null && now()>strtotime($file->created_at.' + '.$file->validez.' days')){
            $this->form_validation->set_message('check_documento','Documento expiró su periodo de validez.');
            return FALSE;
        }
        
        return TRUE;
    }
}

?>
