<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Formularios extends CI_Controller {
    
    public function __construct() {
        parent::__construct();

        UsuarioBackendSesion::force_login();
    }

    public function listar($proceso_id){
        $data['proceso']=Doctrine::getTable('Proceso')->find($proceso_id);
        $data['formularios']=$data['proceso']->Formularios;
        
        $data['title']='Formularios';
        $data['content']='backend/formularios/index';
        
        $this->load->view('backend/template',$data);
    }
    
    public function crear($proceso_id){
        $formulario=new Formulario();
        $formulario->proceso_id=$proceso_id;
        $formulario->nombre='Formulario';
        $formulario->save();
        
        redirect('backend/formularios/editar/'.$formulario->id);
    }
    
    public function eliminar($formulario_id){
        $formulario=Doctrine::getTable('Formulario')->find($formulario_id);
        $proceso=$formulario->Proceso;
        $formulario->delete();
        
        redirect('backend/formularios/listar/'.$proceso->id);
    }


    public function editar($formulario_id) {
        $data['formulario']=Doctrine::getTable('Formulario')->find($formulario_id);
        $data['proceso']=$data['formulario']->Proceso;
        
        $data['title']=$data['formulario']->nombre;
        $data['content']='backend/formularios/editar';
        
        $this->load->view('backend/template',$data);
    }
    
    public function ajax_editar($formulario_id){
        $data['formulario']=Doctrine::getTable('Formulario')->find($formulario_id);
        
        $this->load->view('backend/formularios/ajax_editar',$data);
    }
    
    public function editar_form($formulario_id){
        $this->form_validation->set_rules('nombre','Nombre','required');
        
        if($this->form_validation->run()==TRUE){
            $formulario=Doctrine::getTable('Formulario')->find($formulario_id);
            $formulario->nombre=$this->input->post('nombre');
            $formulario->save();
            
            $respuesta->validacion=TRUE;
            $respuesta->redirect=site_url('backend/formularios/editar/'.$formulario->id);
            
        } else{
            $respuesta->validacion=FALSE;
            $respuesta->errores=validation_errors();
            
        }
        
        echo json_encode($respuesta);
    }
    
    public function ajax_editar_campo($campo_id){
        $data['campo']=Doctrine::getTable('Campo')->find($campo_id);
        
        $this->load->view('backend/formularios/ajax_editar_campo',$data);
    }
    
    public function editar_campo_form($campo_id){
        $this->form_validation->set_rules('nombre','Nombre','required');
        $this->form_validation->set_rules('etiqueta','Etiqueta','required');
        
        if($this->form_validation->run()==TRUE){
            $campo=Doctrine::getTable('Campo')->find($campo_id);
            $campo->nombre=$this->input->post('nombre');
            $campo->etiqueta=$this->input->post('etiqueta');
            $campo->validacion=$this->input->post('validacion');
            $campo->save();
            
            $respuesta->validacion=TRUE;
            $respuesta->redirect=site_url('backend/formularios/editar/'.$campo->Formulario->id);
            
        } else{
            $respuesta->validacion=FALSE;
            $respuesta->errores=validation_errors();
            
        }
        
        echo json_encode($respuesta);
    }
    
    public function ajax_agregar_campo($formulario_id, $tipo){
        $data['formulario_id']=$formulario_id;
        $data['tipo']=$tipo;
        $this->load->view('backend/formularios/ajax_agregar_campo',$data);
    }
    
    public function agregar_campo_form($formulario_id, $tipo){
        $this->form_validation->set_rules('nombre','Nombre','required');
        $this->form_validation->set_rules('etiqueta','Etiqueta','required');
        
        if($this->form_validation->run()==TRUE){
            $campo=new Campo();
            $campo->formulario_id=$formulario_id;
            $campo->tipo=$tipo;
            $campo->nombre=$this->input->post('nombre');
            $campo->etiqueta=$this->input->post('etiqueta');
            $campo->validacion=$this->input->post('validacion');
            $campo->save();
            
            $respuesta->validacion=TRUE;
            $respuesta->redirect=site_url('backend/formularios/editar/'.$campo->Formulario->id);
            
        } else{
            $respuesta->validacion=FALSE;
            $respuesta->errores=validation_errors();
            
        }
        
        echo json_encode($respuesta);
    }
    
    public function eliminar_campo($campo_id){
        $campo=Doctrine::getTable('Campo')->find($campo_id);
        $formulario=$campo->Formulario;
        $campo->delete();
        
        redirect('backend/formularios/editar/'.$formulario->id);
    }
    
    public function editar_posicion_campos($formulario_id){
        $formulario=Doctrine::getTable('Formulario')->find($formulario_id);
        
        $json=$this->input->post('posiciones');
        $formulario->updatePosicionesCamposFromJSON($json);
        
        
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */