<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Tramites extends CI_Controller {

    public function __construct() {
        parent::__construct();

        UsuarioSesion::force_login();
    }

    public function index() {
        redirect('tramites/inbox');
    }


    public function participados() {
        $data['tramites']=Doctrine::getTable('Tramite')->findParticipados(UsuarioSesion::usuario()->id);
        
        $data['content'] = 'tramites/participados';
        $data['title'] = 'Bienvenido';
        $this->load->view('template', $data);
    }
    
    public function inbox() {
        $data['tramites']=Doctrine::getTable('Tramite')->findPendientes(UsuarioSesion::usuario()->id);
        
        $data['content'] = 'tramites/inbox';
        $data['title'] = 'Bandeja de Entrada';
        $this->load->view('template', $data);
    }
    
    public function sinasignar() {
        $data['tramites']=Doctrine::getTable('Tramite')->findSinAsignar(UsuarioSesion::usuario()->id);
        
        $data['content'] = 'tramites/sinasignar';
        $data['title'] = 'Sin Asignar';
        $this->load->view('template', $data);
    }

    public function disponibles() {
        $data['procesos']=Doctrine::getTable('Proceso')->findProcesosDisponiblesParaIniciar(UsuarioSesion::usuario()->id);
        
        $data['content'] = 'tramites/disponibles';
        $data['title'] = 'Trámites disponibles a iniciar';
        $this->load->view('template', $data);
    }

    public function iniciar($proceso_id) {
        
        $tramite=new Tramite();
        $tramite->iniciar($proceso_id);
        
        
        
        
        redirect('etapas/ejecutar/'.$tramite->getEtapaActual()->id);
    }

}
