<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Portada extends CI_Controller {

    public function __construct() {
        parent::__construct();

        UsuarioSesion::force_login();
    }

    public function index() {
        $pendientes=Doctrine::getTable('Etapa')->findPendientes(UsuarioSesion::usuario()->id);
        
        if($pendientes->count()>0)
            redirect('etapas/inbox');
        else
            redirect('tramites/disponibles');
    }

}