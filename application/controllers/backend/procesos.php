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
        
        if($proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para eliminar este proceso';
            exit;
        }
        
        $proceso->delete();
        
        redirect('backend/procesos/index/');
    }

    public function editar($proceso_id) {
        $proceso=Doctrine::getTable('Proceso')->find($proceso_id);
        
        if($proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para editar este proceso';
            exit;
        }
        
        $data['proceso'] = $proceso;

        $data['title'] = 'Modelador';
        $data['content'] = 'backend/procesos/editar';
        $this->load->view('backend/template', $data);
    }
    
    public function ajax_editar($proceso_id){
        $proceso=Doctrine::getTable('Proceso')->find($proceso_id);
        
        if($proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para editar este proceso';
            exit;
        }
        
        $data['proceso']=$proceso;
        
        $this->load->view('backend/procesos/ajax_editar',$data);
    }
    
    public function editar_form($proceso_id){
        $proceso=Doctrine::getTable('Proceso')->find($proceso_id);
        
        if($proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para editar este proceso';
            exit;
        }
        
        $this->form_validation->set_rules('nombre', 'Nombre', 'required');

        if ($this->form_validation->run() == TRUE) {
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
    
    public function ajax_crear_tarea($proceso_id,$tarea_identificador){
        $proceso=Doctrine::getTable('Proceso')->find($proceso_id);
        
        if($proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para crear esta tarea.';
            exit;
        }
        
        $tarea=new Tarea();
        $tarea->proceso_id=$proceso->id;
        $tarea->identificador=$tarea_identificador;
        $tarea->nombre=$this->input->post('nombre');
        $tarea->posx=$this->input->post('posx');
        $tarea->posy=$this->input->post('posy');
        $tarea->save();
        
        $this->load->library('pusher');
        $this->pusher->trigger('modelador', 'updateModel', array('modelo' => $tarea->Proceso->getJSONFromModel()));
    }
    
    public function ajax_editar_tarea($proceso_id,$tarea_identificador){
        $tarea=Doctrine::getTable('Tarea')->findOneByProcesoIdAndIdentificador($proceso_id,$tarea_identificador);
        
        if($tarea->Proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para editar esta tarea.';
            exit;
        }
        
        $data['tarea'] = $tarea;
        $data['grupos_usuarios']=Doctrine::getTable('GrupoUsuarios')->findAll();
        $data['formularios']=Doctrine::getTable('Formulario')->findByProcesoId($proceso_id);
        
        $this->load->view('backend/procesos/ajax_editar_tarea',$data);
    }
    
    public function editar_tarea_form($tarea_id){
        $tarea=Doctrine::getTable('Tarea')->find($tarea_id);
        
        if($tarea->Proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para editar esta tarea.';
            exit;
        }
        
        $this->form_validation->set_rules('nombre', 'Nombre', 'required');

        if ($this->form_validation->run() == TRUE) {
            $tarea->nombre=$this->input->post('nombre');
            $tarea->inicial=$this->input->post('inicial');
            $tarea->final=$this->input->post('final');
            $tarea->asignacion=$this->input->post('asignacion');
            $tarea->asignacion_usuario=$this->input->post('asignacion_usuario');
            $tarea->asignacion_notificar=$this->input->post('asignacion_notificar');
            $tarea->setGruposUsuariosFromArray($this->input->post('grupos_usuarios'));
            $tarea->setPasosFromArray($this->input->post('pasos'));
            $tarea->almacenar_usuario=$this->input->post('almacenar_usuario');
            $tarea->almacenar_usuario_variable=$this->input->post('almacenar_usuario_variable');
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
        
        if($tarea->Proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para eliminar esta tarea.';
            exit;
        }
        
        $proceso=$tarea->Proceso;
        $tarea->delete();
        
        //$socket_id_emisor=$this->input->get('socket_id_emisor');
        $this->load->library('pusher');
        $this->pusher->trigger('modelador', 'updateModel', array('modelo' => $proceso->getJSONFromModel()));
    
        redirect('backend/procesos/editar/'.$proceso->id);
    }
    
    public function ajax_crear_conexion($proceso_id,$conexion_identificador){        
        $proceso=Doctrine::getTable('Proceso')->find($proceso_id);
        $tarea_origen=Doctrine::getTable('Tarea')->findOneByProcesoIdAndIdentificador($proceso_id,$this->input->post('tarea_id_origen'));
        $tarea_destino=Doctrine::getTable('Tarea')->findOneByProcesoIdAndIdentificador($proceso_id,$this->input->post('tarea_id_destino'));
        
        if($proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para crear esta conexion.';
            exit;
        }
        if($tarea_origen->Proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para crear esta conexion.';
            exit;
        }
        if($tarea_destino->Proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para crear esta conexion.';
            exit;
        }
        
        $conexion=new Conexion();
        $conexion->identificador=$conexion_identificador;
        $conexion->tarea_id_origen=$tarea_origen->id;
        $conexion->tarea_id_destino=$tarea_destino->id;
        $conexion->tipo=$this->input->post('tipo');
        $conexion->save();
    }
    
    public function ajax_editar_conexion($proceso_id,$conexion_identificador){        
        $conexion=  Doctrine_Query::create()
                ->from('Conexion c, c.TareaOrigen t')
                ->where('t.proceso_id=? AND c.identificador=?',array($proceso_id,$conexion_identificador))
                ->fetchOne();
        
        if($conexion->TareaOrigen->Proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para editar esta tarea.';
            exit;
        }
        
        $data['conexion'] = $conexion;
        
        $this->load->view('backend/procesos/ajax_editar_conexion',$data);
    }
    
    public function editar_conexion_form($conexion_id){
        $conexion=Doctrine::getTable('Conexion')->find($conexion_id);
        
        if($conexion->TareaOrigen->Proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para editar esta tarea.';
            exit;
        }
        
        $this->form_validation->set_rules('regla', 'Regla');

        if ($this->form_validation->run() == TRUE) {
            $conexion->regla=$this->input->post('regla');
            $conexion->save();
            
            //$socket_id_emisor=$this->input->post('socket_id_emisor');
            $this->load->library('pusher');
            $this->pusher->trigger('modelador', 'updateModel', array('modelo' => $conexion->TareaOrigen->Proceso->getJSONFromModel()));
            
            $respuesta->validacion=TRUE;
            $respuesta->redirect=site_url('backend/procesos/editar/'.$conexion->TareaOrigen->Proceso->id);
            
        }else{
            $respuesta->validacion=FALSE;
            $respuesta->errores=validation_errors();
        }
        
        echo json_encode($respuesta);
    }
    
    public function eliminar_conexion($conexion_id){
        $conexion=Doctrine::getTable('Conexion')->find($conexion_id);
        
        if($conexion->TareaOrigen->Proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para eliminar esta conexion.';
            exit;
        }
        
        $proceso=$conexion->TareaOrigen->Proceso;
        $conexion->delete();
        
        //$socket_id_emisor=$this->input->get('socket_id_emisor');
        $this->load->library('pusher');
        $this->pusher->trigger('modelador', 'updateModel', array('modelo' => $proceso->getJSONFromModel()));
    
        redirect('backend/procesos/editar/'.$proceso->id);
    }

    public function ajax_editar_modelo($proceso_id) {
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if($proceso->cuenta_id!=UsuarioBackendSesion::usuario()->cuenta_id){
            echo 'Usuario no tiene permisos para editar este proceso';
            exit;
        }
        
        $modelo=$this->input->post('modelo');
        $socket_id_emisor=$this->input->post('socket_id_emisor');
                
        $proceso->updateModelFromJSON($modelo);
             
        $this->load->library('pusher');
        $this->pusher->trigger('modelador', 'updateModel', array('modelo' => $proceso->getJSONFromModel()),$socket_id_emisor);
        
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */