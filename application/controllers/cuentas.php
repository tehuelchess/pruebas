<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Cuentas extends MY_Controller {

    public function __construct() {
        parent::__construct();

        
        
        if(!UsuarioSesion::usuario()->registrado){
            echo 'Usuario no registrado en el sistema.';
        }
    }

    public function editar() {
        $data['usuario']=UsuarioSesion::usuario();
        $data['redirect']=$this->session->flashdata('redirect');
        
        $data['content'] = 'cuenta/editar';
        $data['title'] = 'Edita tu información';
        $this->load->view('template', $data);
    }
    
    public function editar_form(){
        $this->form_validation->set_rules('nombre','Nombre','required');
        $this->form_validation->set_rules('apellidos','Apellidos','required');
        $this->form_validation->set_rules('email','Correo electrónico','required|valid_email');
        
        $respuesta=new stdClass();
        if($this->form_validation->run()==TRUE){
            $usuario=UsuarioSesion::usuario();
            $usuario->nombre=$this->input->post('nombre');
            $usuario->apellidos=$this->input->post('apellidos');
            $usuario->email=$this->input->post('email');
            $usuario->save();
            
            $respuesta->validacion=TRUE;
            $redirect=$this->input->post('redirect');
            if(!$redirect)
                $respuesta->redirect=site_url();
            else
                $respuesta->redirect=$redirect;
            
        }else{
            $respuesta->validacion=FALSE;
            $respuesta->errores=validation_errors();
        }
        
        echo json_encode($respuesta);
    }
    
    public function editar_password() {
        $data['usuario']=UsuarioSesion::usuario();
        $data['redirect']=$this->input->server('HTTP_REFERER');
        
        $data['content'] = 'cuenta/editar_password';
        $data['title'] = 'Edita tu información';
        $this->load->view('template', $data);
    }
    
    public function editar_password_form(){
        $this->form_validation->set_rules('password_old','Contraseña antigua','required|callback_check_password');
        $this->form_validation->set_rules('password_new','Contraseña nueva','required');
        $this->form_validation->set_rules('password_new_confirm','Confirmar contraseña nueva','required|matches[password_new]');
        
        if($this->form_validation->run()==TRUE){
            $usuario=UsuarioSesion::usuario();
            $usuario->password=$this->input->post('password_new');
            $usuario->save();
            
            $respuesta->validacion=TRUE;
            $respuesta->redirect=$this->input->post('redirect');
            
        }else{
            $respuesta->validacion=FALSE;
            $respuesta->errores=validation_errors();
        }
        
        echo json_encode($respuesta);
    }

    function check_password($password){
        $autorizacion=UsuarioSesion::validar_acceso(UsuarioSesion::usuario()->usuario,$this->input->post('password_old'));
        
        if($autorizacion)
            return TRUE;
        
        $this->form_validation->set_message('check_password','Usuario y/o contraseña incorrecta.');
        return FALSE;
        
    }

}
