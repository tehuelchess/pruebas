<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Autenticacion extends CI_Controller {
    public function  __construct() {
        parent::__construct();
    }
    
    public function login(){
        $data['redirect']=$this->session->flashdata('redirect');
        
        $this->load->view('autenticacion/login', $data);
    }

    public function login_form() {

        $this->form_validation->set_rules('usuario', 'Usuario', 'required');
        $this->form_validation->set_rules('password', 'Contraseña', 'required|callback_check_password');

        if ($this->form_validation->run() == TRUE) {
            UsuarioSesion::login($this->input->post('usuario'),$this->input->post('password'));
            $respuesta->validacion=TRUE;
            $respuesta->redirect=$this->input->post('redirect')?$this->input->post('redirect'):site_url();
            
        }else{
            $respuesta->validacion=FALSE;
            $respuesta->errores=validation_errors();
        }
        
        echo json_encode($respuesta);

    }
    
    public function registrar(){        
        $this->load->view('autenticacion/registrar');
    }
    
    public function registrar_form() {
        $this->form_validation->set_rules('usuario', 'Usuario', 'required|callback_check_usuario');
        $this->form_validation->set_rules('password', 'Contraseña', 'required|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', 'Confirmar contraseña');
        $this->form_validation->set_rules('email', 'Correo electrónico','valid_email');

        if ($this->form_validation->run() == TRUE) {
            $usuario=new Usuario();
            $usuario->usuario=$this->input->post('usuario');
            $usuario->password=$this->input->post('password');
            $usuario->email=$this->input->post('email');
            $usuario->save();
            
            UsuarioSesion::login($this->input->post('usuario'),$this->input->post('password'));
            
            $respuesta->validacion=TRUE;
            $respuesta->redirect=site_url();
            
        }else{
            $respuesta->validacion=FALSE;
            $respuesta->errores=validation_errors();
        }
        
        echo json_encode($respuesta);

    }


    function logout() {
        UsuarioSesion::logout();
        redirect($this->input->server('HTTP_REFERER'));
    }


    function check_password($password){
        $autorizacion=UsuarioSesion::validar_acceso($this->input->post('usuario'),$this->input->post('password'));
        
        if($autorizacion)
            return TRUE;
        
        $this->form_validation->set_message('check_password','Usuario y/o contraseña incorrecta.');
        return FALSE;
        
    }
    
    function check_usuario($usuario){
        $usuario=Doctrine::getTable('Usuario')->findOneByUsuario($usuario);
        
        if(!$usuario)
            return TRUE;
        
        $this->form_validation->set_message('check_usuario','Usuario ya existe.');
        return FALSE;
        
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
