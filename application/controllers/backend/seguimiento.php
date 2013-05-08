<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Seguimiento extends CI_Controller {

    public function __construct() {
        parent::__construct();

        UsuarioBackendSesion::force_login();
        
        if(UsuarioBackendSesion::usuario()->rol!='super' && UsuarioBackendSesion::usuario()->rol!='operacion'){
            echo 'No tiene permisos para acceder a esta seccion.';
            exit;
        }
    }

    public function index() {
        $data['procesos'] = Doctrine::getTable('Proceso')->findByCuentaId(UsuarioBackendSesion::usuario()->cuenta_id);

        $data['title'] = 'Listado de Procesos';
        $data['content'] = 'backend/seguimiento/index';
        $this->load->view('backend/template', $data);
    }

    public function index_proceso($proceso_id) {
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if (UsuarioBackendSesion::usuario()->cuenta_id != $proceso->cuenta_id) {
            echo 'Usuario no tiene permisos';
            exit;
        }




        $doctrine_query = Doctrine_Query::create()
                ->from('Tramite t, t.Proceso p')
                ->where('p.id = ?', $proceso_id)
                ->orderBy('t.updated_at desc');

        $query = $this->input->get('query');
        if ($query) {
            $this->load->library('sphinxclient');
            $this->sphinxclient->setFilter('proceso_id', array($proceso_id));
            $result = $this->sphinxclient->query($query, 'tramites');
            if($result['total']>0){
                $matches = array_keys($result['matches']);
                $doctrine_query->whereIn('t.id',$matches);
            }else{
                $doctrine_query->where('0');
            }
        }

        $data['query'] = $query;
        $data['proceso'] = $proceso;
        $data['tramites'] = $doctrine_query->execute();

        $data['title'] = 'Seguimiento de ' . $proceso->nombre;
        $data['content'] = 'backend/seguimiento/index_proceso';
        $this->load->view('backend/template', $data);
    }

    public function ver($tramite_id) {
        $tramite = Doctrine::getTable('Tramite')->find($tramite_id);

        if (UsuarioBackendSesion::usuario()->cuenta_id != $tramite->Proceso->cuenta_id) {
            echo 'No tiene permisos para hacer seguimiento a este tramite.';
            exit;
        }

        $data['tramite'] = $tramite;
        $data['etapas'] = Doctrine_Query::create()->from('Etapa e, e.Tramite t')->where('t.id = ?', $tramite->id)->orderBy('created_at desc')->execute();

        $data['title'] = 'Seguimiento - ' . $tramite->Proceso->nombre;
        $data['content'] = 'backend/seguimiento/ver';
        $this->load->view('backend/template', $data);
    }

    public function ajax_ver_etapas($tramite_id, $tarea_identificador) {
        $tramite = Doctrine::getTable('Tramite')->find($tramite_id);

        if (UsuarioBackendSesion::usuario()->cuenta_id != $tramite->Proceso->cuenta_id) {
            echo 'No tiene permisos para hacer seguimiento a este tramite.';
            exit;
        }

        $etapas = Doctrine_Query::create()
                ->from('Etapa e, e.Tarea tar, e.Tramite t')
                ->where('t.id = ? AND tar.identificador = ?', array($tramite_id, $tarea_identificador))
                ->execute();


        $data['etapas'] = $etapas;

        $this->load->view('backend/seguimiento/ajax_ver_etapas', $data);
    }

    public function ver_etapa($etapa_id, $paso = 0) {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);

        if (UsuarioBackendSesion::usuario()->cuenta_id != $etapa->Tramite->Proceso->cuenta_id) {
            echo 'No tiene permisos para hacer seguimiento a este tramite.';
            exit;
        }

        $data['etapa'] = $etapa;
        $data['paso'] = $paso;

        $data['title'] = 'Seguimiento - ' . $etapa->Tarea->nombre;
        $data['content'] = 'backend/seguimiento/ver_etapa';
        $this->load->view('backend/template', $data);
    }

    public function reasignar_form($etapa_id) {
        $this->form_validation->set_rules('usuario_id', 'Usuario', 'required');

        if ($this->form_validation->run() == TRUE) {
            $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
            $etapa->usuario_id = $this->input->post('usuario_id');
            $etapa->save();

            $respuesta->validacion = TRUE;
            $respuesta->redirect = site_url('backend/seguimiento/ver_etapa/' . $etapa->id);
        } else {
            $respuesta->validacion = FALSE;
            $respuesta->errores = validation_errors();
        }

        echo json_encode($respuesta);
    }

    public function borrar_tramite($tramite_id) {
        $tramite = Doctrine::getTable('Tramite')->find($tramite_id);

        if (UsuarioBackendSesion::usuario()->cuenta_id != $tramite->Proceso->cuenta_id) {
            echo 'No tiene permisos para hacer seguimiento a este tramite.';
            exit;
        }

        $tramite->delete();

        redirect($this->input->server('HTTP_REFERER'));
    }

    public function borrar_proceso($proceso_id) {
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if (UsuarioBackendSesion::usuario()->cuenta_id != $proceso->cuenta_id) {
            echo 'No tiene permisos para hacer seguimiento a este tramite.';
            exit;
        }

        $proceso->Tramites->delete();

        redirect($this->input->server('HTTP_REFERER'));
    }
    
    public function ajax_editar_vencimiento($etapa_id){
        $etapa=Doctrine::getTable('Etapa')->find($etapa_id);
        $data['etapa']=$etapa;
        
        $this->load->view('backend/seguimiento/ajax_editar_vencimiento',$data);
    }
    
    public function editar_vencimiento_form($etapa_id){
        $this->form_validation->set_rules('vencimiento_at','Fecha de vencimiento','required');
        $respuesta=new stdClass();
        if($this->form_validation->run()==TRUE){
            $etapa=Doctrine::getTable('Etapa')->find($etapa_id);
            $etapa->vencimiento_at=date('Y-m-d',strtotime($this->input->post('vencimiento_at')));
            $etapa->save();
            
            $respuesta->validacion=TRUE;
            $respuesta->redirect=  site_url('backend/seguimiento/index_proceso/'.$etapa->Tarea->proceso_id);
        }else{
            $respuesta->validacion=FALSE;
            $respuesta->errores=  validation_errors();
        }
        
        echo json_encode($respuesta);
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */