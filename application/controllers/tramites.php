<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Tramites extends MY_Controller {

    public function __construct() {
        parent::__construct();

    }

    public function index() {
        redirect('etapas/inbox');
    }


    public function participados() {
        $data['tramites']=Doctrine::getTable('Tramite')->findParticipados(UsuarioSesion::usuario()->id, Cuenta::cuentaSegunDominio());
        
        $data['content'] = 'tramites/participados';
        $data['title'] = 'Bienvenido';
        $this->load->view('template', $data);
    }

    public function disponibles() {
        $data['procesos']=Doctrine::getTable('Proceso')->findProcesosDisponiblesParaIniciar(UsuarioSesion::usuario()->id, Cuenta::cuentaSegunDominio());
        
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
        
        //Vemos si es que usuario ya tiene un tramite de proceso_id ya iniciado, y que se encuentre en su primera etapa.
        //Si es asi, hacemos que lo continue. Si no, creamos uno nuevo
        $tramite=Doctrine_Query::create()
                ->from('Tramite t, t.Proceso p, t.Etapas e, e.Tramite.Etapas hermanas')
                ->where('t.pendiente=1 AND p.id = ? AND e.usuario_id = ?',array($proceso_id, UsuarioSesion::usuario()->id))
                ->groupBy('t.id')
                ->having('COUNT(hermanas.id) = 1')
                ->fetchOne();
        
        if(!$tramite){
            $tramite=new Tramite();
            $tramite->iniciar($proceso->id);
        }  
        
    
        $qs=$this->input->server('QUERY_STRING');
        redirect('etapas/ejecutar/'.$tramite->getEtapasActuales()->get(0)->id.($qs?'?'.$qs:''));
    }
    
    public function eliminar($tramite_id){
        $tramite=Doctrine::getTable('Tramite')->find($tramite_id);
                
        if($tramite->Etapas->count()>1){
            echo 'Tramite no se puede eliminar, ya ha avanzado mas de una etapa';
            exit;
        }
        
        if(UsuarioSesion::usuario()->id!=$tramite->Etapas[0]->usuario_id){
            echo 'Usuario no tiene permisos para eliminar este tramite';
            exit;
        }
        
        $tramite->delete();
        redirect($this->input->server('HTTP_REFERER'));
    }

}
