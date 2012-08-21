<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Cuenta extends CI_Controller {

    public function __construct() {
        parent::__construct();

        UsuarioSesion::force_login();
        
        if(!UsuarioSesion::usuario()->registrado){
            echo 'Usuario no registrado en el sistema.';
        }
    }

    public function editar() {
        $data['usuario']=UsuarioSesion::usuario();
        $data['redirect']=$this->input->server('HTTP_REFERER');
        
        $data['content'] = 'cuenta/editar';
        $data['title'] = 'Edita tu información';
        $this->load->view('template', $data);
    }
    
    public function editar_form(){
        $this->form_validation->set_rules('rut','RUT','rut');
        
        if($this->form_validation->run()==TRUE){
            $usuario=UsuarioSesion::usuario();
            $usuario->rut=$this->input->post('rut');
            $usuario->nombre=$this->input->post('nombre');
            $usuario->apellidos=$this->input->post('apellidos');
            $usuario->email=$this->input->post('email');
            $usuario->save();
            
            $respuesta->validacion=TRUE;
            $respuesta->redirect=$this->input->post('redirect');
            
        }else{
            $respuesta->validacion=FALSE;
            $respuesta->errores=validation_errors();
        }
        
        echo json_encode($respuesta);
    }


}
