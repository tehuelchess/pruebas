<?php

class Validador extends MY_Controller {

    function __construct() {
        parent::__construct();

    }
    
    public function index(){
        redirect('validador/documento');
    }

    public function documento($codigo=null){
        if($codigo)
            $_POST['codigo']=$codigo;
        
        $this->form_validation->set_rules('codigo','Identificador','required|callback_check_documento');
        
        if($this->form_validation->run()==TRUE){
            $codigo=$this->input->post('codigo');
            $filename_copia=$codigo.'.copia.pdf';
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
    
    public function check_documento($codigo){
        $filename=$codigo.'.pdf';
        
        $file=Doctrine_Query::create()
                ->from('File f')
                ->where('f.filename = ? AND f.tipo = ?',array($filename,'documento'))
                ->fetchOne();
        
        if(!$file){
            $this->form_validation->set_message('check_documento','Documento no existe.');
            return FALSE;
        }
        
        return TRUE;
    }
}

?>
