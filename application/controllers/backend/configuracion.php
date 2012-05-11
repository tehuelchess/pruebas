<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Configuracion extends CI_Controller {
    
    public function __construct() {
        parent::__construct();

        UsuarioBackendSesion::force_login();
    }

    public function index() {
        redirect('backend/procesos');
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */