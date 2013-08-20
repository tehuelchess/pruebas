<?php

class MY_Input extends CI_Input {

    function __construct()
    {
        parent::__construct();
    }

    function post($index = '', $xss_clean = TRUE)
    {
        return parent::post($index, $xss_clean);
    }
}