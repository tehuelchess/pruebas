<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Configuracion extends MY_Controller {

    public function __construct() {
        parent::__construct();

        UsuarioBackendSesion::force_login();
    }

    public function index() {
        redirect('backend/configuracion/misitio');
    }
    
    public function cuenta(){
        $data['usuario']= UsuarioBackendSesion::usuario();
        
        $data['title'] = 'Configuración de Cuenta';
        $data['content'] = 'backend/configuracion/cuenta';

        $this->load->view('backend/template', $data);
    }
    
    public function cuenta_form(){
        $this->form_validation->set_rules('password', 'Contraseña', 'matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', 'Confirmar contraseña');

        if ($this->form_validation->run() == TRUE) {
            $usuario=UsuarioBackendSesion::usuario();
            $usuario->password=$this->input->post('password');
            $usuario->save();
            
            $this->session->set_flashdata('message','Cuenta actualizada con éxito.');

            $respuesta->validacion = TRUE;
            $respuesta->redirect = site_url('backend/configuracion/cuenta');
        }else {
            $respuesta->validacion = FALSE;
            $respuesta->errores = validation_errors();
        }

        echo json_encode($respuesta);
    }

    public function grupos_usuarios() {
        $data['grupos_usuarios'] = Doctrine::getTable('GrupoUsuarios')->findByCuentaId(UsuarioBackendSesion::usuario()->cuenta_id);

        $data['title'] = 'Configuración de Grupos de Usuarios';
        $data['content'] = 'backend/configuracion/grupos_usuarios';

        $this->load->view('backend/template', $data);
    }

    public function grupo_usuarios_editar($grupo_usuarios_id = NULL) {
        if ($grupo_usuarios_id) {
            $grupo_usuarios = Doctrine::getTable('GrupoUsuarios')->find($grupo_usuarios_id);

            if ($grupo_usuarios->cuenta_id != UsuarioBackendSesion::usuario()->cuenta_id) {
                echo 'No tiene permisos para editar este grupo de usuarios';
                exit;
            }

            $data['grupo_usuarios'] = $grupo_usuarios;
        }

        $data['usuarios']=Doctrine::getTable('Usuario')->findByRegistradoAndOpenId(1,0);
        
        $data['title'] = 'Configuración de Grupo de Usuarios';
        $data['content'] = 'backend/configuracion/grupo_usuarios_editar';

        $this->load->view('backend/template', $data);
    }

    public function grupo_usuarios_editar_form($grupo_usuarios_id = NULL) {
        $grupo_usuarios=NULL;
        if ($grupo_usuarios_id) {
            $grupo_usuarios = Doctrine::getTable('GrupoUsuarios')->find($grupo_usuarios_id);

            if ($grupo_usuarios->cuenta_id != UsuarioBackendSesion::usuario()->cuenta_id) {
                echo 'No tiene permisos para editar este grupo de usuarios';
                exit;
            }
        }

        $this->form_validation->set_rules('nombre', 'Nombre', 'required');

        if ($this->form_validation->run() == TRUE) {
            if (!$grupo_usuarios)
                $grupo_usuarios = new GrupoUsuarios();

            $grupo_usuarios->nombre = $this->input->post('nombre');
            $grupo_usuarios->cuenta_id = UsuarioBackendSesion::usuario()->cuenta_id;
            $grupo_usuarios->setUsuariosFromArray($this->input->post('usuarios'));
            $grupo_usuarios->save();

            $respuesta->validacion = TRUE;
            $respuesta->redirect = site_url('backend/configuracion/grupos_usuarios');
        }else {
            $respuesta->validacion = FALSE;
            $respuesta->errores = validation_errors();
        }

        echo json_encode($respuesta);
    }

    public function grupo_usuarios_eliminar($grupo_usuarios_id) {
        $grupo_usuarios = Doctrine::getTable('GrupoUsuarios')->find($grupo_usuarios_id);

        if ($grupo_usuarios->cuenta_id != UsuarioBackendSesion::usuario()->cuenta_id) {
            echo 'No tiene permisos para eliminar este grupo de usuarios';
            exit;
        }

        $grupo_usuarios->delete();

        redirect('backend/configuracion/grupos_usuarios');
    }

    public function usuarios() {
        $data['usuarios'] = Doctrine::getTable('Usuario')->findByCuentaId(UsuarioBackendSesion::usuario()->cuenta_id);

        $data['title'] = 'Configuración de Usuarios';
        $data['content'] = 'backend/configuracion/usuarios';

        $this->load->view('backend/template', $data);
    }

    public function usuario_editar($usuario_id = NULL) {
        if ($usuario_id) {
            $usuario = Doctrine::getTable('Usuario')->find($usuario_id);

            if ($usuario->cuenta_id != UsuarioBackendSesion::usuario()->cuenta_id) {
                echo 'Usuario no tiene permisos para editar este usuario.';
                exit;
            }

            $data['usuario'] = $usuario;
        }
        $data['grupos_usuarios'] = Doctrine::getTable('GrupoUsuarios')->findByCuentaId(UsuarioBackendSesion::usuario()->cuenta_id);

        $data['title'] = 'Configuración de Usuarios';
        $data['content'] = 'backend/configuracion/usuario_editar';

        $this->load->view('backend/template', $data);
    }

    public function usuario_editar_form($usuario_id = NULL) {
        $usuario=NULL;
        if ($usuario_id) {
            $usuario = Doctrine::getTable('Usuario')->find($usuario_id);

            if ($usuario->cuenta_id != UsuarioBackendSesion::usuario()->cuenta_id) {
                echo 'Usuario no tiene permisos para editar este usuario.';
                exit;
            }
        }

        $this->form_validation->set_rules('usuario', 'Nombre de Usuario', 'required');
        $this->form_validation->set_rules('password', 'Contraseña', 'matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', 'Confirmar contraseña');
        $this->form_validation->set_rules('nombre', 'Nombre', 'required');
        $this->form_validation->set_rules('apellidos', 'Apellidos', 'required');
        $this->form_validation->set_rules('email', 'Correo electrónico', 'valid_email');

        if ($this->form_validation->run() == TRUE) {
            if (!$usuario)
                $usuario = new Usuario();

            $usuario->usuario = $this->input->post('usuario');
            if($this->input->post('password')) $usuario->password=$this->input->post('password');
            $usuario->nombre = $this->input->post('nombre');
            $usuario->apellidos = $this->input->post('apellidos');
            $usuario->email = $this->input->post('email');
            $usuario->vacaciones = $this->input->post('vacaciones');
            $usuario->setGruposUsuariosFromArray($this->input->post('grupos_usuarios'));
            $usuario->cuenta_id = UsuarioBackendSesion::usuario()->cuenta_id;
            $usuario->save();

            $respuesta->validacion = TRUE;
            $respuesta->redirect = site_url('backend/configuracion/usuarios');
        }else {
            $respuesta->validacion = FALSE;
            $respuesta->errores = validation_errors();
        }

        echo json_encode($respuesta);
    }

    public function usuario_eliminar($usuario_id) {
        $usuario = Doctrine::getTable('Usuario')->find($usuario_id);

        if ($usuario->cuenta_id != UsuarioBackendSesion::usuario()->cuenta_id) {
            echo 'Usuario no tiene permisos para eliminar este usuario.';
            exit;
        }

        $usuario->delete();

        redirect('backend/configuracion/usuarios');
    }
    
    public function misitio(){
        $data['cuenta']=Doctrine::getTable('Cuenta')->find(UsuarioBackendSesion::usuario()->cuenta_id);
        
        $data['title'] = 'Configuración de Usuarios';
        $data['content'] = 'backend/configuracion/misitio';
        $this->load->view('backend/template', $data);
    }
    
    public function misitio_form() {      
        $this->form_validation->set_rules('nombre_largo', 'Nombre largo', 'required');
        
        if ($this->form_validation->run() == TRUE) {
            $cuenta=Doctrine::getTable('Cuenta')->find(UsuarioBackendSesion::usuario()->cuenta_id);

            $cuenta->nombre_largo=$this->input->post('nombre_largo');
            $cuenta->logo=$this->input->post('logo');
            $cuenta->save();

            $respuesta->validacion = TRUE;
            $respuesta->redirect = site_url('backend/configuracion/misitio');
        }else {
            $respuesta->validacion = FALSE;
            $respuesta->errores = validation_errors();
        }

        echo json_encode($respuesta);
    }


}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */