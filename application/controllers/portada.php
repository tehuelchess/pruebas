<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Portada extends MY_Controller {

    public function __construct() {
        parent::__construct();

    }

    public function index() {
        $pendientes=Doctrine::getTable('Etapa')->findPendientes(UsuarioSesion::usuario()->id);
        
        if(UsuarioSesion::usuario()->registrado && $pendientes->count()>0)
            redirect('etapas/inbox');
        else
            redirect('tramites/disponibles');
    }
    
    public function test(){
        //echo add_working_days('2013-05-08', 5,array('Saturday','Sunday'),array('2013-05-10'));
        
        $etapas=Doctrine::getTable('Etapa')->findAll();
        foreach($etapas as $e){
            $e->vencimiento_at=$e->calcularVencimiento();
            $e->save();
        }
    }

}