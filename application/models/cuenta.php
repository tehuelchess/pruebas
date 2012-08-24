<?php

class Cuenta extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('nombre');
    }

    function setUp() {
        parent::setUp();
        
        $this->hasMany('UsuarioBackend as UsuariosBackend',array(
            'local'=>'id',
            'foreign'=>'cuenta_id'
        ));
        
        $this->hasMany('Usuario as Usuarios',array(
            'local'=>'id',
            'foreign'=>'cuenta_id'
        ))
        
        ;$this->hasMany('GrupoUsuarios as GruposUsuarios',array(
            'local'=>'id',
            'foreign'=>'cuenta_id'
        ));
        
        $this->hasMany('Proceso as Procesos',array(
            'local'=>'id',
            'foreign'=>'cuenta_id'
        ));
        
        $this->hasMany('Widget as Widgets',array(
            'local'=>'id',
            'foreign'=>'cuenta_id',
            'orderBy'=>'posicion'
        ));
    }
    
    public function updatePosicionesWidgetsFromJSON($json){
        $posiciones=  json_decode($json);
        
        Doctrine_Manager::connection()->beginTransaction();
        foreach($this->Widgets as $c){
            $c->posicion=array_search($c->id, $posiciones);
            $c->save();
        }
        Doctrine_Manager::connection()->commit();
    }

}
