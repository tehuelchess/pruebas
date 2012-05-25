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

        $new_regla = preg_replace_callback('/@@(\w+)/', function($match) use ($tramite_id) {
                    $nombre_dato = $match[1];
                    $dato = Doctrine::getTable('Dato')->findOneByTramiteIdAndNombre($tramite_id, $nombre_dato);
                    if ($dato) {
                        $dato_almacenado = json_decode($dato->valor);
                        if (is_array($dato_almacenado)) {
                            $valor_dato = 'array(' . implode(',', $dato_almacenado) . ')';
                        }
                        else
                            $valor_dato = "'" . $dato_almacenado . "'";
                    }
                    else {
                        $valor_dato = NULL;
                    }

                    return $valor_dato;
                }, $this->regla);

        $new_regla = 'return ' . $new_regla . ';';

        $CI = & get_instance();
        $CI->load->library('SaferEval');
        $resultado = FALSE;
        if (!$errores = $CI->safereval->checkScript($new_regla, FALSE))
            $resultado = eval($new_regla);

        return $resultado;
    }

}