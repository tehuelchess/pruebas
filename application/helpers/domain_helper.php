<?php

//Retorna en que subdominio se esta cargando el sitio
function subdomain() {
    $CI=&get_instance();
    preg_match('/(.+)\.chilesinpapeleo\.cl/', $CI->input->server('HTTP_HOST'), $matches);
    $cuenta=null;
    if(isset($matches[1]) && $matches[1]!='simple')
        $cuenta =  $matches[1];
    return $cuenta;
}