<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Acciones extends CI_Controller {

    public function __construct() {
        parent::__construct();

        UsuarioBackendSesion::force_login();
    }

    public function listar($proceso_id) {
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if ($proceso->cuenta_id != UsuarioBackendSesion::usuario()->cuenta_id) {
            echo 'Usuario no tiene permisos para listar los formularios de este proceso';
            exit;
        }
        $data['proceso'] = $proceso;
        $data['acciones'] = $data['proceso']->Acciones;

        $data['title'] = 'Triggers';
        $data['content'] = 'backend/acciones/index';

        $this->load->view('backend/template', $data);
    }
    
    public function ajax_seleccionar($proceso_id){
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if ($proceso->cuenta_id != UsuarioBackendSesion::usuario()->cuenta_id) {
            echo 'Usuario no tiene permisos para listar los formularios de este proceso';
            exit;
        }
        
        $data['proceso_id']=$proceso_id;
        $this->load->view('backend/acciones/ajax_seleccionar',$data);
    }
    
    public function seleccionar_form($proceso_id){
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if ($proceso->cuenta_id != UsuarioBackendSesion::usuario()->cuenta_id) {
            echo 'Usuario no tiene permisos para listar los formularios de este proceso';
            exit;
        }
        
        $this->form_validation->set_rules('tipo','Tipo','required');
        
        if($this->form_validation->run()==TRUE){
            $tipo=$this->input->post('tipo');
            $respuesta->validacion=TRUE;
            $respuesta->redirect=site_url('backend/acciones/crear/'.$proceso_id.'/'.$tipo);
        }else{
            $respuesta->validacion=FALSE;
            $respuesta->errores=validation_errors();
        }
        
        echo json_encode($respuesta);
    }
    
    public function crear($proceso_id,$tipo){
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if ($proceso->cuenta_id != UsuarioBackendSesion::usuario()->cuenta_id) {
            echo 'Usuario no tiene permisos para listar los formularios de este proceso';
            exit;
        }
        
        if($tipo=='enviar_correo')
            $accion=new AccionEnviarCorreo();
        
        $data['edit']=FALSE;
        $data['proceso']=$proceso;
        $data['tipo']=$tipo;
        $data['accion']=$accion;
        
        $data['content']='backend/acciones/editar';
        $data['title']='Crear Acción';
        $this->load->view('backend/template',$data);
    }
    
    public function editar($accion_id){
        $accion = Doctrine::getTable('Accion')->find($accion_id);

        if ($accion->Proceso->cuenta_id != UsuarioBackendSesion::usuario()->cuenta_id) {
            echo 'Usuario no tiene permisos para listar los formularios de este proceso';
            exit;
        }
        
        
        $data['edit']=TRUE;
        $data['proceso']=$accion->Proceso;
        $data['accion']=$accion;
        
        $data['content']='backend/acciones/editar';
        $data['title']='Editar Acción';
        $this->load->view('backend/template',$data);
    }
    
    public function editar_form($accion_id=NULL){
        $accion=NULL;
        if($accion_id){
            $accion=Doctrine::getTable('Accion')->find($accion_id);

            if($accion->Proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
                echo 'Usuario no tiene permisos para editar esta accion.';
                exit;
            }
        }
        
        $this->form_validation->set_rules('nombre','Nombre','required');
        if(!$accion_id){
            $this->form_validation->set_rules('proceso_id','Proceso','required|callback_check_permiso_proceso');
            $this->form_validation->set_rules('tipo','Tipo de Campo','required');
        }
        
        if($this->form_validation->run()==TRUE){
            if(!$accion){
                $accion=new Accion();
                $accion->proceso_id=$this->input->post('proceso_id');
                $accion->tipo=$this->input->post('tipo');
            }
            
            $accion->nombre=$this->input->post('nombre');
            $accion->extra=$this->input->post('extra');
            $accion->save();
            
            $respuesta->validacion=TRUE;
            $respuesta->redirect=site_url('backend/acciones/listar/'.$accion->Proceso->id);
        }else{
            $respuesta->validacion=FALSE;
            $respuesta->errores=validation_errors();
        }
        
        echo json_encode($respuesta);
    }
    
    public function eliminar($accion_id){
        $accion=Doctrine::getTable('Accion')->find($accion_id);
        
        if($accion->Proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para eliminar esta accion.';
            exit;
        }
        
        $proceso=$accion->Proceso;
        $accion->delete();
        
        redirect('backend/acciones/listar/'.$proceso->id);
        
    }
    
    public function check_permiso_proceso($proceso_id){
        $proceso=Doctrine::getTable('Proceso')->find($proceso_id);
        
        if($proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            $this->form_validation->set_message('check_permiso_proceso' ,'Usuario no tiene permisos para agregar acciones a este proceso.');
            return FALSE;
        }
        
        return TRUE;
    }
    
    

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */