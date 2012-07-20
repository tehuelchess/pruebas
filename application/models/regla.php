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
        $new_regla=preg_replace_callback('/@@(\w+)/', function($match) use ($tramite_id) {
                    $nombre_dato = $match[1];
                    $dato = Doctrine::getTable('Dato')->findOneByTramiteIdAndNombre($tramite_id, $nombre_dato);
                    if ($dato) {
                        $dato_almacenado = $dato->valor;
                        if (is_array($dato_almacenado)) {
                            $valor_dato = 'array(' . implode(',', $dato_almacenado) . ')';
                        }
                        else
                            $valor_dato = "'" . $dato_almacenado . "'";
                    }
                    else {
                        //No reemplazamos el dato
                        $valor_dato = $match[0];
                    }

                    return $valor_dato;
                }, $this->regla);
                
         //Si quedaron variables sin reemplazar, la evaluacion deberia ser siempre falsa.
         if(preg_match('/@@\w+/', $new_regla))
            return false;
          
         return $new_regla;
    }
    
    //Obtiene la expresion con los reemplazos de variables ya hechos de acuerdo a los datos capturados en el tramite tramite_id.
    //Esta es una representacion con las variables reemplazadas. No es una expresion evaluable. (Los arrays y strings no estan definidos como tal)
    public function getExpresionParaOutput($tramite_id){
        $new_regla=preg_replace_callback('/@@(\w+)/', function($match) use ($tramite_id) {
                    $nombre_dato = $match[1];
                    $dato = Doctrine::getTable('Dato')->findOneByTramiteIdAndNombre($tramite_id, $nombre_dato);
                    if ($dato) {
                        $dato_almacenado = $dato->valor;
                        if (is_array($dato_almacenado)) {
                            $valor_dato = implode(',', $dato_almacenado);
                        }
                        else
                            $valor_dato = $dato_almacenado;
                    }
                    else {
                        //No reemplazamos el dato
                        $valor_dato = $match[0];
                    }

                    return $valor_dato;
                }, $this->regla);
          
         return $new_regla;
    }

}