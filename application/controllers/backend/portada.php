<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Portada extends CI_Controller {
    
    public function __construct() {
        parent::__construct();

        UsuarioBackendSesion::force_login();
    }

    public function index() {
        $data['widgets']=UsuarioBackendSesion::usuario()->Cuenta->Widgets;
        
        $data['title']='Portada';
        $data['content']='backend/portada/index';
        $this->load->view('backend/template',$data);
    }
    
    public function widget_create($tipo){
        $widget=new Widget();
        $widget->nombre='Nuevo widget';
        $widget->tipo=$tipo;
        $widget->cuenta_id=UsuarioBackendSesion::usuario()->cuenta_id;
        $widget->save();
        
        redirect($this->input->server('HTTP_REFERER'));
    }
    
    public function widget_change_positions(){
        $cuenta=UsuarioBackendSesion::usuario()->Cuenta;

        $json=$this->input->post('posiciones');
        $cuenta->updatePosicionesWidgetsFromJSON($json);
        
        
    }
    
    public function widget_load($widget_id){
        $widget=Doctrine::getTable('Widget')->find($widget_id);;
        
        if(UsuarioBackendSesion::usuario()->cuenta_id!=$widget->cuenta_id){
            echo 'Usuario no tiene permisos para ver este widget';
            exit;
        }
        
        $data['widget']=$widget;
        $this->load->view('backend/portada/widget_load',$data);
    }
    
    public function widget_config_form($widget_id){
        $widget=Doctrine::getTable('Widget')->find($widget_id);;
        
        if(UsuarioBackendSesion::usuario()->cuenta_id!=$widget->cuenta_id){
            echo 'Usuario no tiene permisos para ver este widget';
            exit;
        }
        
        $this->form_validation->set_rules('nombre','Nombre','required');
        $widget->validateForm();
        
        if($this->form_validation->run()==TRUE){
            $widget->nombre=$this->input->post('nombre');
            $widget->config=$this->input->post('config');
            $widget->save();
            
            $respuesta->validacion=TRUE;
        }else{
            $respuesta->validacion=FALSE;
            $respuesta->errores=validation_errors();
        }
        
        echo json_encode($respuesta);
    }
    
    public function widget_remove($widget_id){
        $widget=  Doctrine::getTable('Widget')->find($widget_id);
        
        if(UsuarioBackendSesion::usuario()->cuenta_id!=$widget->cuenta_id){
            echo 'Usuario no tiene permisos';
            exit;
        }
        
        $widget->delete();
        redirect($this->input->server('HTTP_REFERER'));
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */