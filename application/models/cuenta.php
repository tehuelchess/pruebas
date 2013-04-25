<?php

class Cuenta extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('nombre');
        $this->hasColumn('nombre_largo');
        $this->hasColumn('mensaje');
        $this->hasColumn('logo');
    }

    function setUp() {
        parent::setUp();

        $this->hasMany('UsuarioBackend as UsuariosBackend', array(
            'local' => 'id',
            'foreign' => 'cuenta_id'
        ));

        $this->hasMany('Usuario as Usuarios', array(
            'local' => 'id',
            'foreign' => 'cuenta_id'
        ));
        
        $this->hasMany('GrupoUsuarios as GruposUsuarios', array(
            'local' => 'id',
            'foreign' => 'cuenta_id'
        ));

        $this->hasMany('Proceso as Procesos', array(
            'local' => 'id',
            'foreign' => 'cuenta_id'
        ));

        $this->hasMany('Widget as Widgets', array(
            'local' => 'id',
            'foreign' => 'cuenta_id',
            'orderBy' => 'posicion'
        ));
        
        $this->hasMany('HsmConfiguracion as HsmConfiguraciones', array(
            'local' => 'id',
            'foreign' => 'cuenta_id'
        ));
    }

    public function updatePosicionesWidgetsFromJSON($json) {
        $posiciones = json_decode($json);

        Doctrine_Manager::connection()->beginTransaction();
        foreach ($this->Widgets as $c) {
            $c->posicion = array_search($c->id, $posiciones);
            $c->save();
        }
        Doctrine_Manager::connection()->commit();
    }

    //Retorna el objecto cuenta perteneciente a este dominio.
    //Retorna el string localhost si estamos en localhost
    //Retorna null si no estamos en ninguna cuenta valida.
    public static function cuentaSegunDominio() {
        static $firstTime=true;
        static $cuentaSegunDominio=null;
        if ($firstTime) {
            $firstTime=false;
            $CI = &get_instance();
            $host=$CI->input->server('HTTP_HOST');
            preg_match('/(.+)\.chilesinpapeleo\.cl/', $host, $matches);
                if($host == 'localhost' || (isset($matches[1]) && $matches[1] == 'simple')){
                    $cuentaSegunDominio='localhost';
                }else if (isset ($matches[1])){
                    $cuentaSegunDominio = Doctrine::getTable('Cuenta')->findOneByNombre($matches[1]);
                }
            
                
        }

        return $cuentaSegunDominio;
    }
    
    public function getLogoADesplegar(){
        if($this->logo)
            return base_url('uploads/logos/'.$this->logo);
        else
            return base_url('assets/img/logo.png');
    }

}
