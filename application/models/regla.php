<?php

class Regla {

    private $regla;

    function __construct($regla) {
        $this->regla = $regla;
    }
    
    

    //Evalua la regla de acuerdo a los datos capturados en el tramite tramite_id
    public function evaluar($tramite_id) {
        if (!$this->regla)
            return TRUE;

        $new_regla = $this->getExpresionParaEvaluar($tramite_id);   
        $new_regla = 'return ' . $new_regla . ';';
        $CI = & get_instance();
        $CI->load->library('SaferEval');
        $resultado = FALSE;
        if (!$errores = $CI->safereval->checkScript($new_regla, FALSE))
            $resultado = eval($new_regla);
        
        return $resultado;
    }
    
    //Obtiene la expresion con los reemplazos de variables ya hechos de acuerdo a los datos capturados en el tramite tramite_id.
    //Esta expresion es la que se evalua finalmente en la regla
    public function getExpresionParaEvaluar($tramite_id){
        $new_regla=$this->regla;
        $new_regla=preg_replace_callback('/@@(\w+)((->(\w+))|(\[(\w+)\]))?/', function($match) use ($tramite_id) {
                    $nombre_dato = $match[1];
                    $obj_accesor=isset($match[4])?$match[4]:null;
                    $arr_accesor=isset($match[6])?$match[6]:null;
                    
                    $dato = Doctrine::getTable('Dato')->findOneByTramiteIdAndNombre($tramite_id, $nombre_dato);                    
                    if ($dato) {
                        if($obj_accesor!=null)
                            $valor_dato = 'json_decode(\'' .json_encode($dato->valor->{$obj_accesor}). '\')';
                        else if($arr_accesor!=null)
                            $valor_dato = 'json_decode(\'' .json_encode($dato->valor[$arr_accesor]). '\')';
                        else
                            $valor_dato = 'json_decode(\'' .json_encode($dato->valor). '\')';
                    }
                    else {
                        //No reemplazamos el dato
                        $valor_dato = 'json_decode(\'' .json_encode(null). '\')';
                    }

                    return $valor_dato;
                }, $new_regla);
                
         $new_regla=preg_replace_callback('/@!(\w+)/', function($match) use ($tramite_id) {
                    $nombre_dato = $match[1];
                    $usuario=UsuarioSesion::usuario();
                    if($nombre_dato=='rut')
                        return "'".$usuario->rut."'";
                    else if($nombre_dato=='nombre')
                        return "'".$usuario->nombre."'";
                    else if($nombre_dato=='apellidos')
                        return "'".$usuario->apellidos."'";
                    else if($nombre_dato=='email')
                        return "'".$usuario->email."'";
                    else if($nombre_dato=='tramite_id'){
                        return "'".$tramite_id."'";
                    }
                }, $new_regla);
                
         //Si quedaron variables sin reemplazar, la evaluacion deberia ser siempre falsa.
         if(preg_match('/@@\w+/', $new_regla))
            return false;
                   
         return $new_regla;
    }
    
    //Obtiene la expresion con los reemplazos de variables ya hechos de acuerdo a los datos capturados en el tramite tramite_id.
    //Esta es una representacion con las variables reemplazadas. No es una expresion evaluable. (Los arrays y strings no estan definidos como tal)
    public function getExpresionParaOutput($tramite_id){
        $new_regla=$this->regla;     
        $new_regla=preg_replace_callback('/@@(\w+)((->(\w+))|(\[(\w+)\]))?/', function($match) use ($tramite_id) {
                    $nombre_dato = $match[1];
                    $obj_accesor=isset($match[4])?$match[4]:null;
                    $arr_accesor=isset($match[6])?$match[6]:null;
                    //echo $arr_accesor;
                    $dato = Doctrine::getTable('Dato')->findOneByTramiteIdAndNombre($tramite_id, $nombre_dato);
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
         
         $new_regla=preg_replace_callback('/@!(\w+)/', function($match) use ($tramite_id) {
                    $nombre_dato = $match[1];
                    $usuario=UsuarioSesion::usuario();
                    if($nombre_dato=='rut')
                        return $usuario->rut;
                    else if($nombre_dato=='nombre')
                        return $usuario->nombre;
                    else if($nombre_dato=='apellidos')
                        return $usuario->apellidos;
                    else if($nombre_dato=='email')
                        return $usuario->email;
                    else if($nombre_dato=='tramite_id'){
                        return $tramite_id;
                    }
                }, $new_regla);
          
         return $new_regla;
    }

}