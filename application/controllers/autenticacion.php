<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Autenticacion extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function login_openid() {
        $this->load->library('LightOpenID');
        $redirect = $this->input->get('redirect') ? $this->input->get('redirect') : site_url();
        $this->lightopenid->returnUrl = $redirect;
        $this->lightopenid->required = array('person/guid');
        redirect($this->lightopenid->authUrl());
    }

    public function login_form() {

        $this->form_validation->set_rules('usuario', 'Usuario', 'required');
        $this->form_validation->set_rules('password', 'Contraseña', 'required|callback_check_password');

        $respuesta = new stdClass();
        if ($this->form_validation->run() == TRUE) {
            UsuarioSesion::login($this->input->post('usuario'), $this->input->post('password'));
            $respuesta->validacion = TRUE;
            $respuesta->redirect = $this->input->post('redirect') ? $this->input->post('redirect') : site_url();
        } else {
            $respuesta->validacion = FALSE;
            $respuesta->errores = validation_errors();
        }

        echo json_encode($respuesta);
    }

    public function login() {
        $data['redirect'] = $this->session->flashdata('redirect');

        $data['title'] = 'Login';
        $this->load->view('autenticacion/login', $data);
    }

    public function registrar() {
        $data['title'] = 'Registro';
        $this->load->view('autenticacion/registrar', $data);
    }

    public function registrar_form() {
        $this->form_validation->set_rules('usuario', 'Nombre de Usuario', 'required|alpha_dash|callback_check_usuario');
        $this->form_validation->set_rules('nombres', 'Nombres', 'required');
        $this->form_validation->set_rules('apellido_paterno', 'Apellido Paterno', 'required');
        $this->form_validation->set_rules('apellido_materno', 'Apellido Materno', 'required');
        $this->form_validation->set_rules('password', 'Contraseña', 'required|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', 'Confirmar contraseña');
        $this->form_validation->set_rules('email', 'Correo electrónico', 'required|valid_email');

        if ($this->form_validation->run() == TRUE) {
            $usuario = new Usuario();
            $usuario->usuario = $this->input->post('usuario');
            $usuario->setPasswordWithSalt($this->input->post('password'));
            $usuario->email = $this->input->post('email');
            $usuario->save();
            
            $cuenta=Cuenta::cuentaSegunDominio();
            if(is_a($cuenta, 'Cuenta'))
                $this->email->from($cuenta->nombre.'@chilesinpapeleo.cl', $cuenta->nombre_largo);
            else
                $this->email->from('simple@chilesinpapeleo.cl', 'Simple');
            $this->email->to($usuario->email);
            $this->email->subject('Bienvenido');
            $this->email->message('<p>Usted ya es parte de la plataforma para hacer trámites en línea "Chile Sin Papeleo".</p><p>Su nombre de usuario es: '.$usuario->usuario.'</p>');
            $this->email->send();

            UsuarioSesion::login($this->input->post('usuario'), $this->input->post('password'));

            $respuesta->validacion = TRUE;
            $respuesta->redirect = site_url();
        } else {
            $respuesta->validacion = FALSE;
            $respuesta->errores = validation_errors();
        }

        echo json_encode($respuesta);
    }

    public function olvido() {        
        $data['title']='Olvide mi contraseña';
        $this->load->view('autenticacion/olvido',$data);
    }

    public function olvido_form() {
        $this->form_validation->set_rules('usuario', 'Usuario', 'required|callback_check_usuario_existe');

        $respuesta=new stdClass();
        if ($this->form_validation->run() == TRUE) {
            $random=random_string('alnum',16);
            
            $usuario = Doctrine::getTable('Usuario')->findOneByUsuario($this->input->post('usuario'));
            $usuario->reset_token=$random;
            $usuario->save();

            $cuenta=Cuenta::cuentaSegunDominio();
            if(is_a($cuenta, 'Cuenta'))
                $this->email->from($cuenta->nombre.'@chilesinpapeleo.cl', $cuenta->nombre_largo);
            else
                $this->email->from('simple@chilesinpapeleo.cl', 'Simple');
            $this->email->to($usuario->email);
            $this->email->subject('Reestablecer contraseña');
            $this->email->message('<p>Haga click en el siguiente link para reestablecer su contraseña:</p><p><a href="'.site_url('autenticacion/reestablecer?id='.$usuario->id.'&reset_token='.$random).'">'.site_url('autenticacion/reestablecer?id='.$usuario->id.'&reset_token='.$random).'</a></p>');
            $this->email->send();
            
            $this->session->set_flashdata('message','Se le ha enviado un correo con instrucciones de como reestablecer su contraseña.');
            
            $respuesta->validacion = TRUE;
            $respuesta->redirect = site_url('autenticacion/login');
        } else {
            $respuesta->validacion = FALSE;
            $respuesta->errores = validation_errors();
        }

        echo json_encode($respuesta);
    }
    
    public function reestablecer(){
        $id=$this->input->get('id');
        $reset_token=$this->input->get('reset_token');
        
        $usuario=Doctrine::getTable('Usuario')->find($id);
        
        if(!$usuario){
            echo 'Usuario no existe';
            exit;
        }
        if(!$reset_token){
            echo 'Faltan parametros';
            exit;
        }
        
        $usuario_input=new Usuario();
        $usuario_input->reset_token=$reset_token;
        
        if($usuario->reset_token!=$usuario_input->reset_token){
            echo 'Token incorrecto';
            exit;
        }
        
        $data['usuario']=$usuario;
        $data['title']='Reestablecer';
        $this->load->view('autenticacion/reestablecer',$data);  
    }
    
    public function reestablecer_form(){
        $id=$this->input->get('id');
        $reset_token=$this->input->get('reset_token');
        
        $usuario=Doctrine::getTable('Usuario')->find($id);
        
        if(!$usuario){
            echo 'Usuario no existe';
            exit;
        }
        if(!$reset_token){
            echo 'Faltan parametros';
            exit;
        }
        
        $usuario_input=new Usuario();
        $usuario_input->reset_token=$reset_token;
        
        if($usuario->reset_token!=$usuario_input->reset_token){
            echo 'Token incorrecto';
            exit;
        }
        
        $this->form_validation->set_rules('password','Contraseña','required');
        $this->form_validation->set_rules('password_confirm','Confirmar contraseña','required|matches[password]');
        
        $respuesta=new stdClass();
        if ($this->form_validation->run() == TRUE) {
            $usuario->password=$this->input->post('password');
            $usuario->reset_token=null;
            $usuario->save();
            
            $this->session->set_flashdata('message','Su contraseña se ha reestablecido.');
            
            $respuesta->validacion = TRUE;
            $respuesta->redirect = site_url('autenticacion/login');
        } else {
            $respuesta->validacion = FALSE;
            $respuesta->errores = validation_errors();
        }

        echo json_encode($respuesta);
    }

    function logout() {
        UsuarioSesion::logout();
        redirect('');
    }

    function check_password($password) {
        $autorizacion = UsuarioSesion::validar_acceso($this->input->post('usuario'), $this->input->post('password'));

        if ($autorizacion)
            return TRUE;

        $this->form_validation->set_message('check_password', 'Usuario y/o contraseña incorrecta.');
        return FALSE;
    }

    function check_usuario($usuario) {
        $usuario = Doctrine::getTable('Usuario')->findOneByUsuario($usuario);

        if (!$usuario)
            return TRUE;

        $this->form_validation->set_message('check_usuario', 'Usuario ya existe.');
        return FALSE;
    }

    function check_usuario_existe($usuario) {
        $usuario = Doctrine::getTable('Usuario')->findOneByUsuario($usuario);

        if ($usuario)
            return TRUE;

        $this->form_validation->set_message('check_usuario_existe', 'Usuario no existe.');
        return FALSE;
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
