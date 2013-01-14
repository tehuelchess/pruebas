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
        ))

        ;
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

    
    public static function cuentaSegunDominio() {
        static $firstTime=true;
        static $cuentaSegunDominio=null;
        if ($firstTime) {
            $firstTime=false;
            $CI = &get_instance();
            preg_match('/(.+)\.chilesinpapeleo\.cl/', $CI->input->server('HTTP_HOST'), $matches);
            $dominio = null;
            if (isset($matches[1]) && $matches[1] != 'simple'){
                $dominio = $matches[1];
                $cuentaSegunDominio = Doctrine::getTable('Cuenta')->findOneByNombre($dominio);
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
