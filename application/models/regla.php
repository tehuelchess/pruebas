<?php

class Regla {

    private $regla;

    function __construct($regla) {
        $this->regla = $regla;
    }
    
    

    //Evalua la regla de acuerdo a los datos capturados en el tramite tramite_id
    public function evaluar($etapa_id) {
        if (!$this->regla)
            return TRUE;

        $new_regla = $this->getExpresionParaEvaluar($etapa_id);   
        $new_regla = 'return ' . $new_regla . ';';
        $CI = & get_instance();
        $CI->load->library('SaferEval');
        $resultado = FALSE;
        if (!$errores = $CI->safereval->checkScript($new_regla, FALSE))
            $resultado = @eval($new_regla);
        
        return $resultado;
    }
    
    //Obtiene la expresion con los reemplazos de variables ya hechos de acuerdo a los datos capturados en el tramite tramite_id.
    //Esta expresion es la que se evalua finalmente en la regla
    public function getExpresionParaEvaluar($etapa_id){
        $new_regla=$this->regla;
        $new_regla=preg_replace_callback('/@@(\w+)((->(\w+))|(\[(\w+)\]))?/', function($match) use ($etapa_id) {
                    $nombre_dato = $match[1];
                    $obj_accesor=isset($match[4])?$match[4]:null;
                    $arr_accesor=isset($match[6])?$match[6]:null;
                    
                    $dato = Doctrine::getTable('DatoSeguimiento')->findByNombreHastaEtapa($nombre_dato,$etapa_id);                    
                    if ($dato) {
                        if($obj_accesor!=null)
                            $valor_dato = var_export($dato->valor->{$obj_accesor},true);
                        else if($arr_accesor!=null)
                            $valor_dato = var_export($dato->valor[$arr_accesor],true);
                        else
                            $valor_dato = var_export($dato->valor,true);
                    }
                    else {
                        //No reemplazamos el dato
                        $valor_dato = var_export(null,true);
                    }

                    return $valor_dato;
                }, $new_regla);
                
         $new_regla=preg_replace_callback('/@!(\w+)/', function($match) use ($etapa_id) {
                    $nombre_dato = $match[1];
                    $usuario=UsuarioSesion::usuario();
                    if($nombre_dato=='rut')
                        return "'".$usuario->rut."'";
                    else if($nombre_dato=='nombre')         //Deprecated
                        return "'".$usuario->nombres."'";
                    else if($nombre_dato=='apellidos')      //Deprecated
                        return "'".$usuario->apellido_paterno.' '.$usuario->apellido_materno."'";
                    else if($nombre_dato=='nombres')
                        return "'".$usuario->nombres."'";
                    else if($nombre_dato=='apellido_paterno')
                        return "'".$usuario->apellido_paterno."'";
                    else if($nombre_dato=='apellido_materno')
                        return "'".$usuario->apellido_materno."'";
                    else if($nombre_dato=='email')
                        return "'".$usuario->email."'";
                    else if($nombre_dato=='tramite_id'){
                        return "'".Doctrine::getTable('Etapa')->find($etapa_id)->tramite_id."'";
                    }
                }, $new_regla);
                
         //Si quedaron variables sin reemplazar, la evaluacion deberia ser siempre falsa.
         if(preg_match('/@@\w+/', $new_regla))
            return false;
                   
         return $new_regla;
    }
    
    //Obtiene la expresion con los reemplazos de variables ya hechos de acuerdo a los datos capturados en el tramite tramite_id.
    //Esta es una representacion con las variables reemplazadas. No es una expresion evaluable. (Los arrays y strings no estan definidos como tal)
    public function getExpresionParaOutput($etapa_id){
        $new_regla=$this->regla;     
        $new_regla=preg_replace_callback('/@@(\w+)((->(\w+))|(\[(\w+)\]))?/', function($match) use ($etapa_id) {
                    $nombre_dato = $match[1];
                    $obj_accesor=isset($match[4])?$match[4]:null;
                    $arr_accesor=isset($match[6])?$match[6]:null;
                    //echo $arr_accesor;
                    $dato = Doctrine::getTable('DatoSeguimiento')->findByNombreHastaEtapa($nombre_dato,$etapa_id);
                    if ($dato) {
                        $dato_almacenado = $dato->valor;
                        if($obj_accesor!=null)
                            $valor_dato=  json_encode ($dato_almacenado->{$obj_accesor});
                        else if($arr_accesor!=null)
                            $valor_dato=  json_encode ($dato_almacenado[$arr_accesor]);
                        else
                            $valor_dato=  json_encode($dato_almacenado);
                        
                        //Si es un string lo representamos directamente.
                        if(is_string(json_decode($valor_dato)))
                            $valor_dato=json_decode($valor_dato);
                    }
                    else {
                        //Entregamos vacio
                        $valor_dato = '';
                    }

                    return $valor_dato;
                }, $new_regla);
         
         $new_regla=preg_replace_callback('/@!(\w+)/', function($match) use ($etapa_id) {
                    $nombre_dato = $match[1];
                    $usuario=UsuarioSesion::usuario();
                    if($nombre_dato=='rut')
                        return $usuario->rut;
                    else if($nombre_dato=='nombre')         //Deprecated
                        return $usuario->nombres;
                    else if($nombre_dato=='apellidos')      //Deprecated
                        return $usuario->apellido_paterno.' '.$usuario->apellido_materno;
                    else if($nombre_dato=='nombres')
                        return $usuario->nombres;
                    else if($nombre_dato=='apellido_paterno')
                        return $usuario->apellido_paterno;
                    else if($nombre_dato=='apellido_materno')
                        return $usuario->apellido_materno;
                    else if($nombre_dato=='email')
                        return $usuario->email;
                    else if($nombre_dato=='tramite_id'){
                        return Doctrine::getTable('Etapa')->find($etapa_id)->tramite_id;
                    }
                }, $new_regla);
          
         return $new_regla;
    }

}