<?php

class MY_Controller extends CI_Controller {

    function __construct(){
        parent::__construct();
        
        if($this->config->item('https'))
            $this->force_ssl();
        
        UsuarioSesion::force_login();
        
        $this->force_email();
        
    }
    
    private function force_ssl(){
        if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on") {
            $url = "https://". $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
            redirect($url);
        }
    }
    
    //Obliga a los usuarios a registrar su email, para hacer tramites en simple
    private function force_email(){
        if(uri_string()!='cuentas/editar' && uri_string()!='cuentas/editar_form' && uri_string()!='autenticacion/logout' && UsuarioSesion::usuario() && UsuarioSesion::usuario()->registrado && !UsuarioSesion::usuario()->email)
            redirect('cuentas/editar');
    }
}