<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Configuracion extends CI_Controller {
    
    public function __construct() {
        parent::__construct();

        UsuarioBackendSesion::force_login();
    }

    public function index() {
        redirect('backend/configuracion/usuarios');
    }
    
    public function grupos_usuarios(){
        $data['grupos_usuarios']=Doctrine::getTable('GrupoUsuarios')->findByCuentaId(UsuarioBackendSesion::usuario()->cuenta_id);
        
        $data['title']='Configuración de Grupos de Usuarios';
        $data['content']='backend/configuracion/grupos_usuarios';
        
        $this->load->view('backend/template',$data);
    }
    
    public function grupo_usuarios_editar($grupo_usuarios_id=NULL){
        if($grupo_usuarios_id)
            $data['grupo_usuarios']=Doctrine::getTable('GrupoUsuarios')->find($grupo_usuarios_id);
        
        $data['title']='Configuración de Grupo de Usuarios';
        $data['content']='backend/configuracion/grupo_usuarios_editar';
        
        $this->load->view('backend/template',$data);
    }
    
    public function grupo_usuarios_editar_form($grupo_usuarios_id=NULL){
        $this->form_validation->set_rules('nombre','Nombre','required');
        
        if($this->form_validation->run()==TRUE){
            if($grupo_usuarios_id)
                $grupo_usuarios=Doctrine::getTable('GrupoUsuarios')->find($grupo_usuarios_id);
            else
                $grupo_usuarios=new GrupoUsuarios();
            
            $grupo_usuarios->nombre=$this->input->post('nombre');
            $grupo_usuarios->cuenta_id=UsuarioBackendSesion::usuario()->cuenta_id;
            $grupo_usuarios->save();
            
            $respuesta->validacion=TRUE;
            $respuesta->redirect=site_url('backend/configuracion/grupos_usuarios');
        }else{
            $respuesta->validacion=FALSE;
            $respuesta->errores=validation_errors();
        }
        
        echo json_encode($respuesta);
    }
    
    public function grupo_usuarios_eliminar($grupo_usuarios_id){
        $grupo_usuarios=Doctrine::getTable('GrupoUsuarios')->find($grupo_usuarios_id);
        $grupo_usuarios->delete();
        
        redirect('backend/configuracion/grupos_usuarios');
    }
    
    public function usuarios(){
        $data['usuarios']=Doctrine::getTable('Usuario')->findByCuentaId(UsuarioBackendSesion::usuario()->cuenta_id);
        
        $data['title']='Configuración de Usuarios';
        $data['content']='backend/configuracion/usuarios';
        
        $this->load->view('backend/template',$data);
    }
    
    public function usuario_editar($usuario_id=NULL){
        if($usuario_id)
            $data['usuario']=Doctrine::getTable('Usuario')->find($usuario_id);
        $data['grupos_usuarios']=Doctrine::getTable('GrupoUsuarios')->findByCuentaId(UsuarioBackendSesion::usuario()->cuenta_id);
        
        $data['title']='Configuración de Usuarios';
        $data['content']='backend/configuracion/usuario_editar';
        
        $this->load->view('backend/template',$data);
    }
    
    public function usuario_editar_form($usuario_id=NULL){
        $this->form_validation->set_rules('usuario','Nombre de Usuario','required');
        $this->form_validation->set_rules('nombre','Nombre','required');
        $this->form_validation->set_rules('apellidos','Apellidos','required');
        $this->form_validation->set_rules('grupos_usuarios','Grupos de Usuarios','required');
        
        if($this->form_validation->run()==TRUE){
            if($usuario_id)
                $usuario=Doctrine::getTable('Usuario')->find($usuario_id);
            else
                $usuario=new Usuario();
            
            $usuario->usuario=$this->input->post('usuario');
            $usuario->nombre=$this->input->post('nombre');
            $usuario->apellidos=$this->input->post('apellidos');
            $usuario->setGruposUsuariosFromArray($this->input->post('grupos_usuarios'));
            $usuario->cuenta_id=UsuarioBackendSesion::usuario()->cuenta_id;
            $usuario->save();
            
            $respuesta->validacion=TRUE;
            $respuesta->redirect=site_url('backend/configuracion/usuarios');
        }else{
            $respuesta->validacion=FALSE;
            $respuesta->errores=validation_errors();
        }
        
        echo json_encode($respuesta);
    }
    
    public function usuario_eliminar($usuario_id){
        $usuario=Doctrine::getTable('Usuario')->find($usuario_id);
        $usuario->delete();
        
        redirect('backend/configuracion/usuarios');
    }

    
    

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */