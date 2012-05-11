<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Procesos extends CI_Controller {
    
    public function __construct() {
        parent::__construct();

        UsuarioBackendSesion::force_login();
    }

    public function index() {
        $data['procesos'] = Doctrine::getTable('Proceso')->findByCuentaId(UsuarioBackendSesion::usuario()->cuenta_id);

        $data['title'] = 'Listado de Procesos';
        $data['content'] = 'backend/procesos/index';
        $this->load->view('backend/template', $data);
    }
    
    public function crear(){
        $proceso=new Proceso();
        $proceso->nombre='Proceso';
        $proceso->cuenta_id=UsuarioBackendSesion::usuario()->cuenta_id;
        
        $proceso->save();
        
        redirect('backend/procesos/editar/'.$proceso->id);
    }
    
    public function eliminar($proceso_id){
        $proceso=Doctrine::getTable('Proceso')->find($proceso_id);
        $proceso->delete();
        
        redirect('backend/procesos/index/');
    }

    public function editar($proceso_id) {
        $data['proceso'] = Doctrine::getTable('Proceso')->find($proceso_id);

        $data['title'] = 'Modelador';
        $data['content'] = 'backend/procesos/editar';
        $this->load->view('backend/template', $data);
    }
    
    public function ajax_editar($proceso_id){
        $data['proceso']=Doctrine::getTable('Proceso')->find($proceso_id);
        
        $this->load->view('backend/procesos/ajax_editar',$data);
    }
    
    public function editar_form($proceso_id){
        $this->form_validation->set_rules('nombre', 'Nombre', 'required');

        if ($this->form_validation->run() == TRUE) {
            $proceso=Doctrine::getTable('Proceso')->find($proceso_id);
            $proceso->nombre=$this->input->post('nombre');
            $proceso->save();
            
            //$socket_id_emisor=$this->input->post('socket_id_emisor');
            $this->load->library('pusher');
            $this->pusher->trigger('modelador', 'updateModel', array('modelo' => $proceso->getJSONFromModel()));
            
            $respuesta->validacion=TRUE;
            $respuesta->redirect=site_url('backend/procesos/editar/'.$proceso->id);
            
        }else{
            $respuesta->validacion=FALSE;
            $respuesta->errores=validation_errors();
        }
        
        echo json_encode($respuesta);
    }
    
    public function ajax_editar_tarea($proceso_id,$tarea_identificador){
        $data['tarea'] = Doctrine::getTable('Tarea')->findOneByProcesoIdAndIdentificador($proceso_id,$tarea_identificador);
        $data['grupos_usuarios']=Doctrine::getTable('GrupoUsuarios')->findAll();
        $data['formularios']=Doctrine::getTable('Formulario')->findByProcesoId($proceso_id);
        
        $this->load->view('backend/procesos/ajax_editar_tarea',$data);
    }
    
    public function editar_tarea_form($tarea_id){
        $this->form_validation->set_rules('nombre', 'Nombre', 'required');

        if ($this->form_validation->run() == TRUE) {
            $tarea=Doctrine::getTable('Tarea')->find($tarea_id);
            $tarea->nombre=$this->input->post('nombre');
            $tarea->inicial=$this->input->post('inicial');
            $tarea->final=$this->input->post('final');
            $tarea->setGruposUsuariosFromArray($this->input->post('grupos_usuarios'));
            $tarea->setPasosFromArray($this->input->post('pasos'));
            $tarea->save();
            
            //$socket_id_emisor=$this->input->post('socket_id_emisor');
            $this->load->library('pusher');
            $this->pusher->trigger('modelador', 'updateModel', array('modelo' => $tarea->Proceso->getJSONFromModel()));
            
            $respuesta->validacion=TRUE;
            $respuesta->redirect=site_url('backend/procesos/editar/'.$tarea->Proceso->id);
            
        }else{
            $respuesta->validacion=FALSE;
            $respuesta->errores=validation_errors();
        }
        
        echo json_encode($respuesta);
    }
    
    public function eliminar_tarea($tarea_id){
        $tarea=Doctrine::getTable('Tarea')->find($tarea_id);
        $proceso=$tarea->Proceso;
        $tarea->delete();
        
        //$socket_id_emisor=$this->input->get('socket_id_emisor');
        $this->load->library('pusher');
        $this->pusher->trigger('modelador', 'updateModel', array('modelo' => $proceso->getJSONFromModel()));
    
        redirect('backend/procesos/editar/'.$proceso->id);
    }

    public function ajax_editar_modelo($proceso_id) {
        $modelo=$this->input->post('modelo');
        $socket_id_emisor=$this->input->post('socket_id_emisor');
        
        
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);
        //$proceso->modelo = $modelo;
        $proceso->updateModelFromJSON($modelo);
             
        $this->load->library('pusher');
        $this->pusher->trigger('modelador', 'updateModel', array('modelo' => $proceso->getJSONFromModel()),$socket_id_emisor);
        
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */