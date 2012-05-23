<?php

class Regla{
    
    private $regla;
    
    function __construct($regla) {
        $this->regla=$regla;
    }
    
    //Evalua la regla de acuerdo a los datos capturados en el tramite tramite_id
    public function evaluar($tramite_id){
        if(!$this->regla)
            return TRUE; 
        
        $new_regla=preg_replace_callback('/@@(\w+)/', function($match) use ($tramite_id){
            $nombre_dato=$match[1];
            $datos=Doctrine_Query::create()
                    ->from('Dato d')
                    ->where('d.tramite_id=? and d.nombre=?',array($tramite_id,$nombre_dato))
                    ->execute();
            if($datos->count()>1){
                foreach($datos as $d)
                    $valores[]="'".$d->valor."'";
                $valor_dato='array('.implode(',',$valores).')';
            }
            else if($datos->count()==1)
                $valor_dato="'".$datos[0]->valor."'";
            else
                $valor_dato='';
            
            return $valor_dato;
            
        }, $this->regla);
        
        $new_regla='return '.$new_regla.';';
        
        $CI=& get_instance(); 
        $CI->load->library('SaferEval');
        $resultado=FALSE;
        if(!$errores=$CI->safereval->checkScript($new_regla,FALSE))
            $resultado=eval($new_regla);
        
        return $resultado;
    }
}