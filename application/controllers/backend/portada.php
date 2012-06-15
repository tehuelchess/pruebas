<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Portada extends CI_Controller {
    
    public function __construct() {
        parent::__construct();

        UsuarioBackendSesion::force_login();
    }

    public function index() {
        $data['title']='Portada';
        $data['content']='backend/portada/index';
        $this->load->view('backend/template',$data);
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */