<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Seguimiento extends CI_Controller {

    public function __construct() {
        parent::__construct();

        UsuarioBackendSesion::force_login();
    }

    public function index() {
        $data['tramites']=  Doctrine_Query::create()
                ->from('Tramite t, t.Proceso p')
                ->where('p.cuenta_id = ?',  UsuarioBackendSesion::usuario()->cuenta_id)
                ->orderBy('t.updated_at desc')
                ->execute();
        
        $data['title']='Seguimiento de Trámites';
        $data['content']='backend/seguimiento/index';
        $this->load->view('backend/template',$data);
    }

    public function ver($tramite_id){
        $tramite=Doctrine::getTable('Tramite')->find($tramite_id);
        
        if(UsuarioBackendSesion::usuario()->cuenta_id!=$tramite->Proceso->cuenta_id){
            echo 'No tiene permisos para hacer seguimiento a este tramite.';
            exit;
        }
        
        $data['tramite']=$tramite;
        
        $data['title']='Seguimiento - '.$tramite->Proceso->nombre;
        $data['content']='backend/seguimiento/ver';
        $this->load->view('backend/template',$data);
    }

    public function ajax_ver_etapas($tramite_id,$tarea_identificador){
        $tramite=Doctrine::getTable('Tramite')->find($tramite_id);
        
        if(UsuarioBackendSesion::usuario()->cuenta_id!=$tramite->Proceso->cuenta_id){
            echo 'No tiene permisos para hacer seguimiento a este tramite.';
            exit;
        }
        
        $etapas=Doctrine_Query::create()
                ->from('Etapa e, e.Tarea tar, e.Tramite t')
                ->where('t.id = ? AND tar.identificador = ?',array($tramite_id,$tarea_identificador))
                ->execute();

        
        $data['etapas']=$etapas;
        
        $this->load->view('backend/seguimiento/ajax_ver_etapas',$data);
    }
    
    public function ver_etapa($etapa_id,$paso=0){
        $etapa=Doctrine::getTable('Etapa')->find($etapa_id);
        
        if(UsuarioBackendSesion::usuario()->cuenta_id!=$etapa->Tramite->Proceso->cuenta_id){
            echo 'No tiene permisos para hacer seguimiento a este tramite.';
            exit;
        }
        
        $data['etapa']=$etapa;
        $data['paso']=$paso;
        
        $data['title']='Seguimiento - '.$etapa->Tarea->nombre;
        $data['content']='backend/seguimiento/ver_etapa';
        $this->load->view('backend/template',$data);
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */