<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Documentos extends CI_Controller {

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
        $data['documentos'] = $data['proceso']->Documentos;

        $data['title'] = 'Documentos';
        $data['content'] = 'backend/documentos/index';

        $this->load->view('backend/template', $data);
    }

    public function crear($proceso_id) {
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);
        
        if ($proceso->cuenta_id != UsuarioBackendSesion::usuario()->cuenta_id) {
            echo 'No tiene permisos para crear este documento';
            exit;
        }

        $data['edit'] = FALSE;
        $data['proceso'] = $proceso;
        $data['title'] = 'Edición de Documento';
        $data['content'] = 'backend/documentos/editar';

        $this->load->view('backend/template', $data);
    }

    public function editar($documento_id) {
        $documento = Doctrine::getTable('Documento')->find($documento_id);

        if ($documento->Proceso->cuenta_id != UsuarioBackendSesion::usuario()->cuenta_id) {
            echo 'No tiene permisos para editar este documento';
            exit;
        }

        $data['documento'] = $documento;
        $data['edit'] = TRUE;
        $data['proceso']=$documento->Proceso;
        $data['title'] = 'Edición de Documento';
        $data['content'] = 'backend/documentos/editar';

        $this->load->view('backend/template', $data);
    }
    
    public function editar_form($documento_id=NULL){
        $documento=NULL;
        if($documento_id){
            $documento=Doctrine::getTable('Documento')->find($documento_id);
        }else{
            $documento=new Documento();
            $documento->proceso_id=$this->input->post('proceso_id');
        }
        
        if($documento->Proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
                echo 'Usuario no tiene permisos para editar este documento.';
                exit;
            }
        
        $this->form_validation->set_rules('nombre','Nombre','required');
        $this->form_validation->set_rules('contenido','Contenido','required');
        
        if($this->form_validation->run()==TRUE){         
            $documento->nombre=$this->input->post('nombre');
            $documento->contenido=$this->input->post('contenido');
            $documento->save();
            
            $respuesta->validacion=TRUE;
            $respuesta->redirect=site_url('backend/documentos/listar/'.$documento->Proceso->id);
        }else{
            $respuesta->validacion=FALSE;
            $respuesta->errores=validation_errors();
        }
        
        echo json_encode($respuesta);
    }
    
    public function previsualizar($documento_id){
        $documento=Doctrine::getTable('Documento')->find($documento_id);
        
        if($documento->Proceso->cuenta_id != UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos';
            exit;
        }
        
        $documento->previsualizar();
    }


    public function eliminar($documento_id){
        $documento=Doctrine::getTable('Documento')->find($documento_id);
        
        if($documento->Proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para eliminar este documento.';
            exit;
        }
        
        $proceso=$documento->Proceso;
        $documento->delete();
        
        redirect('backend/documentos/listar/'.$proceso->id);
        
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */