<?php

class MY_Controller extends CI_Controller {

    function __construct(){
        parent::__construct();
        
        if($this->config->item('https'))
            $this->force_ssl();
    }
    
    private function force_ssl(){
        if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on") {
            $url = "https://". $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
            redirect($url);
        }
    }
}