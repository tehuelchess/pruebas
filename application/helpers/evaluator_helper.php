<?php

//Obtiene el elemento dentro de un array u object.
function get($element,$index){
    if(is_array($element))
        return $element[$index];
    else if(is_object($element))
        return $element->{$index};
    
    
    return null;
}