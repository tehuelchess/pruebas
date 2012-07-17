<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Tramites extends CI_Controller {

    public function __construct() {
        parent::__construct();

        UsuarioSesion::force_login();
    }

    public function index() {
        redirect('etapas/inbox');
    }


    public function participados() {
        $data['tramites']=Doctrine::getTable('Tramite')->findParticipados(UsuarioSesion::usuario()->id);
        
        $data['content'] = 'tramites/participados';
        $data['title'] = 'Bienvenido';
        $this->load->view('template', $data);
    }

    public function disponibles() {
        $data['procesos']=Doctrine::getTable('Proceso')->findProcesosDisponiblesParaIniciar(UsuarioSesion::usuario()->id);
        
        $data['content'] = 'tramites/disponibles';
        $data['title'] = 'Trámites disponibles a iniciar';
        $this->load->view('template', $data);
    }

    public function iniciar($proceso_id) {
        $proceso=Doctrine::getTable('Proceso')->find($proceso_id);
        
        if(!$proceso->canUsuarioIniciarlo(UsuarioSesion::usuario()->id)){
            echo 'Usuario no puede iniciar este proceso';
            exit;
        }
        
        $tramite=new Tramite();
        $tramite->iniciar($proceso->id);
        
        
        
        $qs=$this->input->server('QUERY_STRING');
        redirect('etapas/ejecutar/'.$tramite->getEtapasActuales()->get(0)->id.($qs?'?'.$qs:''));
    }

}
