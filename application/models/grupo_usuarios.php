<?php

class GrupoUsuarios extends Doctrine_Record {

    function setTableDefinition() {
        $this->hasColumn('id');
        $this->hasColumn('nombre');
        $this->hasColumn('cuenta_id');
        $this->hasColumn('registrados');
    }

    function setUp() {
        parent::setUp();
        
        $this->hasOne('Cuenta',array(
            'local'=>'cuenta_id',
            'foreign'=>'id'
        ));
        
        $this->hasMany('Tarea as Tareas',array(
            'local'=>'grupo_usuarios_id',
            'foreign'=>'tarea_id',
            'refClass' => 'TareaHasGrupoUsuarios'
        ));
        
        $this->hasMany('Usuario as Usuarios',array(
            'local'=>'grupo_usuarios_id',
            'foreign'=>'usuario_id',
            'refClass' => 'GrupoUsuariosHasUsuario'
        ));
    }

}
