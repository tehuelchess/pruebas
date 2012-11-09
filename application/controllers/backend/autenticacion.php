<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Autenticacion extends MY_Controller {
    public function  __construct() {
        parent::__construct();
    }
    
    public function login(){
        $data['redirect']=$this->session->flashdata('redirect');
        
        $this->load->view('backend/autenticacion/login', $data);
    }

    public function login_form() {

        $this->form_validation->set_rules('usuario', 'Usuario', 'required');
        $this->form_validation->set_rules('password', 'Contraseña', 'required|callback_check_password');

        if ($this->form_validation->run() == TRUE) {
            UsuarioBackendSesion::login($this->input->post('usuario'),$this->input->post('password'));
            $respuesta->validacion=TRUE;
            $respuesta->redirect=$this->input->post('redirect')?$this->input->post('redirect'):site_url('backend');
            
        }else{
            $respuesta->validacion=FALSE;
            $respuesta->errores=validation_errors();
        }
        
        echo json_encode($respuesta);

    }


    function logout() {
        UsuarioBackendSesion::logout();
        redirect($this->input->server('HTTP_REFERER'));
    }


    function check_password($password){
        $autorizacion=UsuarioBackendSesion::validar_acceso($this->input->post('usuario'),$this->input->post('password'));
        
        if($autorizacion)
            return TRUE;
        
        $this->form_validation->set_message('check_password','Usuario y/o contraseña incorrecta.');
        return FALSE;
        
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
